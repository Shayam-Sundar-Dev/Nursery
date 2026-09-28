import React, { useState, useEffect } from 'react';
import api from '../api/client';
import { useStore } from '../context/StoreContext';
import {
  ShieldCheck,
  CheckCircle2,
  Truck,
  CreditCard,
  MapPin,
  Lock,
  ArrowRight,
  Sparkles,
  ChevronLeft,
  Leaf,
  ShoppingBag,
  Tag,
  AlertCircle,
} from 'lucide-react';

export default function CheckoutView() {
  const {
    cart,
    cartSubtotal,
    currencySymbol,
    freeShippingThreshold,
    defaultShippingFee,
    thermalPackRequested,
    setThermalPackRequested,
    thermalPackagingFee,
    appliedCoupon,
    applyCouponCode,
    removeCoupon,
    couponLoading,
    clearCart,
    navigateTo,
    addToast,
    user,
    token,
    setAuthModalOpen,
    updateUserProfile,
  } = useStore();

  const [saveProfileForFuture, setSaveProfileForFuture] = useState(true);
  const [hasSavedProfileApplied, setHasSavedProfileApplied] = useState(false);

  const [form, setForm] = useState({
    customer_name: user?.name || '',
    customer_email: user?.email || '',
    customer_phone: user?.phone || '',
    shipping_street: user?.street_address || '',
    shipping_landmark: '',
    city: user?.city || '',
    state: user?.state || '',
    postal_code: user?.postal_code || '',
    dispatch_notes: '',
    payment_method: 'cod', // 'cod' | 'upi' | 'card'
  });

  const [summary, setSummary] = useState(null);
  const [loadingSummary, setLoadingSummary] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [createdOrder, setCreatedOrder] = useState(null);
  const [couponInput, setCouponInput] = useState('');

  // Auto-fill logged-in customer credentials or saved local profile
  useEffect(() => {
    let saved = null;
    try {
      const raw = localStorage.getItem('botanical_saved_delivery_profile');
      if (raw) saved = JSON.parse(raw);
    } catch {
      saved = null;
    }

    const defaultName = user?.name || saved?.customer_name || '';
    const defaultEmail = user?.email || saved?.customer_email || '';
    const defaultPhone = user?.phone || saved?.customer_phone || '';
    const defaultStreet = user?.street_address || saved?.shipping_street || '';
    const defaultLandmark = saved?.shipping_landmark || '';
    const defaultCity = user?.city || saved?.city || '';
    const defaultState = user?.state || saved?.state || '';
    const defaultPostalCode = user?.postal_code || saved?.postal_code || '';

    if (defaultPhone || defaultStreet || defaultCity || defaultPostalCode) {
      setHasSavedProfileApplied(true);
    }

    setForm((prev) => ({
      ...prev,
      customer_name: defaultName || prev.customer_name,
      customer_email: defaultEmail || prev.customer_email,
      customer_phone: defaultPhone || prev.customer_phone,
      shipping_street: defaultStreet || prev.shipping_street,
      shipping_landmark: defaultLandmark || prev.shipping_landmark,
      city: defaultCity || prev.city,
      state: defaultState || prev.state,
      postal_code: defaultPostalCode || prev.postal_code,
    }));
  }, [user]);

  const handleClearSavedProfile = () => {
    try {
      localStorage.removeItem('botanical_saved_delivery_profile');
    } catch (e) {
      console.warn('Failed clearing saved profile:', e);
    }
    setHasSavedProfileApplied(false);
    setForm((prev) => ({
      ...prev,
      customer_phone: '',
      shipping_street: '',
      shipping_landmark: '',
      city: '',
      state: '',
      postal_code: '',
    }));
    addToast('Cleared saved delivery details.', 'info');
  };

  // Recalculate summary from server API whenever items, coupon, or thermal pack changes
  useEffect(() => {
    if (cart.length === 0) return;

    async function fetchSummary() {
      try {
        setLoadingSummary(true);
        const payload = {
          items: cart.map((item) => ({
            variant_id: item.variantId,
            quantity: item.quantity,
          })),
          postal_code: form.postal_code,
          coupon_code: appliedCoupon?.code,
          thermal_packaging: thermalPackRequested,
        };

        const res = await api.getCheckoutSummary(payload);
        if (res.success && res.breakdown) {
          setSummary(res.breakdown);
        }
      } catch (err) {
        console.error('Summary calculation error:', err);
      } finally {
        setLoadingSummary(false);
      }
    }

    fetchSummary();
  }, [cart, appliedCoupon, thermalPackRequested, form.postal_code]);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const handleApplyCoupon = (e) => {
    e.preventDefault();
    if (couponInput.trim()) {
      applyCouponCode(couponInput.trim());
      setCouponInput('');
    }
  };

  const handleOrderSubmit = async (e) => {
    e.preventDefault();
    if (cart.length === 0) {
      addToast('Your cart is empty. Please add botanicals before checkout.', 'error');
      return;
    }

    if (!form.customer_name.trim() || !form.customer_email.trim() || !form.shipping_street.trim() || !form.city.trim() || !form.postal_code.trim()) {
      addToast('Please complete all required delivery details.', 'error');
      return;
    }

    try {
      setSubmitting(true);
      const streetAddress = form.shipping_landmark.trim()
        ? `${form.shipping_street.trim()}, ${form.shipping_landmark.trim()}`
        : form.shipping_street.trim();

      const orderPayload = {
        customer_name: form.customer_name.trim(),
        customer_email: form.customer_email.trim(),
        customer_phone: form.customer_phone.trim(),
        shipping_address: {
          street: streetAddress,
          city: form.city.trim(),
          state: form.state.trim() || 'Karnataka',
          postal_code: form.postal_code.trim(),
        },
        items: cart.map((item) => ({
          variant_id: item.variantId,
          quantity: item.quantity,
        })),
        gift_message: form.dispatch_notes.trim(),
        coupon_code: appliedCoupon?.code,
        payment_method: form.payment_method,
      };

      const res = await api.createOrder(orderPayload);
      if (res.success && res.order) {
        setCreatedOrder(res.order);
        clearCart();

        // Save delivery address and phone number for future orders
        if (saveProfileForFuture) {
          const profileToSave = {
            customer_name: form.customer_name.trim(),
            customer_email: form.customer_email.trim(),
            customer_phone: form.customer_phone.trim(),
            shipping_street: form.shipping_street.trim(),
            shipping_landmark: form.shipping_landmark.trim(),
            city: form.city.trim(),
            state: form.state.trim() || 'Karnataka',
            postal_code: form.postal_code.trim(),
          };

          try {
            localStorage.setItem('botanical_saved_delivery_profile', JSON.stringify(profileToSave));
            setHasSavedProfileApplied(true);
          } catch (storageErr) {
            console.warn('Failed storing address in localStorage:', storageErr);
          }

          if (user && token && updateUserProfile) {
            updateUserProfile({
              phone: profileToSave.customer_phone,
              street_address: streetAddress,
              city: profileToSave.city,
              state: profileToSave.state,
              postal_code: profileToSave.postal_code,
            }).catch(() => {});
          }
        }

        addToast(`Order #${res.order.order_number} confirmed! Dispatched in climate-controlled packaging.`, 'success');
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
        throw new Error(res.message || 'Failed to place botanical order');
      }
    } catch (err) {
      console.error('Order creation error:', err);
      addToast(err.message || 'Failed to place botanical order. Please try again.', 'error');
    } finally {
      setSubmitting(false);
    }
  };

  // 1. Order Success Screen
  if (createdOrder) {
    return (
      <div className="max-w-2xl mx-auto px-4 py-12 sm:py-16 text-center space-y-6 animate-in fade-in duration-300">
        <div className="w-20 h-20 rounded-3xl bg-emerald-100 text-emerald-800 flex items-center justify-center mx-auto shadow-inner">
          <CheckCircle2 className="w-10 h-10" />
        </div>

        <div>
          <span className="text-xs uppercase font-bold tracking-widest text-botanical-700">
            Greenhouse Order Confirmed
          </span>
          <h2 className="font-serif text-3xl sm:text-4xl font-bold text-stone-900 mt-1">
            Thank you, {createdOrder.customer_name}
          </h2>
          <p className="text-xs sm:text-sm text-stone-500 mt-1.5">
            Your live plant order reference:{' '}
            <span className="font-mono font-bold text-stone-900 bg-white border border-stone-200 px-2.5 py-0.5 rounded-lg text-sm shadow-2xs">
              #{createdOrder.order_number}
            </span>
          </p>
        </div>

        <div className="p-6 rounded-3xl bg-white border border-stone-200/90 shadow-card text-left max-w-lg mx-auto space-y-3.5 text-xs">
          <div className="flex justify-between border-b border-stone-100 pb-2.5">
            <span className="text-stone-500">Total amount</span>
            <span className="font-bold text-stone-900 tabular-nums font-serif text-base">
              {currencySymbol}{parseFloat(createdOrder.total_amount).toFixed(2)}
            </span>
          </div>
          <div className="flex justify-between border-b border-stone-100 pb-2.5">
            <span className="text-stone-500">Payment method</span>
            <span className="font-semibold text-stone-800">
              {createdOrder.payment_method === 'cod' ? 'Cash on Delivery' : createdOrder.payment_method?.toUpperCase()}
            </span>
          </div>
          <div className="flex justify-between border-b border-stone-100 pb-2.5">
            <span className="text-stone-500">Delivery address</span>
            <span className="font-medium text-stone-800 text-right max-w-xs">
              {createdOrder.shipping_address?.street || createdOrder.shipping_address_line1}, {createdOrder.shipping_address?.city || createdOrder.city} {createdOrder.shipping_address?.postal_code || createdOrder.postal_code}
            </span>
          </div>
          <div className="flex justify-between items-center">
            <span className="text-stone-500">Transit handling</span>
            <span className="font-bold text-botanical-800 flex items-center gap-1.5">
              <Sparkles className="w-3.5 h-3.5 text-amber-500" />
              Climate acclimation & packaging
            </span>
          </div>
        </div>

        <div className="flex flex-col sm:flex-row gap-3 justify-center pt-2">
          <button
            onClick={() => navigateTo('tracking', { trackingNumber: createdOrder.order_number })}
            className="h-12 px-6 rounded-full bg-botanical-800 hover:bg-botanical-900 text-white font-bold text-xs uppercase tracking-wider shadow-md transition flex items-center justify-center gap-2 cursor-pointer active:scale-98"
          >
            <Truck className="w-4 h-4 text-emerald-300" />
            <span>Track Plant Transit Timeline</span>
          </button>
          <button
            onClick={() => {
              setCreatedOrder(null);
              navigateTo('catalog');
            }}
            className="h-12 px-6 rounded-full border border-stone-300 hover:bg-white text-stone-700 font-bold text-xs uppercase tracking-wider transition cursor-pointer"
          >
            Return to Nursery Catalog
          </button>
        </div>
      </div>
    );
  }

  // 2. Empty Cart Fallback State
  if (cart.length === 0) {
    return (
      <div className="max-w-2xl mx-auto px-4 py-20 sm:py-28 text-center space-y-6">
        <div className="w-20 h-20 mx-auto rounded-3xl bg-sand-100 border border-sand-200 flex items-center justify-center text-stone-400 shadow-subtle">
          <ShoppingBag className="w-9 h-9 stroke-1 text-botanical-600" />
        </div>
        <div className="space-y-2">
          <h2 className="font-serif text-3xl sm:text-4xl font-bold text-stone-900 text-balance">
            Your cart is currently empty
          </h2>
          <p className="text-stone-500 max-w-md mx-auto text-xs sm:text-sm leading-relaxed text-pretty">
            Please add living houseplants, rare botanical specimens, or nursery essentials to your cart before proceeding to checkout.
          </p>
        </div>
        <div className="pt-3">
          <button
            onClick={() => navigateTo('catalog')}
            className="h-12 px-7 rounded-full bg-botanical-800 hover:bg-botanical-900 text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-botanical-900/10 transition inline-flex items-center gap-2.5 active:scale-98 cursor-pointer"
          >
            <span>Explore Botanical Catalog</span>
            <ArrowRight className="w-3.5 h-3.5 text-emerald-300" />
          </button>
        </div>
      </div>
    );
  }

  // 3. Main Dedicated Checkout Page
  const finalSubtotal = Number(summary?.subtotal ?? cartSubtotal ?? 0);
  const finalDiscount = Number(summary?.discount ?? appliedCoupon?.calculated_discount ?? 0);
  const finalShipping = Number(summary?.shipping ?? (cartSubtotal >= (freeShippingThreshold || 75) ? 0 : (defaultShippingFee || 9.99)));
  const finalThermal = Number(summary?.insulation_fee ?? summary?.thermal_packaging ?? (thermalPackRequested ? (thermalPackagingFee || 4.50) : 0));
  const finalTotal = Number(summary?.total ?? summary?.grand_total ?? Math.max(0, finalSubtotal - finalDiscount + finalShipping + finalThermal));

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-8">
      {/* Top Header Deck */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-stone-200/80">
        <div>
          <button
            onClick={() => navigateTo('catalog')}
            className="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-botanical-700 hover:text-botanical-900 mb-2 cursor-pointer transition"
          >
            <ChevronLeft className="w-4 h-4" />
            <span>Continue Shopping</span>
          </button>
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-botanical-800 text-white flex items-center justify-center shadow-xs">
              <Lock className="w-4 h-4 text-emerald-300" />
            </div>
            <div>
              <h1 className="font-serif text-2xl sm:text-3xl font-bold text-stone-900 tracking-tight leading-tight">
                Botanical Checkout
              </h1>
              <span className="text-xs text-stone-500 font-medium">
                Safe transit guarantee & direct nursery fulfillment
              </span>
            </div>
          </div>
        </div>

        {/* Member Sign-in indicator */}
        {!user && (
          <div className="inline-flex items-center gap-2 p-2 px-3 rounded-full bg-sand-100 text-stone-600 text-xs">
            <span>Already have an account?</span>
            <button
              onClick={() => setAuthModalOpen('login')}
              className="font-bold text-botanical-800 hover:underline cursor-pointer"
            >
              Sign In
            </button>
          </div>
        )}
      </div>

      <form onSubmit={handleOrderSubmit} className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {/* Left Column: Delivery & Payment Details (7 cols) */}
        <div className="lg:col-span-7 space-y-6">
          
          {/* Section 1: Customer Details */}
          <div className="p-6 rounded-3xl bg-white border border-stone-200/90 shadow-card space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-stone-100">
              <h2 className="text-xs font-bold uppercase tracking-wider text-stone-800 flex items-center gap-2">
                <span className="w-5 h-5 rounded-full bg-botanical-800 text-white flex items-center justify-center text-[10px] font-bold">1</span>
                Recipient & Contact Details
              </h2>
              {user && (
                <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-botanical-100 text-botanical-800 border border-botanical-200">
                  <Sparkles className="w-3 h-3 text-amber-500" />
                  <span>Member Account</span>
                </span>
              )}
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
              <div className="sm:col-span-2">
                <label className="block text-[11px] font-bold uppercase tracking-wider text-stone-500 mb-1">
                  Full Name *
                </label>
                <input
                  type="text"
                  name="customer_name"
                  required
                  placeholder="e.g. Priya Sharma"
                  value={form.customer_name}
                  onChange={handleInputChange}
                  className="w-full h-11 px-3.5 rounded-xl border border-stone-200 bg-sand-50/50 text-xs font-medium focus:bg-white focus:border-botanical-600 focus:ring-2 focus:ring-botanical-500/20 focus:outline-none transition"
                />
              </div>

              <div>
                <label className="block text-[11px] font-bold uppercase tracking-wider text-stone-500 mb-1">
                  Email Address *
                </label>
                <input
                  type="email"
                  name="customer_email"
                  required
                  placeholder="name@example.com"
                  value={form.customer_email}
                  onChange={handleInputChange}
                  className="w-full h-11 px-3.5 rounded-xl border border-stone-200 bg-sand-50/50 text-xs font-medium focus:bg-white focus:border-botanical-600 focus:ring-2 focus:ring-botanical-500/20 focus:outline-none transition"
                />
              </div>

              <div>
                <label className="block text-[11px] font-bold uppercase tracking-wider text-stone-500 mb-1">
                  Phone Number *
                </label>
                <input
                  type="tel"
                  name="customer_phone"
                  required
                  placeholder="+91 98765 43210"
                  value={form.customer_phone}
                  onChange={handleInputChange}
                  className="w-full h-11 px-3.5 rounded-xl border border-stone-200 bg-sand-50/50 text-xs font-medium focus:bg-white focus:border-botanical-600 focus:ring-2 focus:ring-botanical-500/20 focus:outline-none transition"
                />
              </div>
            </div>
          </div>

          {/* Section 2: Shipping Destination */}
          <div className="p-6 rounded-3xl bg-white border border-stone-200/90 shadow-card space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-stone-100">
              <h2 className="text-xs font-bold uppercase tracking-wider text-stone-800 flex items-center gap-2">
                <span className="w-5 h-5 rounded-full bg-botanical-800 text-white flex items-center justify-center text-[10px] font-bold">2</span>
                Climate-Protected Delivery Address
              </h2>
              {hasSavedProfileApplied && (
                <button
                  type="button"
                  onClick={handleClearSavedProfile}
                  className="text-[11px] font-semibold text-stone-400 hover:text-stone-700 transition underline cursor-pointer"
                >
                  Clear saved address
                </button>
              )}
            </div>

            {hasSavedProfileApplied && (
              <div className="p-3 rounded-2xl bg-emerald-50/80 border border-emerald-200/80 flex items-center gap-2.5 text-xs text-emerald-900">
                <CheckCircle2 className="w-4 h-4 text-emerald-600 shrink-0" />
                <span className="font-medium">
                  Auto-filled from your saved botanical delivery profile. Details can be updated below anytime.
                </span>
              </div>
            )}

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
              <div className="sm:col-span-2">
                <label className="block text-[11px] font-bold uppercase tracking-wider text-stone-500 mb-1">
                  Street Address / Flat / Building *
                </label>
                <input
                  type="text"
                  name="shipping_street"
                  required
                  placeholder="Flat 402, Green Meadows Apartment, Palm Grove Road"
                  value={form.shipping_street}
                  onChange={handleInputChange}
                  className="w-full h-11 px-3.5 rounded-xl border border-stone-200 bg-sand-50/50 text-xs font-medium focus:bg-white focus:border-botanical-600 focus:ring-2 focus:ring-botanical-500/20 focus:outline-none transition"
                />
              </div>

              <div>
                <label className="block text-[11px] font-bold uppercase tracking-wider text-stone-500 mb-1">
                  Apartment / Landmark (Optional)
                </label>
                <input
                  type="text"
                  name="shipping_landmark"
                  placeholder="Near City Botanical Park"
                  value={form.shipping_landmark}
                  onChange={handleInputChange}
                  className="w-full h-11 px-3.5 rounded-xl border border-stone-200 bg-sand-50/50 text-xs font-medium focus:bg-white focus:border-botanical-600 focus:ring-2 focus:ring-botanical-500/20 focus:outline-none transition"
                />
              </div>

              <div>
                <label className="block text-[11px] font-bold uppercase tracking-wider text-stone-500 mb-1">
                  City *
                </label>
                <input
                  type="text"
                  name="city"
                  required
                  placeholder="e.g. Bengaluru"
                  value={form.city}
                  onChange={handleInputChange}
                  className="w-full h-11 px-3.5 rounded-xl border border-stone-200 bg-sand-50/50 text-xs font-medium focus:bg-white focus:border-botanical-600 focus:ring-2 focus:ring-botanical-500/20 focus:outline-none transition"
                />
              </div>

              <div>
                <label className="block text-[11px] font-bold uppercase tracking-wider text-stone-500 mb-1">
                  State *
                </label>
                <input
                  type="text"
                  name="state"
                  required
                  placeholder="e.g. Karnataka"
                  value={form.state}
                  onChange={handleInputChange}
                  className="w-full h-11 px-3.5 rounded-xl border border-stone-200 bg-sand-50/50 text-xs font-medium focus:bg-white focus:border-botanical-600 focus:ring-2 focus:ring-botanical-500/20 focus:outline-none transition"
                />
              </div>

              <div>
                <label className="block text-[11px] font-bold uppercase tracking-wider text-stone-500 mb-1">
                  PIN / Postal Code *
                </label>
                <input
                  type="text"
                  name="postal_code"
                  required
                  placeholder="e.g. 560034"
                  value={form.postal_code}
                  onChange={handleInputChange}
                  className="w-full h-11 px-3.5 rounded-xl border border-stone-200 bg-sand-50/50 text-xs font-medium focus:bg-white focus:border-botanical-600 focus:ring-2 focus:ring-botanical-500/20 focus:outline-none transition"
                />
              </div>

              <div className="sm:col-span-2">
                <label className="block text-[11px] font-bold uppercase tracking-wider text-stone-500 mb-1">
                  Plant Delivery / Dispatch Notes
                </label>
                <input
                  type="text"
                  name="dispatch_notes"
                  placeholder="e.g. Please leave with front desk if absent, handle living specimens with care"
                  value={form.dispatch_notes}
                  onChange={handleInputChange}
                  className="w-full h-11 px-3.5 rounded-xl border border-stone-200 bg-sand-50/50 text-xs font-medium focus:bg-white focus:border-botanical-600 focus:ring-2 focus:ring-botanical-500/20 focus:outline-none transition"
                />
              </div>
            </div>

            {/* Save Address and Phone Toggle */}
            <div className="pt-2 border-t border-stone-100">
              <label className="flex items-center gap-3 p-3.5 rounded-2xl bg-sand-50/60 border border-stone-200/80 hover:border-botanical-500 transition cursor-pointer select-none">
                <input
                  type="checkbox"
                  checked={saveProfileForFuture}
                  onChange={(e) => setSaveProfileForFuture(e.target.checked)}
                  className="w-4 h-4 rounded text-botanical-700 focus:ring-botanical-500/20 border-stone-300 cursor-pointer shrink-0"
                />
                <div className="min-w-0">
                  <span className="text-xs font-bold text-stone-800 flex items-center gap-1.5">
                    <span>Save this address and phone number for future orders</span>
                    <span className="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-botanical-100 text-botanical-800">
                      Recommended
                    </span>
                  </span>
                  <span className="text-[11px] text-stone-500 block mt-0.5">
                    Securely remembers your delivery credentials on this device and account for one-click future checkouts.
                  </span>
                </div>
              </label>
            </div>
          </div>

          {/* Section 3: Payment Method */}
          <div className="p-6 rounded-3xl bg-white border border-stone-200/90 shadow-card space-y-4">
            <div className="pb-3 border-b border-stone-100">
              <h2 className="text-xs font-bold uppercase tracking-wider text-stone-800 flex items-center gap-2">
                <span className="w-5 h-5 rounded-full bg-botanical-800 text-white flex items-center justify-center text-[10px] font-bold">3</span>
                Payment Preference
              </h2>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <label
                className={`p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col justify-between ${
                  form.payment_method === 'cod'
                    ? 'border-botanical-700 bg-botanical-50/70 shadow-xs'
                    : 'border-stone-200 bg-white hover:border-stone-300'
                }`}
              >
                <input
                  type="radio"
                  name="payment_method"
                  value="cod"
                  checked={form.payment_method === 'cod'}
                  onChange={handleInputChange}
                  className="hidden"
                />
                <div>
                  <div className="text-xs font-bold text-stone-900">Cash on Delivery</div>
                  <div className="text-[10px] text-stone-500 mt-1 leading-tight">Pay upon doorstep plant arrival & inspection</div>
                </div>
              </label>

              <label
                className={`p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col justify-between ${
                  form.payment_method === 'upi'
                    ? 'border-botanical-700 bg-botanical-50/70 shadow-xs'
                    : 'border-stone-200 bg-white hover:border-stone-300'
                }`}
              >
                <input
                  type="radio"
                  name="payment_method"
                  value="upi"
                  checked={form.payment_method === 'upi'}
                  onChange={handleInputChange}
                  className="hidden"
                />
                <div>
                  <div className="text-xs font-bold text-stone-900">Instant UPI</div>
                  <div className="text-[10px] text-stone-500 mt-1 leading-tight">Google Pay, PhonePe, Paytm, QR code</div>
                </div>
              </label>

              <label
                className={`p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col justify-between ${
                  form.payment_method === 'card'
                    ? 'border-botanical-700 bg-botanical-50/70 shadow-xs'
                    : 'border-stone-200 bg-white hover:border-stone-300'
                }`}
              >
                <input
                  type="radio"
                  name="payment_method"
                  value="card"
                  checked={form.payment_method === 'card'}
                  onChange={handleInputChange}
                  className="hidden"
                />
                <div>
                  <div className="text-xs font-bold text-stone-900">Cards & Netbanking</div>
                  <div className="text-[10px] text-stone-500 mt-1 leading-tight">Visa, MasterCard, RuPay, NetBanking</div>
                </div>
              </label>
            </div>
          </div>
        </div>

        {/* Right Column: Order Summary & Placement (5 cols) */}
        <div className="lg:col-span-5 space-y-6 lg:sticky lg:top-24">
          <div className="p-6 rounded-3xl bg-white border border-stone-200/90 shadow-card space-y-5">
            <div className="flex items-center justify-between pb-3 border-b border-stone-100">
              <h2 className="font-serif text-lg font-bold text-stone-900">
                Order Summary
              </h2>
              <span className="text-xs text-stone-500 font-medium">
                {cart.length} {cart.length === 1 ? 'Specimen' : 'Specimens'}
              </span>
            </div>

            {/* Specimen Items Mini-List */}
            <div className="space-y-3 max-h-64 overflow-y-auto pr-1 no-scrollbar divide-y divide-stone-100">
              {cart.map((item) => (
                <div key={item.id} className="pt-3 first:pt-0 flex items-center gap-3">
                  <div className="w-14 h-14 rounded-xl overflow-hidden bg-sand-100 border border-stone-200/80 shrink-0 relative">
                    <img
                      src={item.image}
                      alt={item.productName}
                      onError={(e) => {
                        e.currentTarget.onerror = null;
                        e.currentTarget.src = 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?auto=format&fit=crop&w=400&q=80';
                      }}
                      className="w-full h-full object-cover object-center"
                    />
                  </div>
                  <div className="flex-1 min-w-0">
                    <h4 className="font-serif font-bold text-xs text-stone-900 truncate">
                      {item.productName}
                    </h4>
                    <p className="text-[11px] text-botanical-700 truncate">{item.variantTitle}</p>
                    <span className="text-[11px] text-stone-400">Qty: {item.quantity}</span>
                  </div>
                  <div className="text-right font-serif font-bold text-xs text-stone-900 tabular-nums">
                    {currencySymbol}{(Number(item.price || 0) * Number(item.quantity || 1)).toFixed(2)}
                  </div>
                </div>
              ))}
            </div>

            {/* Thermal Root Packaging Toggle */}
            <label className="flex items-center justify-between p-3 rounded-2xl bg-sand-50/70 border border-stone-200/80 cursor-pointer hover:border-botanical-400 transition select-none">
              <div className="flex items-center gap-2.5 min-w-0 pr-2">
                <input
                  type="checkbox"
                  checked={thermalPackRequested}
                  onChange={(e) => setThermalPackRequested(e.target.checked)}
                  className="w-4 h-4 rounded text-botanical-700 focus:ring-botanical-500/20 border-stone-300 cursor-pointer shrink-0"
                />
                <div className="min-w-0">
                  <div className="flex items-center gap-1.5 font-bold text-xs text-stone-800">
                    <ShieldCheck className="w-3.5 h-3.5 text-botanical-700 shrink-0" />
                    <span>Thermal Root Protection</span>
                  </div>
                  <p className="text-[10px] text-stone-500 truncate">72h insulated climate wrapping</p>
                </div>
              </div>
              <span className="text-xs font-bold text-botanical-800 tabular-nums shrink-0">
                +{currencySymbol}{(Number(thermalPackagingFee) || 4.50).toFixed(2)}
              </span>
            </label>

            {/* Promo Code Form */}
            <div>
              {appliedCoupon ? (
                <div className="flex items-center justify-between p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 font-semibold">
                  <div className="flex items-center gap-1.5 min-w-0 truncate">
                    <Tag className="w-3.5 h-3.5 text-emerald-700 shrink-0" />
                    <span className="truncate">{appliedCoupon.code} (-{currencySymbol}{appliedCoupon.calculated_discount.toFixed(2)})</span>
                  </div>
                  <button
                    type="button"
                    onClick={removeCoupon}
                    className="text-emerald-700 hover:text-rose-600 text-[11px] font-bold underline cursor-pointer shrink-0"
                  >
                    Remove
                  </button>
                </div>
              ) : (
                <div className="relative flex items-center">
                  <input
                    type="text"
                    value={couponInput}
                    onChange={(e) => setCouponInput(e.target.value)}
                    placeholder="Promo code (e.g. SPRINGBLOOM)"
                    className="w-full h-10 pl-3 pr-20 rounded-xl border border-stone-200 bg-sand-50/50 text-xs uppercase font-mono font-semibold placeholder:normal-case placeholder:font-sans focus:bg-white focus:border-botanical-600 focus:ring-2 focus:ring-botanical-500/20 focus:outline-none transition"
                  />
                  <button
                    type="button"
                    onClick={handleApplyCoupon}
                    disabled={couponLoading || !couponInput.trim()}
                    className="absolute right-1 top-1 bottom-1 px-3.5 rounded-lg bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs disabled:opacity-40 transition cursor-pointer"
                  >
                    {couponLoading ? '...' : 'Apply'}
                  </button>
                </div>
              )}
            </div>

            {/* Price Calculations */}
            <div className="space-y-2 text-xs text-stone-600 pt-3 border-t border-stone-100 tabular-nums">
              <div className="flex justify-between">
                <span>Botanical Subtotal</span>
                <span className="font-semibold text-stone-800">{currencySymbol}{(Number(finalSubtotal) || 0).toFixed(2)}</span>
              </div>

              {finalDiscount > 0 && (
                <div className="flex justify-between text-emerald-700 font-semibold">
                  <span>Voucher Discount ({appliedCoupon?.code})</span>
                  <span>-{currencySymbol}{(Number(finalDiscount) || 0).toFixed(2)}</span>
                </div>
              )}

              {thermalPackRequested && (
                <div className="flex justify-between">
                  <span>Thermal Protection</span>
                  <span className="font-semibold text-stone-800">{currencySymbol}{(Number(finalThermal) || 0).toFixed(2)}</span>
                </div>
              )}

              <div className="flex justify-between">
                <span>Carrier Climate Transit</span>
                <span className="font-semibold text-stone-800">
                  {finalShipping === 0 ? (
                    <span className="text-emerald-700 font-bold uppercase text-[10px] bg-emerald-100/80 px-2 py-0.5 rounded-full">Free</span>
                  ) : (
                    `${currencySymbol}${(Number(finalShipping) || 0).toFixed(2)}`
                  )}
                </span>
              </div>

              <div className="flex justify-between items-baseline text-base font-bold text-stone-900 pt-3 border-t border-stone-200">
                <span className="font-serif text-lg">Grand Total</span>
                <span className="font-serif text-2xl font-bold text-botanical-900">
                  {currencySymbol}{(Number(finalTotal) || 0).toFixed(2)}
                </span>
              </div>
            </div>

            {/* Place Order CTA Button */}
            <div className="space-y-3 pt-2">
              <button
                type="submit"
                disabled={submitting}
                className="w-full min-h-[58px] px-5 py-3.5 rounded-2xl bg-botanical-800 hover:bg-botanical-900 active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed text-white shadow-xl shadow-botanical-950/20 transition-all duration-200 cursor-pointer group flex items-center justify-between gap-3 border border-botanical-700/60"
              >
                {submitting ? (
                  <div className="flex items-center justify-center gap-2.5 w-full py-1 text-xs font-bold uppercase tracking-wider text-emerald-200">
                    <div className="w-4 h-4 border-2 border-emerald-300 border-t-transparent rounded-full animate-spin" />
                    <span>Processing Botanical Order...</span>
                  </div>
                ) : (
                  <>
                    <div className="flex items-center gap-3 text-left min-w-0">
                      <div className="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0 border border-white/10 group-hover:scale-105 transition-transform">
                        <Lock className="w-4 h-4 text-emerald-300" />
                      </div>
                      <div className="min-w-0">
                        <div className="font-bold text-sm sm:text-base text-white leading-tight truncate">
                          Confirm & Place Order
                        </div>
                        <div className="text-[11px] text-emerald-200/80 font-medium truncate">
                          Safe live-transit guarantee
                        </div>
                      </div>
                    </div>

                    <div className="flex items-center gap-2 shrink-0">
                      <span className="px-3 py-1.5 rounded-xl bg-white/15 text-white font-serif font-bold text-sm sm:text-base tabular-nums border border-white/10 shadow-xs">
                        {currencySymbol}{(Number(finalTotal) || 0).toFixed(2)}
                      </span>
                      <div className="w-8 h-8 rounded-xl bg-emerald-400/20 text-emerald-300 flex items-center justify-center group-hover:translate-x-1 transition-transform">
                        <ArrowRight className="w-4 h-4" />
                      </div>
                    </div>
                  </>
                )}
              </button>

              {/* Trust & Guarantee Reassurance */}
              <div className="flex items-center justify-center gap-4 text-[11px] text-stone-500 select-none py-1">
                <span className="inline-flex items-center gap-1.5 font-medium">
                  <ShieldCheck className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                  <span>256-Bit Encrypted</span>
                </span>
                <span className="text-stone-300">&bull;</span>
                <span className="inline-flex items-center gap-1.5 font-medium">
                  <Truck className="w-3.5 h-3.5 text-botanical-700 shrink-0" />
                  <span>Climate Transit Insured</span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  );
}
