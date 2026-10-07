import React, { useState, useEffect, useMemo } from 'react';
import api from '../api/client';
import { useStore } from '../context/StoreContext';
import INDIAN_STATES from '../constants/indianStates';
import {
  X,
  ShieldCheck,
  CheckCircle2,
  Truck,
  CreditCard,
  MapPin,
  Lock,
  ArrowRight,
  Sparkles,
  AlertTriangle,
} from 'lucide-react';

export default function CheckoutModal() {
  const {
    checkoutOpen,
    setCheckoutOpen,
    cart,
    cartSubtotal,
    currencySymbol,
    freeShippingThreshold,
    defaultShippingFee,
    thermalPackRequested,
    appliedCoupon,
    enableStateShipping,
    stateShippingRates,
    getStateShippingRate,
    clearCart,
    navigateTo,
    addToast,
    user,
    token,
    setAuthModalOpen,
    updateUserProfile,
  } = useStore();

  // Guard against unauthenticated checkout in modal
  useEffect(() => {
    if (checkoutOpen && !user && !token) {
      setCheckoutOpen(false);
      addToast('Please sign in or create an account before checkout', 'info');
      setAuthModalOpen('login');
    }
  }, [checkoutOpen, user, token, setCheckoutOpen, setAuthModalOpen, addToast]);

  const [saveProfileForFuture, setSaveProfileForFuture] = useState(true);

  const [form, setForm] = useState({
    customer_name: user?.name || '',
    customer_email: user?.email || '',
    customer_phone: user?.phone || '',
    shipping_address_line1: user?.street_address || '',
    shipping_address_line2: '',
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

    setForm((prev) => ({
      ...prev,
      customer_name: defaultName || prev.customer_name,
      customer_email: defaultEmail || prev.customer_email,
      customer_phone: defaultPhone || prev.customer_phone,
      shipping_address_line1: defaultStreet || prev.shipping_address_line1,
      shipping_address_line2: defaultLandmark || prev.shipping_address_line2,
      city: defaultCity || prev.city,
      state: defaultState || prev.state,
      postal_code: defaultPostalCode || prev.postal_code,
    }));
  }, [user]);

  // Recalculate summary from server API whenever items, coupon, postal code, or state changes
  useEffect(() => {
    if (!checkoutOpen || cart.length === 0) return;

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
        if (res.success) {
          setSummary(res.breakdown);
        }
      } catch (err) {
        console.error('Summary calculation error:', err);
      } finally {
        setLoadingSummary(false);
      }
    }, 200);

    return () => clearTimeout(timer);
  }, [checkoutOpen, cart, appliedCoupon, thermalPackRequested, form.postal_code, form.state]);

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
  const isFreeShipping = cartSubtotal >= (freeShippingThreshold || 75);

  if (!checkoutOpen) return null;

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const handleOrderSubmit = async (e) => {
    e.preventDefault();
    if (!user && !token) {
      addToast('Please sign in or create an account to proceed to checkout', 'error');
      setAuthModalOpen('login');
      return;
    }
    if (cart.length === 0) return;

    try {
      setSubmitting(true);
      const orderPayload = {
        customer_name: form.customer_name,
        customer_email: form.customer_email,
        customer_phone: form.customer_phone,
        shipping_address_line1: form.shipping_address_line1,
        shipping_address_line2: form.shipping_address_line2,
        city: form.city,
        state: form.state,
        postal_code: form.postal_code,
        country: 'India',
        dispatch_notes: form.dispatch_notes,
        payment_method: form.payment_method,
        thermal_packaging_requested: thermalPackRequested,
        coupon_code: appliedCoupon?.code,
        items: cart.map((item) => ({
          variant_id: item.variantId,
          quantity: item.quantity,
        })),
      };

      const res = await api.createOrder(orderPayload);
      if (res.success && res.order) {
        setCreatedOrder(res.order);
        clearCart();

        if (saveProfileForFuture) {
          const profileToSave = {
            customer_name: form.customer_name.trim(),
            customer_email: form.customer_email.trim(),
            customer_phone: form.customer_phone.trim(),
            shipping_street: form.shipping_address_line1.trim(),
            shipping_landmark: form.shipping_address_line2.trim(),
            city: form.city.trim(),
            state: form.state.trim() || 'Karnataka',
            postal_code: form.postal_code.trim(),
          };
          try {
            localStorage.setItem('botanical_saved_delivery_profile', JSON.stringify(profileToSave));
          } catch (e) {}

          if (user && token && updateUserProfile) {
            updateUserProfile({
              phone: profileToSave.customer_phone,
              street_address: profileToSave.shipping_street,
              city: profileToSave.city,
              state: profileToSave.state,
              postal_code: profileToSave.postal_code,
            }).catch(() => {});
          }
        }

        addToast(`Order #${res.order.order_number} confirmed. Dispatched in climate-controlled packaging.`);
      }
    } catch (err) {
      console.error('Order creation error:', err);
      addToast(err.message || 'Failed to place botanical order', 'error');
    } finally {
      setSubmitting(false);
    }
  };

  const handleTrackCreatedOrder = () => {
    if (createdOrder) {
      const orderNo = createdOrder.order_number;
      setCheckoutOpen(false);
      setCreatedOrder(null);
      navigateTo('tracking', { trackingNumber: orderNo });
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
      {/* Backdrop */}
      <div
        onClick={() => !submitting && setCheckoutOpen(false)}
        className="fixed inset-0 bg-stone-950/60 backdrop-blur-sm transition-opacity"
      />

      <div className="relative w-full max-w-3xl bg-[#FAF9F6] rounded-3xl shadow-float border border-stone-200/80 overflow-hidden z-10 my-auto animate-in zoom-in-95 duration-200 max-h-[90vh] flex flex-col">
        {/* Header */}
        <div className="p-5 sm:p-6 border-b border-stone-200/70 flex items-center justify-between bg-white/70 backdrop-blur-md">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-2xl bg-botanical-800 text-white flex items-center justify-center shadow-xs">
              <Lock className="w-4 h-4" />
            </div>
            <div>
              <h2 className="font-serif text-lg sm:text-xl font-bold text-stone-900">
                {createdOrder ? 'Botanical order confirmed' : 'Climate-controlled botanical checkout'}
              </h2>
              <p className="text-[11px] text-stone-500">
                {createdOrder ? 'Your plant companion is being carefully packed' : 'Safe transit guarantee & direct greenhouse fulfillment'}
              </p>
            </div>
          </div>

          {!createdOrder && (
            <button
              onClick={() => setCheckoutOpen(false)}
              className="p-2 rounded-xl hover:bg-stone-200/60 text-stone-400 hover:text-stone-700 transition cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
          )}
        </div>

        {/* Content Body */}
        <div className="flex-1 overflow-y-auto p-6 sm:p-8">
          {createdOrder ? (
            /* Order Success Screen */
            <div className="text-center py-6 space-y-6">
              <div className="w-20 h-20 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center mx-auto shadow-inner">
                <CheckCircle2 className="w-10 h-10" />
              </div>

              <div>
                <span className="text-xs uppercase font-bold tracking-widest text-botanical-700">
                  Greenhouse order confirmed
                </span>
                <h3 className="font-serif text-2xl sm:text-3xl font-bold text-stone-900 mt-1">
                  Thank you, {createdOrder.customer_name}
                </h3>
                <p className="text-xs text-stone-500 mt-1">
                  Order reference:{' '}
                  <span className="font-mono font-bold text-stone-900 bg-white border border-stone-200 px-2 py-0.5 rounded-md text-sm">
                    #{createdOrder.order_number}
                  </span>
                </p>
              </div>

              <div className="p-6 rounded-2xl bg-white border border-stone-200/80 shadow-xs text-left max-w-lg mx-auto space-y-3.5 text-xs">
                <div className="flex justify-between border-b border-stone-100 pb-2.5">
                  <span className="text-stone-500">Total billed</span>
                  <span className="font-bold text-stone-900 tabular-nums">
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
                    {createdOrder.shipping_address_line1}, {createdOrder.city} {createdOrder.postal_code}
                  </span>
                </div>
                <div className="flex justify-between">
                  <span className="text-stone-500">Transit handling</span>
                  <span className="font-bold text-botanical-800 flex items-center gap-1.5">
                    <Sparkles className="w-3.5 h-3.5 text-amber-500" />
                    Greenhouse acclimation & inspection
                  </span>
                </div>
              </div>

              <div className="flex flex-col sm:flex-row gap-3 justify-center pt-4">
                <button
                  onClick={handleTrackCreatedOrder}
                  className="px-6 py-3.5 rounded-2xl bg-botanical-800 hover:bg-botanical-900 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2 cursor-pointer"
                >
                  <Truck className="w-4 h-4" />
                  <span>Track plant transit timeline</span>
                </button>
                <button
                  onClick={() => {
                    setCheckoutOpen(false);
                    setCreatedOrder(null);
                    navigateTo('catalog');
                  }}
                  className="px-6 py-3.5 rounded-2xl border border-stone-300 hover:bg-white text-stone-700 font-semibold text-xs transition cursor-pointer"
                >
                  Return to nursery catalog
                </button>
              </div>
            </div>
          ) : (
            /* Checkout Form */
            <form onSubmit={handleOrderSubmit} className="space-y-6">
              {/* 1. Contact Information */}
              <div>
                <div className="flex items-center justify-between mb-3">
                  <h3 className="text-xs font-bold uppercase tracking-wider text-stone-700 flex items-center gap-2">
                    <span className="w-5 h-5 rounded-full bg-botanical-100 text-botanical-800 flex items-center justify-center text-[10px] font-bold">1</span>
                    Customer & recipient details
                  </h3>
                  {user && (
                    <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-botanical-100 text-botanical-800">
                      <Sparkles className="w-3 h-3 text-amber-500" />
                      <span>Linked to member account</span>
                    </span>
                  )}
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <div>
                    <label className="block text-[11px] font-semibold text-stone-600 mb-1">Full name *</label>
                    <input
                      type="text"
                      name="customer_name"
                      required
                      placeholder="e.g. Priyanshu Roy"
                      value={form.customer_name}
                      onChange={handleInputChange}
                      className="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-[11px] font-semibold text-stone-600 mb-1">Email address *</label>
                    <input
                      type="email"
                      name="customer_email"
                      required
                      placeholder="name@example.com"
                      value={form.customer_email}
                      onChange={handleInputChange}
                      className="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-[11px] font-semibold text-stone-600 mb-1">Phone number *</label>
                    <input
                      type="tel"
                      name="customer_phone"
                      required
                      placeholder="+91 98765 43210"
                      value={form.customer_phone}
                      onChange={handleInputChange}
                      className="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none"
                    />
                  </div>
                </div>
              </div>

              {/* 2. Delivery Address */}
              <div>
                <h3 className="text-xs font-bold uppercase tracking-wider text-stone-700 mb-3 flex items-center gap-2">
                  <span className="w-5 h-5 rounded-full bg-botanical-100 text-botanical-800 flex items-center justify-center text-[10px] font-bold">2</span>
                  Delivery address & postal code
                </h3>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div className="sm:col-span-2">
                    <label className="block text-[11px] font-semibold text-stone-600 mb-1">Street address / Flat / House *</label>
                    <input
                      type="text"
                      name="shipping_address_line1"
                      required
                      placeholder="House number, flat name, street"
                      value={form.shipping_address_line1}
                      onChange={handleInputChange}
                      className="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-[11px] font-semibold text-stone-600 mb-1">Apartment / Landmark</label>
                    <input
                      type="text"
                      name="shipping_address_line2"
                      placeholder="Apartment, suite, or landmark (optional)"
                      value={form.shipping_address_line2}
                      onChange={handleInputChange}
                      className="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-[11px] font-semibold text-stone-600 mb-1">City *</label>
                    <input
                      type="text"
                      name="city"
                      required
                      placeholder="e.g. Bengaluru"
                      value={form.city}
                      onChange={handleInputChange}
                      className="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none"
                    />
                  </div>
                  <div>
                    <div className="flex items-center justify-between mb-1">
                      <label className="block text-[11px] font-semibold text-stone-600">State *</label>
                      {stateRateObj && (
                        <span className="text-[10px] font-bold text-botanical-800 bg-botanical-50 px-1.5 py-0.5 rounded border border-botanical-200/60">
                          {isFreeShipping ? 'Free Delivery' : `${currencySymbol}${parseFloat(stateRateObj.fee).toFixed(0)}`}
                        </span>
                      )}
                    </div>
                    <input
                      type="text"
                      name="state"
                      list="modal-indian-states-list"
                      required
                      placeholder="e.g. Karnataka"
                      value={form.state}
                      onChange={handleInputChange}
                      className="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none"
                    />
                    <datalist id="modal-indian-states-list">
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
                    {form.state && stateRateObj && (
                      <p className="mt-1 text-[10px] text-emerald-800 flex items-center gap-1 font-medium">
                        <CheckCircle2 className="w-3 h-3 text-emerald-600 shrink-0" />
                        <span>{stateRateObj.state}: {currencySymbol}{parseFloat(stateRateObj.fee).toFixed(2)} delivery{stateRateObj.estimated_days ? ` (${stateRateObj.estimated_days})` : ''}</span>
                      </p>
                    )}
                  </div>
                  <div>
                    <label className="block text-[11px] font-semibold text-stone-600 mb-1">Postal code (PIN) *</label>
                    <input
                      type="text"
                      name="postal_code"
                      required
                      placeholder="e.g. 560034"
                      value={form.postal_code}
                      onChange={handleInputChange}
                      className="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none"
                    />
                  </div>
                  <div className="sm:col-span-2">
                    <label className="block text-[11px] font-semibold text-stone-600 mb-1">Dispatch / Delivery instructions</label>
                    <input
                      type="text"
                      name="dispatch_notes"
                      placeholder="e.g. Please leave with security desk if absent, handle living plants with care"
                      value={form.dispatch_notes}
                      onChange={handleInputChange}
                      className="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 bg-white text-xs focus:ring-2 focus:ring-botanical-500 focus:outline-none"
                    />
                  </div>
                </div>
              </div>

              {/* 3. Payment Method */}
              <div>
                <h3 className="text-xs font-bold uppercase tracking-wider text-stone-700 mb-3 flex items-center gap-2">
                  <span className="w-5 h-5 rounded-full bg-botanical-100 text-botanical-800 flex items-center justify-center text-[10px] font-bold">3</span>
                  Payment preference
                </h3>
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                  <label
                    className={`p-4 rounded-2xl border-2 cursor-pointer transition flex items-center gap-3 ${
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
                      <div className="text-xs font-bold text-stone-900">Cash on delivery</div>
                      <div className="text-[10px] text-stone-500 mt-0.5">Pay upon plant arrival</div>
                    </div>
                  </label>

                  <label
                    className={`p-4 rounded-2xl border-2 cursor-pointer transition flex items-center gap-3 ${
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
                      <div className="text-[10px] text-stone-500 mt-0.5">GPay, PhonePe, Paytm</div>
                    </div>
                  </label>

                  <label
                    className={`p-4 rounded-2xl border-2 cursor-pointer transition flex items-center gap-3 ${
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
                      <div className="text-xs font-bold text-stone-900">Cards & netbanking</div>
                      <div className="text-[10px] text-stone-500 mt-0.5">Visa, Mastercard, RuPay</div>
                    </div>
                  </label>
                </div>
              </div>

              {/* Order Breakdown Box */}
              {summary && (
                <div className="p-4 rounded-2xl bg-white border border-stone-200 text-xs space-y-2 tabular-nums">
                  <div className="flex justify-between text-stone-600">
                    <span>Items subtotal ({summary.item_count || cart.length} plant items)</span>
                    <span className="font-semibold text-stone-800">{currencySymbol}{(Number(summary.subtotal) || 0).toFixed(2)}</span>
                  </div>

                  {Number(summary.discount) > 0 && (
                    <div className="flex justify-between text-emerald-700 font-semibold">
                      <span>Voucher discount</span>
                      <span>-{currencySymbol}{(Number(summary.discount) || 0).toFixed(2)}</span>
                    </div>
                  )}

                  {Number(summary.thermal_packaging || summary.insulation_fee) > 0 && (
                    <div className="flex justify-between text-stone-600">
                      <span>Thermal packaging pack</span>
                      <span className="font-semibold text-stone-800">{currencySymbol}{(Number(summary.thermal_packaging || summary.insulation_fee) || 0).toFixed(2)}</span>
                    </div>
                  )}

                  <div className="space-y-0.5">
                    <div className="flex justify-between text-stone-600">
                      <div className="flex items-center gap-1.5 min-w-0">
                        <span className="truncate">
                          {summary.shipping_state || form.state
                            ? `Delivery (${summary.shipping_state || form.state})`
                            : 'Carrier transit delivery'}
                        </span>
                        {(summary.is_state_rate_applied || stateRateObj) && (
                          <span className="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-botanical-100 text-botanical-800 shrink-0">
                            State Rate
                          </span>
                        )}
                      </div>
                      <span className="font-semibold text-stone-800 shrink-0">
                        {summary.qualifies_for_free_shipping ? (
                          <span className="text-emerald-700 font-bold uppercase text-[10px]">Free shipping</span>
                        ) : (
                          `${currencySymbol}${(Number(summary.shipping) || 0).toFixed(2)}`
                        )}
                      </span>
                    </div>
                    {(summary.estimated_transit_days || stateRateObj?.estimated_days) && (
                      <div className="flex justify-between text-[10px] text-stone-500">
                        <span>Est. transit:</span>
                        <span className="font-medium text-stone-700">
                          {summary.estimated_transit_days || stateRateObj?.estimated_days}
                        </span>
                      </div>
                    )}
                  </div>

                  <div className="flex justify-between text-sm font-bold text-stone-900 pt-2 border-t border-stone-100">
                    <span>Grand total</span>
                    <span>{currencySymbol}{(Number(summary.grand_total || summary.total) || 0).toFixed(2)}</span>
                  </div>
                </div>
              )}

              {/* Submit Button */}
              <div className="pt-2 space-y-2.5">
                <button
                  type="submit"
                  disabled={submitting}
                  className="w-full min-h-[56px] px-5 py-3.5 rounded-2xl bg-botanical-800 hover:bg-botanical-900 active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed text-white shadow-xl shadow-botanical-950/20 transition-all duration-200 cursor-pointer group flex items-center justify-between gap-3 border border-botanical-700/60"
                >
                  {submitting ? (
                    <div className="flex items-center justify-center gap-2.5 w-full py-1 text-xs font-bold uppercase tracking-wider text-emerald-200">
                      <div className="w-4 h-4 border-2 border-emerald-300 border-t-transparent rounded-full animate-spin" />
                      <span>Transmitting order to greenhouse...</span>
                    </div>
                  ) : (
                    <>
                      <div className="flex items-center gap-2.5 text-left min-w-0">
                        <div className="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center shrink-0 border border-white/10 group-hover:scale-105 transition-transform">
                          <Lock className="w-4 h-4 text-emerald-300" />
                        </div>
                        <div className="min-w-0">
                          <div className="font-bold text-sm sm:text-base text-white leading-tight truncate">
                            Confirm & Place Order
                          </div>
                          <div className="text-[11px] text-emerald-200/80 font-medium truncate">
                            Safe transit guarantee
                          </div>
                        </div>
                      </div>

                      <div className="flex items-center gap-2 shrink-0">
                        <span className="px-3 py-1.5 rounded-xl bg-white/15 text-white font-serif font-bold text-sm sm:text-base tabular-nums border border-white/10 shadow-xs">
                          {currencySymbol}{(Number(summary?.grand_total || summary?.total || cartSubtotal) || 0).toFixed(2)}
                        </span>
                        <div className="w-7 h-7 rounded-xl bg-emerald-400/20 text-emerald-300 flex items-center justify-center group-hover:translate-x-1 transition-transform">
                          <ArrowRight className="w-3.5 h-3.5" />
                        </div>
                      </div>
                    </>
                  )}
                </button>

                <div className="flex items-center justify-center gap-3 text-[11px] text-stone-500 select-none py-0.5">
                  <span className="inline-flex items-center gap-1 font-medium">
                    <ShieldCheck className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                    <span>256-Bit Encrypted</span>
                  </span>
                  <span className="text-stone-300">&bull;</span>
                  <span className="inline-flex items-center gap-1 font-medium">
                    <Truck className="w-3.5 h-3.5 text-botanical-700 shrink-0" />
                    <span>Climate Transit Insured</span>
                  </span>
                </div>
              </div>
            </form>
          )}
        </div>
      </div>
    </div>
  );
}
