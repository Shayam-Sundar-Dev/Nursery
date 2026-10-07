import React, { useState, useEffect, useMemo } from 'react';
import api from '../api/client';
import { useStore } from '../context/StoreContext';
import INDIAN_STATES from '../constants/indianStates';
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
  Edit2,
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
    enableStateShipping,
    stateShippingRates,
    getStateShippingRate,
    appliedCoupon,
    applyCouponCode,
    removeCoupon,
    couponLoading,
    clearCart,
    setCartOpen,
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
  const [currentStep, setCurrentStep] = useState(1); // 1: Save Address, 2: Order Summary, 3: Payment

  const isAddressValid = () => {
    return Boolean(
      form.customer_name?.trim() &&
      form.customer_email?.trim() &&
      form.customer_phone?.trim() &&
      form.shipping_street?.trim() &&
      form.city?.trim() &&
      form.state?.trim() &&
      form.postal_code?.trim()
    );
  };

  const handleSaveAddressAndContinue = (e) => {
    if (e) e.preventDefault();
    if (!form.customer_name.trim() || !form.customer_email.trim() || !form.customer_phone.trim() || !form.shipping_street.trim() || !form.city.trim() || !form.state.trim() || !form.postal_code.trim()) {
      addToast('Please complete all required delivery details (name, email, phone, street, city, state, postal code).', 'error');
      return;
    }

    const streetAddress = form.shipping_landmark.trim()
      ? `${form.shipping_street.trim()}, ${form.shipping_landmark.trim()}`
      : form.shipping_street.trim();

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

    addToast('Delivery address saved successfully.', 'success');
    setCurrentStep(2);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

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

  // Prompt sign in if user is not authenticated
  useEffect(() => {
    if (!user && !token) {
      addToast('Please sign in or create an account before checkout', 'info');
      setAuthModalOpen('login');
    }
  }, [user, token, setAuthModalOpen, addToast]);

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

  // Recalculate summary from server API whenever items, coupon, thermal pack, postal code, or state changes
  useEffect(() => {
    if (cart.length === 0) return;

    const timer = setTimeout(async () => {
      try {
        setLoadingSummary(true);
        const payload = {
          items: cart.map((item) => ({
            variant_id: item.variantId,
            quantity: item.quantity,
          })),
          postal_code: form.postal_code,
          state: form.state,
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
    }, 200);

    return () => clearTimeout(timer);
  }, [cart, appliedCoupon, thermalPackRequested, form.postal_code, form.state]);

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
    if (!user && !token) {
      addToast('Please sign in or create an account to proceed to checkout', 'error');
      setAuthModalOpen('login');
      return;
    }
    if (cart.length === 0) {
      addToast('Your cart is empty. Please add botanicals before checkout.', 'error');
      return;
    }

    if (!isAddressValid()) {
      addToast('Please complete all required delivery details.', 'error');
      setCurrentStep(1);
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

  // 3. Sign In Required Gate
  if (!user && !token) {
    return (
      <div className="max-w-2xl mx-auto px-4 py-20 sm:py-28 text-center space-y-6">
        <div className="w-20 h-20 mx-auto rounded-3xl bg-amber-50 border border-amber-200/80 flex items-center justify-center text-amber-700 shadow-subtle">
          <Lock className="w-9 h-9 stroke-[1.75]" />
        </div>
        <div className="space-y-2">
          <span className="text-xs uppercase font-bold tracking-widest text-botanical-700">
            Authentication Required
          </span>
          <h2 className="font-serif text-3xl sm:text-4xl font-bold text-stone-900 text-balance">
            Please sign in before checkout
          </h2>
          <p className="text-stone-500 max-w-md mx-auto text-xs sm:text-sm leading-relaxed text-pretty">
            You must be signed in to your nursery account to place an order, calculate location-based shipping, and track plant deliveries.
          </p>
        </div>
        <div className="pt-3 flex flex-col sm:flex-row gap-3 justify-center items-center">
          <button
            onClick={() => setAuthModalOpen('login')}
            className="h-12 px-7 rounded-full bg-botanical-800 hover:bg-botanical-900 text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-botanical-900/10 transition inline-flex items-center gap-2.5 active:scale-98 cursor-pointer"
          >
            <Lock className="w-3.5 h-3.5 text-emerald-300" />
            <span>Sign In to Continue</span>
          </button>
          <button
            onClick={() => setCartOpen(true)}
            className="h-12 px-7 rounded-full border border-stone-300 hover:bg-white text-stone-700 font-bold text-xs uppercase tracking-wider transition inline-flex items-center gap-2 cursor-pointer"
          >
            <ShoppingBag className="w-4 h-4 text-stone-500" />
            <span>Review Botanical Cart</span>
          </button>
        </div>
      </div>
    );
  }

  // 4. Main Dedicated Checkout Page
  const allStatesList = useMemo(() => {
    const list = new Set(INDIAN_STATES);
    if (Array.isArray(stateShippingRates)) {
      stateShippingRates.forEach((r) => {
        if (r?.state) list.add(r.state);
      });
    }
    return Array.from(list).sort((a, b) => a.localeCompare(b));
  }, [stateShippingRates]);

  const stateRateObj = getStateShippingRate(form.state);
  const matchedStateFee = stateRateObj ? parseFloat(stateRateObj.fee) : defaultShippingFee;
  const isFreeShipping = cartSubtotal >= (freeShippingThreshold || 75);
  const fallbackShipping = isFreeShipping ? 0 : matchedStateFee;

  const finalSubtotal = Number(summary?.subtotal ?? cartSubtotal ?? 0);
  const finalDiscount = Number(summary?.discount ?? appliedCoupon?.calculated_discount ?? 0);
  const finalShipping = Number(summary?.shipping ?? fallbackShipping);
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

      {/* Step by Step Progress Wizard Stepper */}
      <div className="bg-white rounded-3xl p-4 sm:p-5 border border-stone-200/90 shadow-xs max-w-4xl mx-auto">
        <div className="flex items-center justify-between max-w-2xl mx-auto">
          {/* Step 1: Address */}
          <button
            type="button"
            onClick={() => {
              setCurrentStep(1);
              window.scrollTo({ top: 0, behavior: 'smooth' });
            }}
            className="flex items-center gap-2 sm:gap-3 text-left cursor-pointer transition group"
          >
            <div className={`w-9 h-9 sm:w-10 sm:h-10 rounded-2xl flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-2xs ${
              currentStep === 1
                ? 'bg-botanical-800 text-white ring-4 ring-botanical-100 shadow-md'
                : currentStep > 1
                ? 'bg-emerald-600 text-white'
                : 'bg-stone-100 text-stone-500'
            }`}>
              {currentStep > 1 ? <CheckCircle2 className="w-5 h-5" /> : '1'}
            </div>
            <div>
              <div className={`text-[10px] sm:text-xs font-bold uppercase tracking-wider ${
                currentStep === 1 ? 'text-botanical-800' : 'text-stone-400'
              }`}>
                Step 1
              </div>
              <div className={`text-xs sm:text-sm font-semibold hidden sm:block ${
                currentStep === 1 ? 'text-stone-900 font-bold' : 'text-stone-600'
              }`}>
                Save Address
              </div>
            </div>
          </button>

          {/* Progress Divider 1-2 */}
          <div className={`flex-1 mx-2 sm:mx-4 h-0.5 rounded-full transition-colors ${
            currentStep > 1 ? 'bg-emerald-500' : 'bg-stone-200'
          }`} />

          {/* Step 2: Order Summary */}
          <button
            type="button"
            onClick={() => {
              if (isAddressValid()) {
                setCurrentStep(2);
                window.scrollTo({ top: 0, behavior: 'smooth' });
              } else {
                addToast('Please complete and save your delivery address first', 'info');
              }
            }}
            className={`flex items-center gap-2 sm:gap-3 text-left transition group ${
              isAddressValid() ? 'cursor-pointer' : 'cursor-not-allowed opacity-60'
            }`}
          >
            <div className={`w-9 h-9 sm:w-10 sm:h-10 rounded-2xl flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-2xs ${
              currentStep === 2
                ? 'bg-botanical-800 text-white ring-4 ring-botanical-100 shadow-md'
                : currentStep > 2
                ? 'bg-emerald-600 text-white'
                : 'bg-stone-100 text-stone-500'
            }`}>
              {currentStep > 2 ? <CheckCircle2 className="w-5 h-5" /> : '2'}
            </div>
            <div>
              <div className={`text-[10px] sm:text-xs font-bold uppercase tracking-wider ${
                currentStep === 2 ? 'text-botanical-800' : 'text-stone-400'
              }`}>
                Step 2
              </div>
              <div className={`text-xs sm:text-sm font-semibold hidden sm:block ${
                currentStep === 2 ? 'text-stone-900 font-bold' : 'text-stone-600'
              }`}>
                Order Summary
              </div>
            </div>
          </button>

          {/* Progress Divider 2-3 */}
          <div className={`flex-1 mx-2 sm:mx-4 h-0.5 rounded-full transition-colors ${
            currentStep > 2 ? 'bg-emerald-500' : 'bg-stone-200'
          }`} />

          {/* Step 3: Payment */}
          <button
            type="button"
            onClick={() => {
              if (isAddressValid()) {
                setCurrentStep(3);
                window.scrollTo({ top: 0, behavior: 'smooth' });
              } else {
                addToast('Please complete and save your delivery address first', 'info');
              }
            }}
            className={`flex items-center gap-2 sm:gap-3 text-left transition group ${
              isAddressValid() ? 'cursor-pointer' : 'cursor-not-allowed opacity-60'
            }`}
          >
            <div className={`w-9 h-9 sm:w-10 sm:h-10 rounded-2xl flex items-center justify-center font-bold text-xs sm:text-sm transition-all shadow-2xs ${
              currentStep === 3
                ? 'bg-botanical-800 text-white ring-4 ring-botanical-100 shadow-md'
                : 'bg-stone-100 text-stone-500'
            }`}>
              3
            </div>
            <div>
              <div className={`text-[10px] sm:text-xs font-bold uppercase tracking-wider ${
                currentStep === 3 ? 'text-botanical-800' : 'text-stone-400'
              }`}>
                Step 3
              </div>
              <div className={`text-xs sm:text-sm font-semibold hidden sm:block ${
                currentStep === 3 ? 'text-stone-900 font-bold' : 'text-stone-600'
              }`}>
                Payment
              </div>
            </div>
          </button>
        </div>
      </div>

      <form
        onSubmit={(e) => {
          e.preventDefault();
          if (currentStep === 1) {
            handleSaveAddressAndContinue();
          } else if (currentStep === 2) {
            setCurrentStep(3);
            window.scrollTo({ top: 0, behavior: 'smooth' });
          } else {
            handleOrderSubmit(e);
          }
        }}
        className="max-w-4xl mx-auto space-y-6"
      >
        {/* ================= STEP 1: SAVE THE ADDRESS ================= */}
        {currentStep === 1 && (
          <div className="space-y-6 animate-in fade-in duration-200">
            {/* Step 1 Title Banner */}
            <div className="flex items-center justify-between pb-2">
              <div>
                <span className="text-xs uppercase font-bold tracking-wider text-botanical-700">
                  Step 1 of 3
                </span>
                <h2 className="font-serif text-xl sm:text-2xl font-bold text-stone-900">
                  Shipping & Delivery Address
                </h2>
              </div>
              <span className="text-xs text-stone-500">
                All fields marked * are required
              </span>
            </div>

            {/* Section 1: Customer Details */}
            <div className="p-6 sm:p-7 rounded-3xl bg-white border border-stone-200/90 shadow-card space-y-4">
              <div className="flex items-center justify-between pb-3 border-b border-stone-100">
                <h3 className="text-xs font-bold uppercase tracking-wider text-stone-800 flex items-center gap-2">
                  <span className="w-5 h-5 rounded-full bg-botanical-800 text-white flex items-center justify-center text-[10px] font-bold">1</span>
                  Recipient & Contact Details
                </h3>
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

            {/* Section 2: Delivery Address */}
            <div className="p-6 sm:p-7 rounded-3xl bg-white border border-stone-200/90 shadow-card space-y-4">
              <div className="flex items-center justify-between pb-3 border-b border-stone-100">
                <h3 className="text-xs font-bold uppercase tracking-wider text-stone-800 flex items-center gap-2">
                  <span className="w-5 h-5 rounded-full bg-botanical-800 text-white flex items-center justify-center text-[10px] font-bold">2</span>
                  Climate-Protected Delivery Address
                </h3>
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
                  <div className="flex items-center justify-between mb-1">
                    <label className="block text-[11px] font-bold uppercase tracking-wider text-stone-500">
                      State / Destination *
                    </label>
                    {stateRateObj && (
                      <span className="text-[10px] font-bold text-botanical-800 bg-botanical-50 px-2 py-0.5 rounded-md border border-botanical-200/60">
                        {isFreeShipping ? 'Free Delivery' : `${currencySymbol}${parseFloat(stateRateObj.fee).toFixed(0)} Delivery`}
                      </span>
                    )}
                  </div>
                  <div className="relative">
                    <input
                      type="text"
                      name="state"
                      list="checkout-indian-states-list"
                      required
                      placeholder="e.g. Karnataka, Maharashtra..."
                      value={form.state}
                      onChange={handleInputChange}
                      className="w-full h-11 px-3.5 rounded-xl border border-stone-200 bg-sand-50/50 text-xs font-medium focus:bg-white focus:border-botanical-600 focus:ring-2 focus:ring-botanical-500/20 focus:outline-none transition"
                    />
                    <datalist id="checkout-indian-states-list">
                      {allStatesList.map((st) => {
                        const rate = getStateShippingRate(st);
                        return (
                          <option
                            key={st}
                            value={st}
                            label={rate ? `${st} (${currencySymbol}${parseFloat(rate.fee).toFixed(0)}${rate.estimated_days ? ` • ${rate.estimated_days}` : ''})` : st}
                          />
                        );
                      })}
                    </datalist>
                  </div>
                  {form.state && (
                    <div className="mt-1.5 flex items-center gap-1.5 text-[11px]">
                      {stateRateObj ? (
                        <span className="text-emerald-800 font-medium flex items-center gap-1">
                          <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                          <span>
                            {stateRateObj.state}: {isFreeShipping ? (
                              <strong className="text-emerald-700">Free Shipping applied</strong>
                            ) : (
                              <>Delivery <strong>{currencySymbol}{parseFloat(stateRateObj.fee).toFixed(2)}</strong></>
                            )}
                            {stateRateObj.estimated_days && ` • Est. transit: ${stateRateObj.estimated_days}`}
                          </span>
                        </span>
                      ) : (
                        <span className="text-stone-500 flex items-center gap-1">
                          <MapPin className="w-3 h-3 text-stone-400 shrink-0" />
                          <span>Standard delivery rate ({currencySymbol}{defaultShippingFee.toFixed(2)}) applies</span>
                        </span>
                      )}
                    </div>
                  )}
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

              {/* Save Address Toggle */}
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

            {/* Step 1 Actions */}
            <div className="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
              <button
                type="button"
                onClick={() => navigateTo('catalog')}
                className="text-xs font-bold text-stone-500 hover:text-stone-800 transition flex items-center gap-1.5 cursor-pointer order-2 sm:order-1"
              >
                <ChevronLeft className="w-4 h-4" />
                <span>Return to Catalog</span>
              </button>
              <button
                type="button"
                onClick={handleSaveAddressAndContinue}
                className="w-full sm:w-auto h-12 px-8 rounded-full bg-botanical-800 hover:bg-botanical-900 active:scale-98 text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-botanical-950/20 transition flex items-center justify-center gap-2 cursor-pointer order-1 sm:order-2"
              >
                <span>Save Address & Continue</span>
                <ArrowRight className="w-4 h-4 text-emerald-300" />
              </button>
            </div>
          </div>
        )}

        {/* ================= STEP 2: SHOW ORDER SUMMARY ================= */}
        {currentStep === 2 && (
          <div className="space-y-6 animate-in fade-in duration-200">
            {/* Step 2 Title Banner */}
            <div className="flex items-center justify-between pb-2">
              <div>
                <span className="text-xs uppercase font-bold tracking-wider text-botanical-700">
                  Step 2 of 3
                </span>
                <h2 className="font-serif text-xl sm:text-2xl font-bold text-stone-900">
                  Review Botanical Order Summary
                </h2>
              </div>
              <span className="text-xs text-stone-500">
                {cart.length} {cart.length === 1 ? 'Specimen' : 'Specimens'} in Cart
              </span>
            </div>

            {/* Saved Delivery Address Summary Banner */}
            <div className="p-5 sm:p-6 rounded-3xl bg-white border border-stone-200/90 shadow-card flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div className="flex items-start gap-3.5">
                <div className="w-10 h-10 rounded-2xl bg-botanical-50 border border-botanical-100 flex items-center justify-center shrink-0 text-botanical-800">
                  <MapPin className="w-5 h-5 text-botanical-700" />
                </div>
                <div className="space-y-1 min-w-0">
                  <div className="flex items-center gap-2">
                    <span className="text-[11px] font-bold uppercase tracking-wider text-botanical-700">
                      Delivering To
                    </span>
                    <span className="text-xs font-bold text-stone-900">{form.customer_name}</span>
                    <span className="text-xs text-stone-400">• {form.customer_phone}</span>
                  </div>
                  <p className="text-xs text-stone-600 line-clamp-2">
                    {form.shipping_street}{form.shipping_landmark ? `, ${form.shipping_landmark}` : ''}, {form.city}, {form.state} - {form.postal_code}
                  </p>
                  {(summary?.estimated_transit_days || stateRateObj?.estimated_days) && (
                    <div className="text-[11px] text-emerald-700 font-medium flex items-center gap-1 pt-0.5">
                      <Truck className="w-3 h-3 text-emerald-600" />
                      <span>Estimated delivery: {summary?.estimated_transit_days || stateRateObj?.estimated_days}</span>
                    </div>
                  )}
                </div>
              </div>
              <button
                type="button"
                onClick={() => {
                  setCurrentStep(1);
                  window.scrollTo({ top: 0, behavior: 'smooth' });
                }}
                className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-stone-200 text-stone-700 hover:bg-stone-50 text-xs font-bold transition shrink-0 cursor-pointer self-start sm:self-center"
              >
                <Edit2 className="w-3.5 h-3.5 text-botanical-700" />
                <span>Edit Address</span>
              </button>
            </div>

            {/* Specimen Items List */}
            <div className="p-6 sm:p-7 rounded-3xl bg-white border border-stone-200/90 shadow-card space-y-4">
              <div className="flex items-center justify-between pb-3 border-b border-stone-100">
                <h3 className="font-serif text-base font-bold text-stone-900 flex items-center gap-2">
                  <ShoppingBag className="w-5 h-5 text-botanical-800" />
                  <span>Botanical Specimens</span>
                </h3>
                <span className="text-xs font-semibold text-stone-500">
                  {cart.length} {cart.length === 1 ? 'Specimen' : 'Specimens'}
                </span>
              </div>

              <div className="space-y-3 divide-y divide-stone-100">
                {cart.map((item) => (
                  <div key={item.id} className="pt-3 first:pt-0 flex items-center gap-4">
                    <div className="w-16 h-16 rounded-2xl overflow-hidden bg-sand-100 border border-stone-200/80 shrink-0 relative">
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
                      <h4 className="font-serif font-bold text-sm text-stone-900 truncate">
                        {item.productName}
                      </h4>
                      <p className="text-xs text-botanical-700 truncate">{item.variantTitle}</p>
                      <span className="text-xs text-stone-400">Qty: {item.quantity}</span>
                    </div>
                    <div className="text-right font-serif font-bold text-sm text-stone-900 tabular-nums">
                      {currencySymbol}{(Number(item.price || 0) * Number(item.quantity || 1)).toFixed(2)}
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Packaging and Voucher Card */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {/* Thermal Packaging Toggle */}
              <label className="flex items-center justify-between p-4 rounded-3xl bg-white border border-stone-200/90 shadow-card cursor-pointer hover:border-botanical-400 transition select-none">
                <div className="flex items-center gap-3 min-w-0 pr-2">
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
              <div className="p-4 rounded-3xl bg-white border border-stone-200/90 shadow-card flex items-center">
                {appliedCoupon ? (
                  <div className="flex items-center justify-between w-full p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 font-semibold">
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
                  <div className="relative flex items-center w-full">
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
            </div>

            {/* Detailed Pricing Breakdown Card */}
            <div className="p-6 sm:p-7 rounded-3xl bg-white border border-stone-200/90 shadow-card space-y-3 tabular-nums text-xs">
              <h3 className="font-serif text-base font-bold text-stone-900 pb-2 border-b border-stone-100">
                Cost Breakdown
              </h3>
              <div className="flex justify-between text-stone-600">
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
                <div className="flex justify-between text-stone-600">
                  <span>Thermal Root Protection</span>
                  <span className="font-semibold text-stone-800">{currencySymbol}{(Number(finalThermal) || 0).toFixed(2)}</span>
                </div>
              )}

              <div className="flex justify-between items-center text-stone-600">
                <div className="flex items-center gap-1.5 min-w-0">
                  <span className="truncate">
                    {summary?.shipping_state || form.state
                      ? `Delivery to ${summary?.shipping_state || form.state}`
                      : 'Carrier Climate Transit'}
                  </span>
                  {(summary?.is_state_rate_applied || stateRateObj) && (
                    <span className="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-botanical-100 text-botanical-800 shrink-0">
                      State Rate
                    </span>
                  )}
                </div>
                <span className="font-semibold text-stone-800 shrink-0">
                  {finalShipping === 0 ? (
                    <span className="text-emerald-700 font-bold uppercase text-[10px] bg-emerald-100/80 px-2 py-0.5 rounded-full">
                      Free
                    </span>
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

            {/* Step 2 Actions */}
            <div className="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
              <button
                type="button"
                onClick={() => {
                  setCurrentStep(1);
                  window.scrollTo({ top: 0, behavior: 'smooth' });
                }}
                className="h-12 px-6 rounded-full border border-stone-300 hover:bg-white text-stone-700 font-bold text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto order-2 sm:order-1"
              >
                <ChevronLeft className="w-4 h-4" />
                <span>Back to Address</span>
              </button>
              <button
                type="button"
                onClick={() => {
                  setCurrentStep(3);
                  window.scrollTo({ top: 0, behavior: 'smooth' });
                }}
                className="w-full sm:w-auto h-12 px-8 rounded-full bg-botanical-800 hover:bg-botanical-900 active:scale-98 text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-botanical-950/20 transition flex items-center justify-center gap-2 cursor-pointer order-1 sm:order-2"
              >
                <span>Continue to Payment</span>
                <ArrowRight className="w-4 h-4 text-emerald-300" />
              </button>
            </div>
          </div>
        )}

        {/* ================= STEP 3: PAYMENT ================= */}
        {currentStep === 3 && (
          <div className="space-y-6 animate-in fade-in duration-200">
            {/* Step 3 Title Banner */}
            <div className="flex items-center justify-between pb-2">
              <div>
                <span className="text-xs uppercase font-bold tracking-wider text-botanical-700">
                  Step 3 of 3
                </span>
                <h2 className="font-serif text-xl sm:text-2xl font-bold text-stone-900">
                  Payment Method & Final Confirmation
                </h2>
              </div>
              <span className="text-xs text-stone-500">
                Final Step
              </span>
            </div>

            {/* Quick Order Highlights Pill */}
            <div className="p-5 sm:p-6 rounded-3xl bg-white border border-stone-200/90 shadow-card flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div className="space-y-1">
                <span className="text-[10px] font-bold uppercase tracking-wider text-botanical-700">
                  Order Summary Snapshot
                </span>
                <div className="text-sm font-semibold text-stone-800">
                  Delivering to <strong className="text-stone-900">{form.customer_name}</strong> in {form.city}, {form.state}
                </div>
                <div className="text-xs text-stone-500">
                  {cart.length} {cart.length === 1 ? 'specimen' : 'specimens'} in climate-conditioned packaging
                </div>
              </div>
              <div className="flex items-center gap-3">
                <div className="text-right">
                  <span className="text-[10px] font-bold uppercase tracking-wider text-stone-400 block">Total Due</span>
                  <span className="font-serif text-2xl font-bold text-botanical-900 tabular-nums">
                    {currencySymbol}{(Number(finalTotal) || 0).toFixed(2)}
                  </span>
                </div>
                <button
                  type="button"
                  onClick={() => {
                    setCurrentStep(2);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                  }}
                  className="px-3.5 py-2 rounded-xl border border-stone-200 hover:bg-stone-50 text-xs font-semibold text-stone-700 transition cursor-pointer"
                >
                  Review
                </button>
              </div>
            </div>

            {/* Payment Method Selector */}
            <div className="p-6 sm:p-7 rounded-3xl bg-white border border-stone-200/90 shadow-card space-y-5">
              <div className="pb-3 border-b border-stone-100 flex items-center justify-between">
                <h3 className="font-serif text-base font-bold text-stone-900 flex items-center gap-2">
                  <CreditCard className="w-5 h-5 text-botanical-800" />
                  <span>Choose Payment Preference</span>
                </h3>
                <span className="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/60 flex items-center gap-1">
                  <ShieldCheck className="w-3.5 h-3.5" />
                  <span>Encrypted & Safe</span>
                </span>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
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
                    <div className="text-[10px] text-stone-500 mt-1 leading-tight">
                      Pay upon doorstep plant arrival & inspection
                    </div>
                  </div>
                  <div className="mt-3 flex items-center justify-between pt-2 border-t border-stone-200/50 text-[10px] font-bold text-botanical-800">
                    <span>Popular</span>
                    <div className={`w-4 h-4 rounded-full border flex items-center justify-center ${
                      form.payment_method === 'cod' ? 'border-botanical-700 bg-botanical-700 text-white' : 'border-stone-300'
                    }`}>
                      {form.payment_method === 'cod' && <div className="w-1.5 h-1.5 rounded-full bg-white" />}
                    </div>
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
                    <div className="text-[10px] text-stone-500 mt-1 leading-tight">
                      Google Pay, PhonePe, Paytm, QR code
                    </div>
                  </div>
                  <div className="mt-3 flex items-center justify-between pt-2 border-t border-stone-200/50 text-[10px] font-bold text-botanical-800">
                    <span>Zero fee</span>
                    <div className={`w-4 h-4 rounded-full border flex items-center justify-center ${
                      form.payment_method === 'upi' ? 'border-botanical-700 bg-botanical-700 text-white' : 'border-stone-300'
                    }`}>
                      {form.payment_method === 'upi' && <div className="w-1.5 h-1.5 rounded-full bg-white" />}
                    </div>
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
                    <div className="text-[10px] text-stone-500 mt-1 leading-tight">
                      Visa, MasterCard, RuPay, NetBanking
                    </div>
                  </div>
                  <div className="mt-3 flex items-center justify-between pt-2 border-t border-stone-200/50 text-[10px] font-bold text-botanical-800">
                    <span>Direct bank</span>
                    <div className={`w-4 h-4 rounded-full border flex items-center justify-center ${
                      form.payment_method === 'card' ? 'border-botanical-700 bg-botanical-700 text-white' : 'border-stone-300'
                    }`}>
                      {form.payment_method === 'card' && <div className="w-1.5 h-1.5 rounded-full bg-white" />}
                    </div>
                  </div>
                </label>
              </div>

              {/* Informative Payment Note */}
              <div className="p-3.5 rounded-2xl bg-sand-50/80 border border-stone-200/70 text-xs text-stone-600 flex items-start gap-2.5">
                <Sparkles className="w-4 h-4 text-amber-500 shrink-0 mt-0.5" />
                <div>
                  {form.payment_method === 'cod' && (
                    <span><strong>Cash on Delivery selected:</strong> Pay securely upon doorstep delivery after inspecting your living plants. No advance payment required.</span>
                  )}
                  {form.payment_method === 'upi' && (
                    <span><strong>Instant UPI selected:</strong> Complete your payment quickly using Google Pay, PhonePe, Paytm, or any BHIM UPI QR.</span>
                  )}
                  {form.payment_method === 'card' && (
                    <span><strong>Cards & Netbanking selected:</strong> 256-bit secure end-to-end processing across all major Indian credit/debit cards and netbanking.</span>
                  )}
                </div>
              </div>
            </div>

            {/* Place Order CTA Card */}
            <div className="p-6 sm:p-7 rounded-3xl bg-white border border-stone-200/90 shadow-card space-y-4">
              <button
                type="submit"
                disabled={submitting}
                className="w-full min-h-[58px] px-6 py-4 rounded-2xl bg-botanical-800 hover:bg-botanical-900 active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed text-white shadow-xl shadow-botanical-950/20 transition-all duration-200 cursor-pointer group flex items-center justify-between gap-3 border border-botanical-700/60"
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

              {/* Trust Guarantees */}
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
                <span className="text-stone-300">&bull;</span>
                <span className="inline-flex items-center gap-1.5 font-medium">
                  <Leaf className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                  <span>Live Plant Guarantee</span>
                </span>
              </div>
            </div>

            {/* Step 3 Actions */}
            <div className="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
              <button
                type="button"
                onClick={() => {
                  setCurrentStep(2);
                  window.scrollTo({ top: 0, behavior: 'smooth' });
                }}
                className="h-12 px-6 rounded-full border border-stone-300 hover:bg-white text-stone-700 font-bold text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 cursor-pointer w-full sm:w-auto"
              >
                <ChevronLeft className="w-4 h-4" />
                <span>Back to Order Summary</span>
              </button>
            </div>
          </div>
        )}
      </form>
    </div>
  );
}
