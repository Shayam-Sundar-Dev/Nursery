import React, { useState, useEffect } from 'react';
import { useStore } from '../context/StoreContext';
import {
  X,
  ShoppingBag,
  Trash2,
  Plus,
  Minus,
  Tag,
  ShieldCheck,
  Truck,
  Sparkles,
  ArrowRight,
  Leaf,
  ChevronDown,
  User,
  MapPin,
  Lock,
} from 'lucide-react';

export default function CartDrawer() {
  const {
    cartOpen,
    setCartOpen,
    cart,
    cartCount,
    cartSubtotal,
    currencySymbol,
    freeShippingThreshold,
    qualifiesForFreeShipping,
    thermalPackRequested,
    setThermalPackRequested,
    thermalPackagingFee,
    estimatedShippingFee,
    estimatedTotal,
    updateQuantity,
    removeFromCart,
    appliedCoupon,
    couponLoading,
    applyCouponCode,
    removeCoupon,
    setCheckoutOpen,
    navigateTo,
    user,
    token,
    setAuthModalOpen,
    addToast,
  } = useStore();

  const [couponInput, setCouponInput] = useState('');
  const [showCouponInput, setShowCouponInput] = useState(Boolean(appliedCoupon));

  // Lock body scroll while cart drawer is open & handle Escape key
  useEffect(() => {
    if (!cartOpen) return;

    const prevOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';

    const handleKeyDown = (e) => {
      if (e.key === 'Escape') setCartOpen(false);
    };
    window.addEventListener('keydown', handleKeyDown);

    return () => {
      document.body.style.overflow = prevOverflow;
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [cartOpen, setCartOpen]);

  if (!cartOpen) return null;

  const freeShippingRemainder = Math.max(0, freeShippingThreshold - cartSubtotal);
  const freeShippingProgress = Math.min(100, (cartSubtotal / freeShippingThreshold) * 100);

  const handleApplyCoupon = (e) => {
    e.preventDefault();
    if (couponInput.trim()) {
      applyCouponCode(couponInput.trim());
      setCouponInput('');
    }
  };

  const handleProceedToCheckout = () => {
    if (!user && !token) {
      addToast('Please sign in or create an account to proceed to checkout', 'info');
      setAuthModalOpen('login');
      return;
    }
    setCartOpen(false);
    navigateTo('checkout');
  };

  return (
    <div className="fixed inset-0 z-50 overflow-hidden">
      {/* Blurred Dimming Backdrop */}
      <div
        onClick={() => setCartOpen(false)}
        className="fixed inset-0 bg-stone-950/70 backdrop-blur-sm transition-opacity duration-300 animate-in fade-in"
        aria-hidden="true"
      />

      {/* Slide-over Container: 100% full-width on mobile, max-w-md on desktop */}
      <div className="fixed inset-y-0 right-0 w-full sm:max-w-md flex z-10">
        <div className="w-full h-full bg-white sm:rounded-l-[2rem] shadow-2xl border-l border-stone-200/90 flex flex-col justify-between animate-in slide-in-from-right duration-300 overflow-hidden">
          
          {/* Header Bar */}
          <div className="px-5 py-4 border-b border-stone-100 flex items-center justify-between bg-white z-20 shrink-0">
            <div className="flex items-center gap-3">
              <div className="w-9 h-9 rounded-xl bg-botanical-800 text-white flex items-center justify-center shadow-xs">
                <ShoppingBag className="w-4 h-4 text-emerald-300" />
              </div>
              <div>
                <h2 className="font-serif text-lg font-bold text-stone-900 leading-tight">
                  Botanical Cart
                </h2>
                <span className="text-xs text-stone-500 font-medium">
                  {cartCount} living {cartCount === 1 ? 'specimen' : 'specimens'}
                </span>
              </div>
            </div>

            <button
              onClick={() => setCartOpen(false)}
              className="w-9 h-9 rounded-full bg-sand-100 hover:bg-sand-200/80 text-stone-600 hover:text-stone-900 transition flex items-center justify-center border border-stone-200/60 active:scale-95 cursor-pointer"
              aria-label="Close cart"
            >
              <X className="w-4 h-4" />
            </button>
          </div>

          {/* Free Shipping Progress Meter */}
          <div className="px-5 py-2.5 bg-emerald-50/70 border-b border-emerald-100/80 shrink-0">
            <div className="flex items-center justify-between text-xs font-semibold mb-1">
              <span className="text-emerald-950 flex items-center gap-1.5 text-[11px] font-bold">
                <Truck className="w-3.5 h-3.5 text-emerald-700 shrink-0" />
                {qualifiesForFreeShipping
                  ? '🎉 Unlocked: FREE Climate-Protected Shipping'
                  : `Add ${currencySymbol}${freeShippingRemainder.toFixed(2)} more for FREE transit`}
              </span>
              <span className="font-bold text-emerald-800 text-xs tabular-nums">{Math.round(freeShippingProgress)}%</span>
            </div>
            <div className="w-full bg-emerald-200/60 h-1.5 rounded-full overflow-hidden">
              <div
                className="bg-emerald-600 h-full rounded-full transition-all duration-500 ease-out shadow-xs"
                style={{ width: `${freeShippingProgress}%` }}
              />
            </div>
          </div>

          {/* Cart Items List: Clean airy rows with hairline dividers */}
          <div className="flex-1 overflow-y-auto px-5 divide-y divide-stone-100 min-h-0">
            {cart.length === 0 ? (
              <div className="h-full flex flex-col items-center justify-center text-center py-12 px-4">
                <div className="w-16 h-16 rounded-3xl bg-sand-100 text-stone-400 flex items-center justify-center mb-4 shadow-subtle border border-sand-200">
                  <ShoppingBag className="w-7 h-7 text-botanical-700" />
                </div>
                <h3 className="font-serif text-xl font-bold text-stone-800">Your Botanical Cart is Empty</h3>
                <p className="text-xs text-stone-500 mt-1.5 max-w-xs leading-relaxed">
                  Explore acclimatized rare houseplants, terracotta planters, and specialized botanical care essentials.
                </p>
                <div className="flex flex-col gap-2.5 mt-6 w-full max-w-xs">
                  <button
                    onClick={() => {
                      setCartOpen(false);
                      navigateTo('catalog');
                    }}
                    className="w-full h-11 px-6 rounded-full bg-botanical-800 hover:bg-botanical-900 text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-botanical-900/10 transition cursor-pointer active:scale-98 flex items-center justify-center gap-2"
                  >
                    <span>Explore Plant Collection</span>
                    <ArrowRight className="w-3.5 h-3.5 text-emerald-300" />
                  </button>

                  {!user && (
                    <button
                      onClick={() => {
                        setCartOpen(false);
                        setAuthModalOpen('login');
                      }}
                      className="w-full h-10 rounded-full border border-stone-200 hover:bg-sand-50 text-stone-700 font-bold text-xs transition cursor-pointer flex items-center justify-center gap-1.5"
                    >
                      <User className="w-3.5 h-3.5 text-stone-400" />
                      <span>Sign In to Sync Saved Cart</span>
                    </button>
                  )}
                </div>
              </div>
            ) : (
              cart.map((item) => (
                <div
                  key={item.id}
                  className="py-4 first:pt-4 last:pb-4 flex gap-3.5 items-center"
                >
                  {/* Strict Fixed-Size Image Container (Guaranteed 80x80) */}
                  <div className="w-20 h-20 rounded-2xl overflow-hidden bg-sand-100 border border-stone-200/80 shrink-0 relative shadow-2xs">
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

                  {/* Info & Controls with matched 80px height */}
                  <div className="flex-1 min-w-0 flex flex-col justify-between h-20">
                    <div className="flex items-start justify-between gap-2">
                      <div className="min-w-0 pr-1">
                        <h4 className="font-serif font-bold text-sm text-stone-900 truncate leading-snug">
                          {item.productName}
                        </h4>
                        <p className="text-xs text-botanical-700 font-medium truncate mt-0.5">{item.variantTitle}</p>
                      </div>

                      <button
                        onClick={() => removeFromCart(item.variantId || item.id)}
                        className="w-7 h-7 rounded-full text-stone-400 hover:text-rose-600 hover:bg-rose-50 transition flex items-center justify-center cursor-pointer shrink-0"
                        title="Remove item"
                        aria-label="Remove item"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
                    </div>

                    <div className="flex items-center justify-between mt-auto">
                      {/* Stepper with explicit equal touch sizes */}
                      <div className="h-7 flex items-center border border-stone-200 rounded-full bg-sand-50 p-0.5 shadow-2xs">
                        <button
                          onClick={() => updateQuantity(item.variantId, -1)}
                          className="w-6 h-6 rounded-full bg-white hover:bg-stone-100 text-stone-700 flex items-center justify-center text-xs font-bold transition active:scale-95 cursor-pointer"
                          aria-label="Decrease quantity"
                        >
                          <Minus className="w-3 h-3" />
                        </button>
                        <span className="w-6 text-center text-xs font-bold text-stone-800 tabular-nums">
                          {item.quantity}
                        </span>
                        <button
                          onClick={() => updateQuantity(item.variantId, 1)}
                          className="w-6 h-6 rounded-full bg-white hover:bg-stone-100 text-stone-700 flex items-center justify-center text-xs font-bold transition active:scale-95 cursor-pointer"
                          aria-label="Increase quantity"
                        >
                          <Plus className="w-3 h-3" />
                        </button>
                      </div>

                      <div className="text-right">
                        <span className="font-serif font-bold text-sm text-stone-900 tabular-nums">
                          {currencySymbol}{(item.price * item.quantity).toFixed(2)}
                        </span>
                        {item.quantity > 1 && (
                          <span className="text-[10px] text-stone-400 block tabular-nums">
                            {currencySymbol}{item.price.toFixed(2)} ea
                          </span>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              ))
            )}
          </div>

          {/* Footer & Checkout Action with safe bottom spacing */}
          {cart.length > 0 && (
            <div className="p-4 sm:p-5 border-t border-stone-200/90 bg-sand-50/70 space-y-3 shrink-0 pb-7 sm:pb-5">
              {/* Thermal Packaging Toggle Card */}
              <label className="flex items-center justify-between p-2.5 rounded-xl bg-white border border-stone-200/90 shadow-2xs cursor-pointer hover:border-botanical-400 transition select-none">
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
                      <span>72-Hour Thermal Root Protection</span>
                    </div>
                    <p className="text-[10px] text-stone-500 truncate leading-tight">Climate-insulated wrap guards delicate roots</p>
                  </div>
                </div>
                <span className="text-xs font-bold text-botanical-800 tabular-nums shrink-0">
                  +{currencySymbol}{thermalPackagingFee.toFixed(2)}
                </span>
              </label>

              {/* Promo Code: Clean Collapsible Form */}
              <div>
                {appliedCoupon ? (
                  <div className="flex items-center justify-between p-2 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 font-semibold">
                    <div className="flex items-center gap-1.5 min-w-0 truncate">
                      <Tag className="w-3.5 h-3.5 text-emerald-700 shrink-0" />
                      <span className="truncate">{appliedCoupon.code} (-{currencySymbol}{appliedCoupon.calculated_discount.toFixed(2)})</span>
                    </div>
                    <button
                      onClick={removeCoupon}
                      className="text-emerald-700 hover:text-rose-600 text-[11px] font-bold underline cursor-pointer shrink-0"
                    >
                      Remove
                    </button>
                  </div>
                ) : showCouponInput ? (
                  <form onSubmit={handleApplyCoupon} className="relative flex items-center">
                    <input
                      type="text"
                      value={couponInput}
                      onChange={(e) => setCouponInput(e.target.value)}
                      placeholder="Promo code (e.g. SPRINGBLOOM)"
                      className="w-full h-9 pl-3 pr-20 rounded-xl border border-stone-200 bg-white text-xs uppercase font-mono font-semibold placeholder:normal-case placeholder:font-sans focus:outline-none focus:ring-2 focus:ring-botanical-500/20 focus:border-botanical-600 transition"
                      autoFocus
                    />
                    <button
                      type="submit"
                      disabled={couponLoading || !couponInput.trim()}
                      className="absolute right-1 top-1 bottom-1 px-3 rounded-lg bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs disabled:opacity-40 transition cursor-pointer"
                    >
                      {couponLoading ? '...' : 'Apply'}
                    </button>
                  </form>
                ) : (
                  <button
                    type="button"
                    onClick={() => setShowCouponInput(true)}
                    className="text-xs font-semibold text-stone-500 hover:text-botanical-800 transition inline-flex items-center gap-1.5 cursor-pointer py-0.5"
                  >
                    <Tag className="w-3.5 h-3.5 text-stone-400" />
                    <span>Have a promo code?</span>
                  </button>
                )}
              </div>

              {/* Price Breakdown */}
              <div className="space-y-1 text-xs text-stone-600 pt-1.5 border-t border-stone-200/80 tabular-nums">
                <div className="flex justify-between">
                  <span>Botanical subtotal</span>
                  <span className="font-semibold text-stone-800">{currencySymbol}{cartSubtotal.toFixed(2)}</span>
                </div>

                {appliedCoupon && (
                  <div className="flex justify-between text-emerald-700 font-semibold">
                    <span>Discount ({appliedCoupon.code})</span>
                    <span>-{currencySymbol}{appliedCoupon.calculated_discount.toFixed(2)}</span>
                  </div>
                )}

                {thermalPackRequested && (
                  <div className="flex justify-between">
                    <span>Thermal protection</span>
                    <span className="font-semibold text-stone-800">{currencySymbol}{thermalPackagingFee.toFixed(2)}</span>
                  </div>
                )}

                <div className="flex justify-between">
                  <span>Climate transit</span>
                  <span className="font-semibold text-stone-800">
                    {qualifiesForFreeShipping ? (
                      <span className="text-emerald-700 font-bold uppercase text-[10px] bg-emerald-100/80 px-2 py-0.5 rounded-full">Free</span>
                    ) : (
                      `${currencySymbol}${estimatedShippingFee.toFixed(2)}`
                    )}
                  </span>
                </div>

                <div className="flex justify-between items-baseline text-base font-bold text-stone-900 pt-1.5 border-t border-stone-200">
                  <span className="font-serif">Estimated total</span>
                  <span className="font-serif text-xl font-bold text-botanical-900">{currencySymbol}{estimatedTotal.toFixed(2)}</span>
                </div>
              </div>

              {/* Primary Checkout CTA */}
              <button
                onClick={handleProceedToCheckout}
                className="h-12 w-full rounded-full bg-botanical-800 hover:bg-botanical-900 active:scale-[0.98] text-white font-bold text-xs uppercase tracking-widest flex items-center justify-center gap-2.5 shadow-lg shadow-botanical-950/20 transition-all duration-200 cursor-pointer group"
              >
                {!user && !token ? (
                  <>
                    <Lock className="w-4 h-4 text-emerald-300" />
                    <span>Sign In to Proceed to Checkout</span>
                  </>
                ) : (
                  <>
                    <span>Proceed to Botanical Checkout</span>
                    <ArrowRight className="w-4 h-4 text-emerald-300 group-hover:translate-x-1 transition-transform" />
                  </>
                )}
              </button>

              {!user && !token && (
                <div className="flex items-center justify-center gap-1.5 text-[11px] text-amber-800 bg-amber-50/90 border border-amber-200/80 rounded-xl py-1.5 px-3">
                  <Lock className="w-3.5 h-3.5 text-amber-600 shrink-0" />
                  <span className="font-medium">Sign in is required before proceeding to checkout</span>
                </div>
              )}

              {/* Location-based Pricing Notice */}
              <div className="flex items-center justify-center gap-1.5 pt-1 text-[11px] text-stone-600 text-center">
                <MapPin className="w-3.5 h-3.5 text-botanical-700 shrink-0" />
                <span className="font-bold text-stone-700">Estimated amount may vary based on the location</span>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
