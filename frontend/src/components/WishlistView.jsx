import React from 'react';
import { useStore } from '../context/StoreContext';
import {
  Heart,
  ShoppingBag,
  Trash2,
  ArrowRight,
  Sun,
  Droplets,
  Sparkles,
  ChevronLeft,
  Leaf,
  Check,
} from 'lucide-react';

export default function WishlistView() {
  const {
    wishlist,
    toggleWishlist,
    addToCart,
    currencySymbol,
    navigateTo,
    openProductModal,
    addToast,
    user,
    token,
    setAuthModalOpen,
    setCartOpen,
  } = useStore();

  const totalWishlistValue = wishlist.reduce((acc, product) => {
    const primaryVariant = product.default_variant || product.variants?.[0];
    const price = parseFloat(primaryVariant?.price || product.base_price || 0);
    return acc + price;
  }, 0);

  const handleAddAllToCart = () => {
    let addedCount = 0;
    wishlist.forEach((product) => {
      const primaryVariant = product.default_variant || product.variants?.[0];
      if (primaryVariant) {
        const added = addToCart(product, primaryVariant, 1);
        if (added) addedCount++;
      }
    });

    if (addedCount > 0) {
      addToast(`Added all ${addedCount} wishlist plants to your cart.`, 'success');
      setCartOpen(true);
    }
  };

  // 1. Unauthenticated Gate with no local favorites
  if (!user && !token && wishlist.length === 0) {
    return (
      <div className="max-w-2xl mx-auto px-4 py-20 sm:py-28 text-center space-y-6">
        <div className="w-20 h-20 mx-auto rounded-3xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-500 shadow-subtle">
          <Heart className="w-10 h-10" />
        </div>
        <div className="space-y-2">
          <h2 className="font-serif text-3xl sm:text-4xl font-bold text-stone-900 text-balance">
            Your Botanical Wishlist is Empty
          </h2>
          <p className="text-stone-500 max-w-md mx-auto text-xs sm:text-sm leading-relaxed text-pretty">
            Browse our greenhouse collection and click the heart icon on any plant or planter to save it to your wishlist.
          </p>
        </div>
        <div className="flex flex-wrap justify-center gap-3 pt-3">
          <button
            onClick={() => navigateTo('catalog')}
            className="h-12 px-6 rounded-full bg-botanical-800 hover:bg-botanical-900 text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-botanical-900/20 transition flex items-center gap-2.5 active:scale-98 cursor-pointer"
          >
            <span>Explore Botanical Catalog</span>
            <ArrowRight className="w-3.5 h-3.5 text-emerald-300" />
          </button>
          <button
            onClick={() => setAuthModalOpen('login')}
            className="h-12 px-6 rounded-full border border-stone-300 hover:bg-white text-stone-700 font-bold text-xs uppercase tracking-wider transition cursor-pointer"
          >
            Sign In / Register
          </button>
        </div>
      </div>
    );
  }

  // 2. Empty Wishlist State
  if (wishlist.length === 0) {
    return (
      <div className="max-w-2xl mx-auto px-4 py-20 sm:py-28 text-center space-y-6">
        <div className="w-20 h-20 mx-auto rounded-3xl bg-sand-100 border border-sand-200 flex items-center justify-center text-stone-400 shadow-subtle">
          <Heart className="w-9 h-9 stroke-1" />
        </div>
        <div className="space-y-2">
          <h2 className="font-serif text-3xl sm:text-4xl font-bold text-stone-900 text-balance">
            Your botanical wishlist is empty
          </h2>
          <p className="text-stone-500 max-w-md mx-auto text-xs sm:text-sm leading-relaxed text-pretty">
            Save your favorite tropical species, rare aroids, and indoor trees here by clicking the heart badge on any plant card.
          </p>
        </div>
        <div className="flex flex-wrap justify-center gap-3 pt-3">
          <button
            onClick={() => navigateTo('catalog')}
            className="h-12 px-6 rounded-full bg-botanical-800 hover:bg-botanical-900 text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-botanical-900/10 transition flex items-center gap-2.5 active:scale-98 cursor-pointer"
          >
            <span>Explore Plant Collection</span>
            <ArrowRight className="w-3.5 h-3.5 text-emerald-300" />
          </button>
          <button
            onClick={() => navigateTo('quiz')}
            className="h-12 px-6 rounded-full border border-stone-300 hover:bg-white text-stone-700 font-bold text-xs uppercase tracking-wider transition flex items-center gap-2 cursor-pointer"
          >
            <Sparkles className="w-4 h-4 text-amber-500" />
            <span>Plant Matcher Quiz</span>
          </button>
        </div>
      </div>
    );
  }

  // 3. Populated Wishlist View (Styled consistently with Cart View)
  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-6 sm:space-y-8">
      {/* Top Header & Add All to Cart Deck */}
      {!user && (
        <div className="p-3.5 rounded-2xl bg-sand-100/80 border border-sand-200/80 flex items-center justify-between text-xs text-stone-700">
          <span>Saved to this browser. Sign in to sync your wishlist and cart across all your devices.</span>
          <button
            onClick={() => setAuthModalOpen('login')}
            className="text-botanical-800 font-bold hover:underline cursor-pointer shrink-0 ml-3"
          >
            Sign In
          </button>
        </div>
      )}

      <div className="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-stone-200/80">
        <div>
          <button
            onClick={() => navigateTo('catalog')}
            className="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-botanical-700 hover:text-botanical-900 mb-2 cursor-pointer transition"
          >
            <ChevronLeft className="w-4 h-4" />
            <span>Continue Shopping</span>
          </button>
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center shadow-xs">
              <Heart className="w-5 h-5 fill-white" />
            </div>
            <div>
              <h1 className="font-serif text-2xl sm:text-3xl font-bold text-stone-900 tracking-tight leading-tight">
                Botanical Wishlist
              </h1>
              <span className="text-xs text-stone-500 font-medium">
                {wishlist.length} saved {wishlist.length === 1 ? 'specimen' : 'specimens'}
              </span>
            </div>
          </div>
        </div>

        {/* Primary Add All to Cart Action */}
        <button
          onClick={handleAddAllToCart}
          className="h-12 px-6 rounded-full bg-botanical-800 hover:bg-botanical-900 active:scale-[0.98] text-white font-bold text-xs uppercase tracking-widest flex items-center justify-center gap-2.5 shadow-lg shadow-botanical-950/20 transition-all duration-200 cursor-pointer group shrink-0"
        >
          <ShoppingBag className="w-4 h-4 text-emerald-300 group-hover:scale-110 transition-transform" />
          <span>Add All ({wishlist.length}) to Cart</span>
          <span className="text-emerald-200 font-mono font-normal">
            &bull; {currencySymbol}{totalWishlistValue.toFixed(2)}
          </span>
        </button>
      </div>

      {/* Wishlist Items Container: Clean divide-y card matching Cart View */}
      <div className="rounded-3xl bg-white border border-stone-200/90 shadow-card overflow-hidden divide-y divide-stone-100">
        {wishlist.map((product) => {
          const primaryVariant = product.default_variant || product.variants?.[0];
          const price = parseFloat(primaryVariant?.price || product.base_price || 0);
          const compareAtPrice = primaryVariant?.compare_at_price ? parseFloat(primaryVariant.compare_at_price) : null;
          const isPetFriendly = product.care_attribute?.is_pet_friendly;
          const light = product.care_attribute?.light_requirement;
          const water = product.care_attribute?.watering_frequency;

          return (
            <div
              key={product.id}
              className="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-sand-50/50 transition-colors"
            >
              {/* Left: Thumbnail & Details (Matching Cart Layout) */}
              <div className="flex items-center gap-3.5 sm:gap-4 min-w-0 flex-1">
                {/* Strict Fixed-Size Image Container (Guaranteed 80x80) */}
                <div
                  onClick={() => openProductModal(product.slug)}
                  className="w-20 h-20 rounded-2xl overflow-hidden bg-sand-100 border border-stone-200/80 shrink-0 relative shadow-2xs cursor-pointer group"
                >
                  <img
                    src={product.primary_image_url || 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?auto=format&fit=crop&w=400&q=80'}
                    alt={product.name}
                    onError={(e) => {
                      e.currentTarget.onerror = null;
                      e.currentTarget.src = 'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?auto=format&fit=crop&w=400&q=80';
                    }}
                    className="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300"
                  />
                </div>

                <div className="flex-1 min-w-0">
                  <div className="flex flex-wrap items-center gap-1.5 mb-1">
                    {product.category && (
                      <span className="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-sand-100 text-stone-600 border border-sand-200/80">
                        {product.category.name}
                      </span>
                    )}
                    {isPetFriendly && (
                      <span className="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-botanical-100 text-botanical-800 border border-botanical-200">
                        🐾 Pet Safe
                      </span>
                    )}
                  </div>

                  <h3
                    onClick={() => openProductModal(product.slug)}
                    className="font-serif font-bold text-base text-stone-900 hover:text-botanical-800 transition cursor-pointer truncate leading-snug"
                  >
                    {product.name}
                  </h3>
                  
                  {product.botanical_name && (
                    <p className="font-serif italic text-xs text-botanical-700 truncate mt-0.5">
                      {product.botanical_name}
                    </p>
                  )}

                  {/* Price row */}
                  <div className="flex items-baseline gap-2 mt-1.5 tabular-nums">
                    <span className="font-serif font-bold text-base text-stone-900">
                      {currencySymbol}{price.toFixed(2)}
                    </span>
                    {compareAtPrice && compareAtPrice > price && (
                      <span className="text-xs text-stone-400 line-through">
                        {currencySymbol}{compareAtPrice.toFixed(2)}
                      </span>
                    )}
                  </div>
                </div>
              </div>

              {/* Right: Actions (Add to Cart + Remove) */}
              <div className="flex items-center justify-between sm:justify-end gap-3 pt-2 sm:pt-0 border-t sm:border-t-0 border-stone-100 shrink-0">
                <button
                  onClick={() => {
                    if (primaryVariant) addToCart(product, primaryVariant, 1);
                  }}
                  className="h-10 px-4 rounded-full bg-botanical-800 hover:bg-botanical-900 active:scale-95 text-white text-xs font-bold shadow-xs hover:shadow-md transition-all duration-200 cursor-pointer inline-flex items-center justify-center gap-2 group shrink-0"
                  title="Add to cart"
                  aria-label={`Add ${product.name} to cart`}
                >
                  <ShoppingBag className="w-3.5 h-3.5 text-emerald-300 group-hover:scale-110 transition-transform shrink-0" />
                  <span>Add to Cart</span>
                </button>

                <button
                  onClick={(e) => {
                    e.stopPropagation();
                    toggleWishlist(product);
                  }}
                  className="w-10 h-10 rounded-full border border-stone-200 hover:border-rose-200 hover:bg-rose-50 text-stone-400 hover:text-rose-600 transition flex items-center justify-center cursor-pointer shrink-0 active:scale-95"
                  title="Remove from favorites"
                  aria-label="Remove from favorites"
                >
                  <Trash2 className="w-4 h-4" />
                </button>
              </div>
            </div>
          );
        })}
      </div>

      {/* Bottom Summary Bar */}
      <div className="p-5 rounded-3xl bg-sand-50/80 border border-stone-200/80 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
          <span className="text-xs text-stone-500 font-medium block">
            Combined Wishlist Value
          </span>
          <span className="font-serif text-2xl font-bold text-botanical-900 tabular-nums">
            {currencySymbol}{totalWishlistValue.toFixed(2)}
          </span>
        </div>

        <div className="flex items-center gap-3 w-full sm:w-auto">
          <button
            onClick={() => navigateTo('catalog')}
            className="flex-1 sm:flex-none h-12 px-6 rounded-full border border-stone-200 hover:bg-white text-stone-700 font-bold text-xs uppercase tracking-wider transition cursor-pointer"
          >
            Browse More
          </button>
          <button
            onClick={handleAddAllToCart}
            className="flex-1 sm:flex-none h-12 px-6 rounded-full bg-botanical-800 hover:bg-botanical-900 active:scale-[0.98] text-white font-bold text-xs uppercase tracking-widest flex items-center justify-center gap-2 shadow-lg shadow-botanical-950/20 transition-all duration-200 cursor-pointer"
          >
            <ShoppingBag className="w-4 h-4 text-emerald-300" />
            <span>Add All to Cart</span>
          </button>
        </div>
      </div>
    </div>
  );
}
