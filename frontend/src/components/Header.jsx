import React, { useState, useRef, useEffect } from 'react';
import { useStore } from '../context/StoreContext';
import {
  ShoppingBag,
  Search,
  Sparkles,
  Truck,
  Leaf,
  Menu,
  X,
  Heart,
  User,
  LogOut,
  Package,
  ChevronDown,
  ArrowRight,
} from 'lucide-react';

export default function Header() {
  const {
    siteName,
    siteSettings,
    announcement,
    cartCount,
    setCartOpen,
    currentPage,
    navigateTo,
    searchQuery,
    setSearchQuery,
    user,
    setAuthModalOpen,
    customerLogout,
    wishlistCount,
  } = useStore();

  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [searchInput, setSearchInput] = useState(searchQuery);
  const [userMenuOpen, setUserMenuOpen] = useState(false);
  const userMenuRef = useRef(null);

  const logoUrl = siteSettings?.general?.site_logo_url;

  // Close user dropdown if clicked outside
  useEffect(() => {
    function handleClickOutside(event) {
      if (userMenuRef.current && !userMenuRef.current.contains(event.target)) {
        setUserMenuOpen(false);
      }
    }
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleSearchSubmit = (e) => {
    e.preventDefault();
    if (searchInput.trim()) {
      navigateTo('catalog', { search: searchInput.trim() });
    }
  };

  return (
    <header className="sticky top-2 sm:top-4 z-40 px-3 sm:px-6 lg:px-8 transition-all">
      {/* 1. Dynamic Announcement Floating Pill */}
      {announcement?.announcement_active && announcement?.announcement_text && (
        <div className="max-w-xl mx-auto mb-2 text-center">
          <div
            className="px-4 py-1.5 text-xs font-semibold tracking-wide rounded-full shadow-subtle inline-flex items-center gap-2 border border-white/20 backdrop-blur-md"
            style={{
              backgroundColor: announcement.announcement_bg_color || '#1b4332',
              color: announcement.announcement_text_color || '#d8f3dc',
            }}
          >
            <span className="truncate">{announcement.announcement_text}</span>
            {announcement.announcement_link && (
              <button
                onClick={() => navigateTo('catalog')}
                className="underline hover:opacity-80 font-bold ml-1 inline-flex items-center text-[10px] uppercase tracking-wider cursor-pointer"
              >
                Shop Now &rarr;
              </button>
            )}
          </div>
        </div>
      )}

      {/* 2. Floating Glass Island Navigation */}
      <div className="max-w-7xl mx-auto rounded-full bg-white/90 backdrop-blur-xl border border-white/80 shadow-card px-4 sm:px-7 py-2.5 flex items-center justify-between gap-4 transition-all duration-300">
        
        {/* Brand Identity */}
        <div className="flex items-center gap-6 lg:gap-8">
          <button
            onClick={() => navigateTo('home')}
            className="flex items-center gap-3 text-left group cursor-pointer"
          >
            <div className="w-10 h-10 rounded-2xl bg-botanical-800 text-white flex items-center justify-center shadow-md shadow-botanical-950/20 group-hover:scale-105 transition-transform overflow-hidden flex-shrink-0">
              {logoUrl ? (
                <img src={logoUrl} alt={siteName} className="w-full h-full object-contain p-1" />
              ) : (
                <Leaf className="w-5 h-5 text-botanical-200" />
              )}
            </div>
            <div>
              <span className="font-serif text-lg sm:text-xl font-bold text-stone-900 tracking-tight block leading-tight">
                {siteName}
              </span>
              <span className="text-[10px] font-semibold text-botanical-700 tracking-wider uppercase block">
                Living Greenhouse
              </span>
            </div>
          </button>

          {/* Desktop Navigation Links */}
          <nav className="hidden lg:flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-stone-600">
            <button
              onClick={() => navigateTo('catalog')}
              className={`px-4 py-2 rounded-full transition cursor-pointer ${
                currentPage === 'catalog'
                  ? 'bg-botanical-800 text-white shadow-xs'
                  : 'hover:text-stone-900 hover:bg-sand-100/80'
              }`}
            >
              Plants & Catalog
            </button>

            <button
              onClick={() => navigateTo('quiz')}
              className={`px-4 py-2 rounded-full flex items-center gap-1.5 transition cursor-pointer ${
                currentPage === 'quiz'
                  ? 'bg-botanical-800 text-white shadow-xs'
                  : 'hover:text-stone-900 hover:bg-sand-100/80 text-botanical-800'
              }`}
            >
              <Sparkles className="w-3.5 h-3.5 text-amber-500" />
              <span>Plant Matcher</span>
            </button>
          </nav>
        </div>

        {/* Right Action Tools */}
        <div className="flex items-center gap-2 sm:gap-3">
          {/* Search Input (Desktop) */}
          <form onSubmit={handleSearchSubmit} className="hidden md:flex relative w-48 lg:w-60">
            <input
              type="text"
              value={searchInput}
              onChange={(e) => setSearchInput(e.target.value)}
              placeholder="Search botanical species..."
              className="w-full pl-8 pr-3 py-2 rounded-full bg-sand-100/80 border border-transparent focus:bg-white focus:border-botanical-500 focus:ring-2 focus:ring-botanical-500/20 text-xs font-medium focus:outline-none transition"
            />
            <Search className="w-3.5 h-3.5 text-stone-400 absolute left-3 top-2.5" />
          </form>

          {/* Customer Authentication Dropdown / Button */}
          {user ? (
            <div className="relative" ref={userMenuRef}>
              <button
                onClick={() => setUserMenuOpen(!userMenuOpen)}
                className="flex items-center gap-2 pl-1.5 pr-3 py-1.5 rounded-full bg-sand-100 hover:bg-sand-200/80 text-stone-800 transition shadow-2xs cursor-pointer border border-sand-200/60"
                aria-label="User account menu"
              >
                {user.avatar ? (
                  <img
                    src={user.avatar}
                    alt={user.name}
                    className="w-7 h-7 rounded-full object-cover ring-1 ring-stone-300"
                  />
                ) : (
                  <div className="w-7 h-7 rounded-full bg-botanical-800 text-white text-xs font-bold flex items-center justify-center">
                    {user.name ? user.name.charAt(0).toUpperCase() : 'G'}
                  </div>
                )}
                <span className="hidden md:inline text-xs font-bold max-w-[100px] truncate">
                  {user.name.split(' ')[0]}
                </span>
                <ChevronDown className={`w-3.5 h-3.5 text-stone-400 transition-transform duration-200 ${userMenuOpen ? 'rotate-180 text-botanical-700' : ''}`} />
              </button>

              {/* Dropdown Menu — auth-gated items with Double-Bezel Framing */}
              {userMenuOpen && (
                <div className="absolute right-0 mt-3 w-64 z-50 animate-in fade-in zoom-in-95 duration-150">
                  <div className="p-1.5 rounded-[1.85rem] bg-stone-900/10 backdrop-blur-xl border border-white/50 shadow-float">
                    <div className="bg-white/95 rounded-[1.5rem] border border-stone-100 overflow-hidden shadow-2xs">
                      {/* User info header */}
                      <div className="px-4 py-3 border-b border-stone-100 bg-sand-50/50">
                        <p className="text-xs font-bold text-stone-900 truncate">{user.name}</p>
                        <p className="text-[11px] text-stone-500 truncate">{user.email}</p>
                        <span className="inline-flex items-center gap-1 mt-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-botanical-100 text-botanical-800 border border-botanical-200">
                          <Sparkles className="w-2.5 h-2.5 text-botanical-600" />
                          <span>Botanical Member</span>
                        </span>
                      </div>

                      {/* Nav items */}
                      <div className="p-1.5 space-y-0.5">
                        {/* Cart */}
                        <button
                          onClick={() => {
                            setCartOpen(true);
                            setUserMenuOpen(false);
                          }}
                          className="w-full px-3 py-2 rounded-xl text-left text-xs font-semibold text-stone-700 hover:bg-sand-50 hover:text-stone-900 flex items-center justify-between cursor-pointer transition"
                        >
                          <span className="flex items-center gap-2.5">
                            <ShoppingBag className="w-4 h-4 text-botanical-700" />
                            <span>My Cart</span>
                          </span>
                          {cartCount > 0 && (
                            <span className="text-[10px] bg-botanical-100 text-botanical-800 px-2 py-0.5 rounded-full font-bold tabular-nums">
                              {cartCount}
                            </span>
                          )}
                        </button>

                        {/* Wishlist */}
                        <button
                          onClick={() => {
                            navigateTo('wishlist');
                            setUserMenuOpen(false);
                          }}
                          className="w-full px-3 py-2 rounded-xl text-left text-xs font-semibold text-stone-700 hover:bg-sand-50 hover:text-stone-900 flex items-center justify-between cursor-pointer transition"
                        >
                          <span className="flex items-center gap-2.5">
                            <Heart className="w-4 h-4 text-rose-500" />
                            <span>Saved Favorites</span>
                          </span>
                          {wishlistCount > 0 && (
                            <span className="text-[10px] bg-rose-100 text-rose-800 px-2 py-0.5 rounded-full font-bold tabular-nums">
                              {wishlistCount}
                            </span>
                          )}
                        </button>

                        {/* Orders */}
                        <button
                          onClick={() => {
                            navigateTo('orders');
                            setUserMenuOpen(false);
                          }}
                          className="w-full px-3 py-2 rounded-xl text-left text-xs font-semibold text-stone-700 hover:bg-sand-50 hover:text-stone-900 flex items-center gap-2.5 cursor-pointer transition"
                        >
                          <Package className="w-4 h-4 text-stone-400" />
                          <span>My Orders</span>
                        </button>

                        {/* Track Transit */}
                        <button
                          onClick={() => {
                            navigateTo('tracking');
                            setUserMenuOpen(false);
                          }}
                          className="w-full px-3 py-2 rounded-xl text-left text-xs font-semibold text-stone-700 hover:bg-sand-50 hover:text-stone-900 flex items-center gap-2.5 cursor-pointer transition"
                        >
                          <Truck className="w-4 h-4 text-stone-400" />
                          <span>Track Transit</span>
                        </button>
                      </div>

                      {/* Sign Out */}
                      <div className="pt-1 border-t border-stone-100 p-1.5 bg-stone-50/40">
                        <button
                          onClick={() => {
                            customerLogout();
                            setUserMenuOpen(false);
                          }}
                          className="w-full px-3 py-2 rounded-xl text-left text-xs font-semibold text-red-600 hover:bg-red-50 flex items-center gap-2.5 cursor-pointer transition"
                        >
                          <LogOut className="w-4 h-4 text-red-500" />
                          <span>Sign Out</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </div>
          ) : (
            /* Guest — Sign In button */
            <button
              onClick={() => setAuthModalOpen('login')}
              className="px-4 py-2 rounded-full bg-botanical-800 hover:bg-botanical-900 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer"
            >
              <User className="w-3.5 h-3.5" />
              <span>Sign In</span>
            </button>
          )}

          {/* Mobile Hamburger Button */}
          <button
            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
            className="lg:hidden p-2 rounded-full text-stone-600 hover:bg-sand-100 cursor-pointer"
            aria-label="Toggle menu"
          >
            {mobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
          </button>
        </div>
      </div>

      {/* 3. Mobile Navigation Drawer */}
      {mobileMenuOpen && (
        <div className="lg:hidden max-w-7xl mx-auto mt-2 bg-white/95 backdrop-blur-xl border border-stone-200/80 rounded-3xl p-5 space-y-4 shadow-float animate-in slide-in-from-top-2">
          {/* Mobile User Status */}
          {user ? (
            <div className="p-3.5 bg-sand-50 rounded-2xl flex items-center justify-between border border-sand-200/60">
              <div className="flex items-center gap-3">
                {user.avatar ? (
                  <img src={user.avatar} alt={user.name} className="w-9 h-9 rounded-full object-cover" />
                ) : (
                  <div className="w-9 h-9 rounded-full bg-botanical-800 text-white text-xs font-bold flex items-center justify-center">
                    {user.name?.charAt(0).toUpperCase() || 'G'}
                  </div>
                )}
                <div>
                  <div className="text-xs font-bold text-stone-900">{user.name}</div>
                  <div className="text-[11px] text-stone-500">{user.email}</div>
                </div>
              </div>
              <button
                onClick={() => {
                  customerLogout();
                  setMobileMenuOpen(false);
                }}
                className="text-xs text-red-600 hover:underline font-semibold cursor-pointer"
              >
                Sign Out
              </button>
            </div>
          ) : (
            <button
              onClick={() => {
                setAuthModalOpen('login');
                setMobileMenuOpen(false);
              }}
              className="w-full py-3 rounded-2xl bg-botanical-800 text-white text-xs font-bold flex items-center justify-center gap-2 cursor-pointer shadow-sm"
            >
              <User className="w-4 h-4" />
              <span>Sign In / Create Account</span>
            </button>
          )}

          {/* Mobile Search */}
          <form onSubmit={handleSearchSubmit} className="relative">
            <input
              type="text"
              value={searchInput}
              onChange={(e) => setSearchInput(e.target.value)}
              placeholder="Search botanical collection..."
              className="w-full pl-9 pr-4 py-2.5 rounded-2xl bg-sand-100 text-xs font-medium focus:outline-none"
            />
            <Search className="w-4 h-4 text-stone-400 absolute left-3 top-3" />
          </form>

          <nav className="flex flex-col gap-1 text-sm font-semibold text-stone-700">
            <button
              onClick={() => {
                navigateTo('catalog');
                setMobileMenuOpen(false);
              }}
              className="px-3.5 py-2.5 rounded-2xl text-left hover:bg-sand-50 cursor-pointer flex items-center justify-between"
            >
              <span>🌿 Plants & Catalog</span>
              <ArrowRight className="w-4 h-4 text-stone-400" />
            </button>
            <button
              onClick={() => {
                navigateTo('quiz');
                setMobileMenuOpen(false);
              }}
              className="px-3.5 py-2.5 rounded-2xl text-left hover:bg-sand-50 flex items-center justify-between text-botanical-800 cursor-pointer"
            >
              <span>✨ Plant Matcher Quiz</span>
              <span className="text-[10px] bg-amber-100 text-amber-900 px-2 py-0.5 rounded-full font-bold">Find Match</span>
            </button>

            {/* Auth-gated items on mobile */}
            {user && (
              <div className="pt-2 mt-1 border-t border-stone-100 space-y-1">
                <p className="px-3.5 text-[10px] font-bold text-stone-400 uppercase tracking-wider">My Account</p>

                <button
                  onClick={() => {
                    setCartOpen(true);
                    setMobileMenuOpen(false);
                  }}
                  className="w-full px-3.5 py-2.5 rounded-2xl text-left hover:bg-sand-50 flex items-center justify-between cursor-pointer"
                >
                  <span className="flex items-center gap-2.5">
                    <ShoppingBag className="w-4 h-4 text-botanical-700" />
                    <span>My Cart</span>
                  </span>
                  {cartCount > 0 && (
                    <span className="text-xs bg-botanical-100 text-botanical-800 px-2 py-0.5 rounded-full font-bold tabular-nums">
                      {cartCount}
                    </span>
                  )}
                </button>

                <button
                  onClick={() => {
                    navigateTo('wishlist');
                    setMobileMenuOpen(false);
                  }}
                  className="w-full px-3.5 py-2.5 rounded-2xl text-left hover:bg-sand-50 flex items-center justify-between text-rose-700 cursor-pointer"
                >
                  <span className="flex items-center gap-2.5">
                    <Heart className="w-4 h-4 fill-rose-500" />
                    <span>Saved Favorites</span>
                  </span>
                  {wishlistCount > 0 && (
                    <span className="text-xs bg-rose-100 px-2 py-0.5 rounded-full font-bold tabular-nums">
                      {wishlistCount}
                    </span>
                  )}
                </button>

                <button
                  onClick={() => {
                    navigateTo('orders');
                    setMobileMenuOpen(false);
                  }}
                  className="px-3.5 py-2.5 rounded-2xl text-left hover:bg-sand-50 flex items-center gap-2.5 w-full cursor-pointer"
                >
                  <Package className="w-4 h-4 text-stone-400" />
                  <span>My Orders</span>
                </button>

                <button
                  onClick={() => {
                    navigateTo('tracking');
                    setMobileMenuOpen(false);
                  }}
                  className="px-3.5 py-2.5 rounded-2xl text-left hover:bg-sand-50 flex items-center gap-2.5 w-full cursor-pointer"
                >
                  <Truck className="w-4 h-4 text-stone-400" />
                  <span>Track Live Plant Transit</span>
                </button>
              </div>
            )}
          </nav>
        </div>
      )}
    </header>
  );
}
