import React, { useState, useEffect } from 'react';
import api from '../api/client';
import { useStore } from '../context/StoreContext';
import {
  X,
  ShoppingBag,
  Sun,
  Droplets,
  Wind,
  ShieldAlert,
  ShieldCheck,
  Sparkles,
  Heart,
  Leaf,
  Check,
  ThermometerSnowflake,
} from 'lucide-react';

export default function ProductDetailModal() {
  const {
    activeProductSlug,
    closeProductModal,
    currencySymbol,
    addToCart,
    setCartOpen,
    toggleWishlist,
    isWishlisted,
    user,
  } = useStore();

  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [selectedVariant, setSelectedVariant] = useState(null);
  const [activeImage, setActiveImage] = useState('');
  const [quantity, setQuantity] = useState(1);

  // Lock body scroll while modal is active & listen for Escape key
  useEffect(() => {
    if (!activeProductSlug) {
      setProduct(null);
      return;
    }

    const prevOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';

    const handleKeyDown = (e) => {
      if (e.key === 'Escape') closeProductModal();
    };
    window.addEventListener('keydown', handleKeyDown);

    async function loadDetail() {
      try {
        setLoading(true);
        const res = await api.getProductBySlug(activeProductSlug);
        if (res.success && res.data) {
          setProduct(res.data);
          const initialVariant = res.data.variants?.[0] || null;
          setSelectedVariant(initialVariant);
          setActiveImage(res.data.primary_image_url || res.data.gallery_urls?.[0] || '');
          setQuantity(1);
        }
      } catch (err) {
        console.error('Failed to load product detail:', err);
      } finally {
        setLoading(false);
      }
    }

    loadDetail();

    return () => {
      document.body.style.overflow = prevOverflow;
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [activeProductSlug]);

  if (!activeProductSlug) return null;

  const handleAddToCart = () => {
    if (!product || !selectedVariant) return;
    const added = addToCart(product, selectedVariant, quantity);
    if (added) {
      closeProductModal();
      setCartOpen(true);
    }
  };

  const care = product?.care_attribute;
  const price = parseFloat(selectedVariant?.price || product?.base_price || 0);
  const compareAt = selectedVariant?.compare_at_price ? parseFloat(selectedVariant.compare_at_price) : null;
  const inStock = (selectedVariant?.stock_quantity ?? 10) > 0;

  return (
    <div className="fixed inset-0 z-50 flex flex-col justify-end md:justify-center md:items-center p-0 md:p-6 overflow-hidden">
      {/* Blurred Organic Backdrop */}
      <div
        onClick={closeProductModal}
        className="fixed inset-0 bg-stone-950/70 backdrop-blur-md transition-opacity duration-300"
        aria-hidden="true"
      />

      {/* Main Modal Container: Mobile Bottom Sheet (h-[92vh]) / Desktop Floating Modal */}
      <div className="relative w-full md:max-w-4xl max-h-[92vh] md:max-h-[88vh] bg-white rounded-t-[2rem] md:rounded-[2rem] shadow-2xl flex flex-col overflow-hidden z-10 animate-in slide-in-from-bottom-6 md:zoom-in-95 duration-300 border border-stone-200/90">
        
        {/* Top Header Bar with Mobile Drag Handle & Dedicated Close Button */}
        <div className="flex-shrink-0 px-5 pt-3 pb-3 border-b border-stone-100 flex items-center justify-between bg-white z-20">
          {/* Mobile Handle indicator */}
          <div className="md:hidden absolute top-2 inset-x-0 flex justify-center pointer-events-none">
            <div className="w-10 h-1 bg-stone-300 rounded-full" />
          </div>

          <div className="flex items-center gap-2 pt-1 md:pt-0">
            <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold tracking-wider uppercase bg-botanical-100/90 text-botanical-800 border border-botanical-200/60 shadow-2xs">
              <Leaf className="w-3 h-3 text-botanical-600" />
              <span>{product?.category?.name || 'Botanical Living Specimen'}</span>
            </div>
            {care?.is_pet_friendly && (
              <span className="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                🐾 Pet Safe
              </span>
            )}
          </div>

          {/* Close Button - Clean, isolated, never overlaps content */}
          <button
            onClick={closeProductModal}
            className="w-9 h-9 rounded-full bg-sand-100 hover:bg-sand-200/80 text-stone-600 hover:text-stone-900 flex items-center justify-center transition active:scale-95 cursor-pointer border border-stone-200/60"
            aria-label="Close modal"
          >
            <X className="w-4 h-4" />
          </button>
        </div>

        {/* Scrollable Content Area */}
        <div className="flex-1 overflow-y-auto min-h-0">
          {loading || !product ? (
            <div className="p-16 flex flex-col items-center justify-center min-h-[360px]">
              <div className="w-10 h-10 border-3 border-botanical-200 border-t-botanical-700 rounded-full animate-spin mb-4" />
              <p className="text-xs font-semibold text-stone-500 tracking-wide uppercase">
                Retrieving botanical care profile...
              </p>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-stone-100">
              
              {/* Left Column: Visual Showcase */}
              <div className="p-5 sm:p-7 bg-sand-50/60 flex flex-col justify-between space-y-4">
                <div className="space-y-3.5">
                  {/* Hero Selected Image */}
                  <div className="relative aspect-[4/3] sm:aspect-square w-full rounded-2xl sm:rounded-3xl overflow-hidden bg-sand-100 shadow-sm border border-stone-200/80">
                    <img
                      src={activeImage || product.primary_image_url}
                      alt={product.name}
                      onError={(e) => {
                        e.currentTarget.onerror = null;
                        e.currentTarget.src = 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?auto=format&fit=crop&w=800&q=80';
                      }}
                      className="w-full h-full object-cover object-center transition-all duration-500"
                    />

                    {care?.is_pet_friendly && (
                      <span className="sm:hidden absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-botanical-900/90 text-emerald-200 border border-emerald-500/40 backdrop-blur-md shadow-2xs">
                        🐾 Pet Safe
                      </span>
                    )}

                    <div className="absolute bottom-3 right-3 px-2.5 py-1 rounded-full bg-stone-900/60 backdrop-blur-md text-white text-[10px] font-mono font-medium">
                      Living Greenhouse
                    </div>
                  </div>

                  {/* Gallery Thumbnails */}
                  {Array.isArray(product.gallery_urls) && product.gallery_urls.length > 1 && (
                    <div className="flex gap-2 overflow-x-auto pb-1 pt-0.5 no-scrollbar">
                      {[product.primary_image_url, ...product.gallery_urls].filter(Boolean).map((img, i) => (
                        <button
                          key={i}
                          onClick={() => setActiveImage(img)}
                          className={`w-14 h-14 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl overflow-hidden flex-shrink-0 border-2 transition-all duration-200 cursor-pointer ${
                            activeImage === img
                              ? 'border-botanical-700 ring-2 ring-botanical-600/30 scale-105'
                              : 'border-transparent opacity-70 hover:opacity-100'
                          }`}
                        >
                          <img src={img} alt={`Gallery view ${i}`} className="w-full h-full object-cover" />
                        </button>
                      ))}
                    </div>
                  )}
                </div>

                {/* Climate Transit Guarantee Note */}
                <div className="p-3.5 sm:p-4 rounded-2xl bg-white border border-stone-200/80 text-xs text-stone-600 space-y-1 shadow-2xs">
                  <div className="font-bold text-stone-900 flex items-center gap-2">
                    <ThermometerSnowflake className="w-4 h-4 text-botanical-700 shrink-0" />
                    <span>72-Hour Climate Insulated Transit</span>
                  </div>
                  <p className="text-[11px] text-stone-500 leading-relaxed font-normal">
                    Living specimens travel in breathable, thermo-protected packaging guaranteeing root health upon doorstep arrival.
                  </p>
                </div>
              </div>

              {/* Right Column: Botanical Details & Specs */}
              <div className="p-5 sm:p-7 space-y-5">
                {/* Title & Botanical Classification */}
                <div>
                  <h2 className="font-serif text-2xl sm:text-3xl font-bold text-stone-900 leading-tight">
                    {product.name}
                  </h2>
                  {product.botanical_name && (
                    <div className="font-serif italic text-xs sm:text-sm text-botanical-700 font-medium mt-1">
                      {product.botanical_name}
                    </div>
                  )}

                  {/* Pricing Bar */}
                  <div className="flex items-baseline gap-3 mt-3 tabular-nums">
                    <span className="font-serif text-2xl sm:text-3xl font-bold text-stone-900 tracking-tight">
                      {currencySymbol}{price.toFixed(2)}
                    </span>
                    {compareAt && compareAt > price && (
                      <span className="text-xs sm:text-sm text-stone-400 line-through">
                        {currencySymbol}{compareAt.toFixed(2)}
                      </span>
                    )}
                    <span className={`text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider ${
                      inStock ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'
                    }`}>
                      {inStock ? 'In Stock & Acclimatized' : 'Propagating in Nursery'}
                    </span>
                  </div>
                </div>

                {/* Description */}
                <p className="text-xs sm:text-sm text-stone-600 leading-relaxed font-normal">
                  {product.description}
                </p>

                {/* Variant Selector (Planter Pot Size) */}
                {Array.isArray(product.variants) && product.variants.length > 0 && (
                  <div className="space-y-2 pt-1">
                    <label className="block text-[11px] font-bold uppercase tracking-wider text-stone-500">
                      Pot Diameter & Planter Size
                    </label>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                      {product.variants.map((v) => {
                        const isChosen = selectedVariant?.id === v.id;
                        return (
                          <button
                            key={v.id}
                            onClick={() => setSelectedVariant(v)}
                            className={`p-3 rounded-2xl border text-left transition-all duration-200 flex items-center justify-between cursor-pointer ${
                              isChosen
                                ? 'border-botanical-800 bg-botanical-50/70 ring-2 ring-botanical-700/20 shadow-2xs'
                                : 'border-stone-200/90 hover:border-stone-300 bg-white'
                            }`}
                          >
                            <div className="pr-2">
                              <span className="text-xs font-bold text-stone-900 block leading-snug">{v.title}</span>
                              <span className="text-xs font-semibold text-botanical-800 mt-0.5 block tabular-nums">
                                {currencySymbol}{parseFloat(v.price).toFixed(2)}
                              </span>
                            </div>
                            <div className={`w-5 h-5 rounded-full border flex items-center justify-center shrink-0 ${
                              isChosen
                                ? 'bg-botanical-800 border-botanical-800 text-white'
                                : 'border-stone-300 bg-stone-50'
                            }`}>
                              {isChosen && <Check className="w-3 h-3 stroke-[3]" />}
                            </div>
                          </button>
                        );
                      })}
                    </div>
                  </div>
                )}

                {/* Botanical Care Specs Profile */}
                {care && (
                  <div className="pt-3 border-t border-stone-100 space-y-2.5">
                    <h4 className="text-[11px] font-bold uppercase tracking-wider text-stone-400">
                      Botanical Care Profile
                    </h4>
                    <div className="grid grid-cols-2 gap-2 text-xs">
                      <div className="p-3 rounded-2xl bg-sand-50/80 border border-sand-200/70 flex items-start gap-2.5">
                        <div className="w-7 h-7 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                          <Sun className="w-3.5 h-3.5" />
                        </div>
                        <div className="min-w-0">
                          <div className="font-bold text-stone-800 text-xs">Lighting</div>
                          <div className="text-[11px] text-stone-500 capitalize truncate">{care.light_requirement?.replace('-', ' ')}</div>
                        </div>
                      </div>

                      <div className="p-3 rounded-2xl bg-sand-50/80 border border-sand-200/70 flex items-start gap-2.5">
                        <div className="w-7 h-7 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center shrink-0">
                          <Droplets className="w-3.5 h-3.5" />
                        </div>
                        <div className="min-w-0">
                          <div className="font-bold text-stone-800 text-xs">Hydration</div>
                          <div className="text-[11px] text-stone-500 capitalize truncate">{care.watering_frequency?.replace('-', ' ')}</div>
                        </div>
                      </div>

                      <div className="p-3 rounded-2xl bg-sand-50/80 border border-sand-200/70 flex items-start gap-2.5">
                        <div className="w-7 h-7 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center shrink-0">
                          <Wind className="w-3.5 h-3.5" />
                        </div>
                        <div className="min-w-0">
                          <div className="font-bold text-stone-800 text-xs">Humidity</div>
                          <div className="text-[11px] text-stone-500 capitalize truncate">{care.humidity_level || 'Average (40-60%)'}</div>
                        </div>
                      </div>

                      <div className="p-3 rounded-2xl bg-sand-50/80 border border-sand-200/70 flex items-start gap-2.5">
                        <div className={`w-7 h-7 rounded-xl flex items-center justify-center shrink-0 ${
                          care.is_pet_friendly ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'
                        }`}>
                          {care.is_pet_friendly ? (
                            <ShieldCheck className="w-3.5 h-3.5" />
                          ) : (
                            <ShieldAlert className="w-3.5 h-3.5" />
                          )}
                        </div>
                        <div className="min-w-0">
                          <div className="font-bold text-stone-800 text-xs">Pet Safety</div>
                          <div className="text-[11px] text-stone-500 truncate">{care.is_pet_friendly ? 'Pet Friendly' : 'Caution / Toxic'}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                )}
              </div>
            </div>
          )}
        </div>

        {/* Sticky Bottom Action Bar — ALWAYS VISIBLE, perfectly aligned */}
        {product && !loading && (
          <div className="flex-shrink-0 p-3.5 sm:p-4 bg-white/95 backdrop-blur-md border-t border-stone-200/80 flex items-center gap-2.5 sm:gap-3 z-30 shadow-[0_-4px_16px_rgba(0,0,0,0.04)]">
            {/* Stepper with explicit equal height h-11 */}
            <div className="h-11 flex items-center border border-stone-200 rounded-full bg-sand-50 p-1 shrink-0">
              <button
                onClick={() => setQuantity((q) => Math.max(1, q - 1))}
                className="w-8 h-8 rounded-full bg-white shadow-2xs hover:bg-stone-100 text-stone-700 font-bold text-sm flex items-center justify-center transition active:scale-95 cursor-pointer"
                aria-label="Decrease quantity"
              >
                -
              </button>
              <span className="w-8 text-center font-bold text-xs text-stone-900 tabular-nums">{quantity}</span>
              <button
                onClick={() => setQuantity((q) => q + 1)}
                className="w-8 h-8 rounded-full bg-white shadow-2xs hover:bg-stone-100 text-stone-700 font-bold text-sm flex items-center justify-center transition active:scale-95 cursor-pointer"
                aria-label="Increase quantity"
              >
                +
              </button>
            </div>

            {/* Primary Add to Cart Action with explicit equal height h-11 */}
            <button
              onClick={handleAddToCart}
              disabled={!inStock}
              className="h-11 flex-1 px-4 sm:px-6 rounded-full bg-botanical-800 hover:bg-botanical-900 disabled:bg-stone-300 text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-botanical-950/20 transition-all duration-200 flex items-center justify-center gap-2 active:scale-98 cursor-pointer shrink"
            >
              <ShoppingBag className="w-4 h-4 text-emerald-300 shrink-0" />
              <span className="tabular-nums truncate">
                Add {quantity} to Cart &bull; {currencySymbol}{(price * quantity).toFixed(2)}
              </span>
            </button>

            {/* Wishlist Button with explicit equal height h-11 */}
            <button
              type="button"
              onClick={() => product && toggleWishlist(product)}
              className={`w-11 h-11 rounded-full border transition-all duration-200 flex items-center justify-center active:scale-95 cursor-pointer shrink-0 ${
                product && isWishlisted(product.id)
                  ? 'bg-rose-50 border-rose-200 text-rose-600 shadow-2xs'
                  : 'border-stone-200 hover:bg-sand-50 text-stone-600 hover:text-rose-600'
              }`}
              title={!user ? 'Sign in to save to favorites' : product && isWishlisted(product.id) ? 'Remove from wishlist' : 'Add to wishlist'}
              aria-label="Wishlist toggle"
            >
              <Heart
                className={`w-4 h-4 ${
                  product && isWishlisted(product.id) ? 'fill-rose-500 text-rose-500' : ''
                }`}
              />
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
