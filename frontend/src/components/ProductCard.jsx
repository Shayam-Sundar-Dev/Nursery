import React from 'react';
import { useStore } from '../context/StoreContext';
import { ShoppingBag, Eye, Sun, Droplets, Heart, Sparkles, Check, ArrowRight } from 'lucide-react';

export default function ProductCard({ product }) {
  const { currencySymbol, addToCart, openProductModal, toggleWishlist, isWishlisted, user } = useStore();

  const primaryVariant = product.default_variant || product.variants?.[0];
  const startingPrice = parseFloat(primaryVariant?.price || product.base_price || 0);
  const compareAtPrice = primaryVariant?.compare_at_price ? parseFloat(primaryVariant.compare_at_price) : null;
  const isPetFriendly = product.care_attribute?.is_pet_friendly;
  const light = product.care_attribute?.light_requirement;
  const water = product.care_attribute?.watering_frequency;

  const discountPercent = compareAtPrice && compareAtPrice > startingPrice
    ? Math.round(((compareAtPrice - startingPrice) / compareAtPrice) * 100)
    : null;

  const handleQuickAdd = (e) => {
    e.stopPropagation();
    addToCart(product, primaryVariant, 1);
  };

  return (
    <article
      onClick={() => openProductModal(product.slug)}
      className="group relative z-0 p-2 rounded-[2rem] bg-stone-900/[0.03] border border-stone-200/80 hover:border-botanical-500/60 shadow-card hover:shadow-card-hover transition-all duration-500 flex flex-col cursor-pointer"
    >
      {/* Inner Core Enclosure */}
      <div className="rounded-[calc(2rem-0.5rem)] bg-white overflow-hidden flex flex-col flex-1 border border-stone-100/90 shadow-2xs">
        
        {/* Botanical Image Showcase */}
        <div className="relative aspect-[4/3.7] w-full bg-sand-100 overflow-hidden">
          <img
            src={product.primary_image_url || 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?auto=format&fit=crop&w=800&q=80'}
            alt={product.name}
            onError={(e) => {
              e.currentTarget.onerror = null;
              e.currentTarget.src = 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?auto=format&fit=crop&w=800&q=80';
            }}
            className="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700 ease-[cubic-bezier(0.32,0.72,0,1)]"
            loading="lazy"
          />

          {/* Ambient Bottom Gradient Scrim */}
          <div className="absolute inset-0 bg-gradient-to-t from-black/35 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none" />

          {/* Floating Top Badges */}
          <div className="absolute top-3 left-3 flex flex-wrap gap-1.5 z-10">
            {product.category && (
              <span className="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/95 backdrop-blur-md text-stone-700 shadow-subtle border border-white/60">
                {product.category.name}
              </span>
            )}
            {discountPercent && (
              <span className="inline-flex items-center gap-0.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-500 text-white shadow-subtle tabular-nums">
                -{discountPercent}%
              </span>
            )}
            {isPetFriendly && (
              <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-botanical-900/90 backdrop-blur-md text-emerald-200 border border-emerald-500/30 shadow-subtle">
                🐾 Pet Safe
              </span>
            )}
          </div>

          {/* Wishlist Floating Toggle */}
          <button
            type="button"
            onClick={(e) => {
              e.stopPropagation();
              toggleWishlist(product);
            }}
            className={`absolute top-3 right-3 z-20 w-8 h-8 rounded-full shadow-subtle backdrop-blur-md transition-all duration-300 flex items-center justify-center active:scale-90 cursor-pointer ${
              isWishlisted(product.id)
                ? 'bg-white text-rose-500 shadow-md ring-2 ring-rose-200/80 scale-105'
                : 'bg-white/80 hover:bg-white text-stone-600 hover:text-rose-500 border border-white/60'
            }`}
            title={!user ? 'Sign in to save favorites' : isWishlisted(product.id) ? 'Remove from wishlist' : 'Add to wishlist'}
            aria-label="Wishlist toggle"
          >
            <Heart
              className={`w-3.5 h-3.5 transition-colors ${
                isWishlisted(product.id) ? 'fill-rose-500 text-rose-500' : ''
              }`}
            />
          </button>

          {/* Interactive Quick-View Overlay Action */}
          <div className="absolute inset-x-3 bottom-3 z-10 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
            <button
              onClick={(e) => {
                e.stopPropagation();
                openProductModal(product.slug);
              }}
              className="w-full py-2.5 rounded-xl bg-white/95 backdrop-blur-md text-stone-900 text-xs font-bold shadow-lg hover:bg-white flex items-center justify-center gap-1.5 transition border border-stone-200/60 cursor-pointer"
            >
              <Eye className="w-3.5 h-3.5 text-botanical-700" />
              <span>Botanical Care Specs</span>
            </button>
          </div>
        </div>

        {/* Card Info Content */}
        <div className="p-5 flex-1 flex flex-col justify-between space-y-3.5">
          <div>
            <h3 className="font-serif text-lg font-bold text-stone-900 group-hover:text-botanical-800 transition-colors leading-snug">
              {product.name}
            </h3>

            {product.botanical_name && (
              <div className="font-serif italic text-xs text-botanical-700 font-medium mt-0.5 mb-2 truncate tracking-wide">
                {product.botanical_name}
              </div>
            )}

            {/* Care Attribute Badges */}
            <div className="flex flex-wrap gap-1.5 text-[11px] text-stone-600 mt-2">
              {light && (
                <span className="inline-flex items-center gap-1 bg-sand-100/90 border border-sand-200/80 px-2 py-0.5 rounded-lg text-stone-600 font-medium">
                  <Sun className="w-3 h-3 text-amber-600" />
                  <span className="capitalize">{light.replace('-', ' ')}</span>
                </span>
              )}
              {water && (
                <span className="inline-flex items-center gap-1 bg-sand-100/90 border border-sand-200/80 px-2 py-0.5 rounded-lg text-stone-600 font-medium">
                  <Droplets className="w-3 h-3 text-sky-600" />
                  <span className="capitalize">{water.replace('-', ' ')}</span>
                </span>
              )}
            </div>
          </div>

          {/* Pricing & Add to Cart Footer */}
          <div className="pt-3 border-t border-stone-100 flex items-center justify-between mt-auto">
            <div>
              <span className="text-[10px] uppercase font-bold tracking-wider text-stone-400 block">
                Greenhouse Price
              </span>
              <div className="flex items-baseline gap-2 mt-0.5 tabular-nums">
                <span className="font-serif text-xl font-bold text-stone-900 tracking-tight">
                  {currencySymbol}{startingPrice.toFixed(2)}
                </span>
                {compareAtPrice && compareAtPrice > startingPrice && (
                  <span className="text-xs text-stone-400 line-through">
                    {currencySymbol}{compareAtPrice.toFixed(2)}
                  </span>
                )}
              </div>
            </div>

            {/* Quick-Add CTA: Center-Aligned Icon Button */}
            <button
              onClick={handleQuickAdd}
              className="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-botanical-800 hover:bg-botanical-900 active:scale-95 text-white shadow-xs hover:shadow-md transition-all duration-200 cursor-pointer shrink-0 flex items-center justify-center group"
              title="Add to cart"
              aria-label={`Add ${product.name} to cart`}
            >
              <ShoppingBag className="w-4 h-4 text-emerald-300 group-hover:scale-110 transition-transform" />
            </button>
          </div>
        </div>
      </div>
    </article>
  );
}
