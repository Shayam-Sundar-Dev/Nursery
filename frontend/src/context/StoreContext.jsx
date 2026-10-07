import React, { createContext, useContext, useState, useEffect, useMemo } from 'react';
import api from '../api/client';

const StoreContext = createContext(null);

export function StoreProvider({ children }) {
  // 1. Site Settings State
  const [siteSettings, setSiteSettings] = useState(null);
  const [settingsLoading, setSettingsLoading] = useState(true);

  // 2. Customer Authentication State
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(() => localStorage.getItem('botanical_customer_token') || null);
  const [authModalOpen, setAuthModalOpen] = useState(null); // 'login' | 'register' | null
  const [customerOrders, setCustomerOrders] = useState([]);
  const [loadingOrders, setLoadingOrders] = useState(false);

  // 3. Navigation State
  const [currentPage, setCurrentPage] = useState('home'); // 'home' | 'catalog' | 'quiz' | 'tracking' | 'garden' | 'orders' | 'wishlist'
  const [selectedCategory, setSelectedCategory] = useState(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [activeProductSlug, setActiveProductSlug] = useState(null);
  const [activeTrackingNumber, setActiveTrackingNumber] = useState('');
  const [cartOpen, setCartOpen] = useState(false);
  const [checkoutOpen, setCheckoutOpen] = useState(false);

  // 4. Cart State (Persistent in LocalStorage)
  const [cart, setCart] = useState(() => {
    try {
      const saved = localStorage.getItem('botanical_storefront_cart');
      return saved ? JSON.parse(saved) : [];
    } catch {
      return [];
    }
  });

  // 5. Wishlist State (Persistent in LocalStorage)
  const [wishlist, setWishlist] = useState(() => {
    try {
      const saved = localStorage.getItem('botanical_storefront_wishlist');
      return saved ? JSON.parse(saved) : [];
    } catch {
      return [];
    }
  });

  // 6. Cart Add-ons & Discounts
  const [thermalPackRequested, setThermalPackRequested] = useState(true);
  const [appliedCoupon, setAppliedCoupon] = useState(null);
  const [couponLoading, setCouponLoading] = useState(false);

  // 7. Toast Notifications
  const [toasts, setToasts] = useState([]);

  const addToast = (message, type = 'success') => {
    const id = Date.now() + Math.random();
    setToasts((prev) => [...prev, { id, message, type }]);
    setTimeout(() => {
      setToasts((prev) => prev.filter((t) => t.id !== id));
    }, 4000);
  };

  const removeToast = (id) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  };

  // Fetch Site Settings on Mount
  useEffect(() => {
    async function loadSettings() {
      try {
        setSettingsLoading(true);
        const res = await api.getSiteSettings();
        if (res.success && res.data) {
          setSiteSettings(res.data);
        }
      } catch (err) {
        console.error('Failed to load site settings:', err);
      } finally {
        setSettingsLoading(false);
      }
    }
    loadSettings();
  }, []);

  // Save Cart to LocalStorage and sync with cloud database across devices
  useEffect(() => {
    try {
      localStorage.setItem('botanical_storefront_cart', JSON.stringify(cart));
      if (token && user) {
        localStorage.setItem(`botanical_user_cart_${user.id}`, JSON.stringify(cart));
        const syncTimer = setTimeout(() => {
          api.customerSyncCart(cart).catch((e) => {
            console.warn('Cross-device cart background sync:', e);
          });
        }, 400);
        return () => clearTimeout(syncTimer);
      }
    } catch (e) {
      console.error('Failed to sync cart:', e);
    }
  }, [cart, token, user]);

  // Save Wishlist to LocalStorage and sync with cloud database across devices
  useEffect(() => {
    try {
      localStorage.setItem('botanical_storefront_wishlist', JSON.stringify(wishlist));
      if (token && user) {
        localStorage.setItem(`botanical_user_wishlist_${user.id}`, JSON.stringify(wishlist));
        const syncTimer = setTimeout(() => {
          api.customerSyncWishlist(wishlist).catch((e) => {
            console.warn('Cross-device wishlist background sync:', e);
          });
        }, 400);
        return () => clearTimeout(syncTimer);
      }
    } catch (e) {
      console.error('Failed to sync wishlist:', e);
    }
  }, [wishlist, token, user]);

  // Merge & restore cloud customer data with local device data
  const syncUserDataFromCloud = (cloudUser) => {
    if (!cloudUser) return;
    try {
      // 1. Synchronize Cart across devices
      const cloudCart = Array.isArray(cloudUser.cart) ? cloudUser.cart : [];
      let localCart = [];
      try {
        const raw =
          localStorage.getItem(`botanical_user_cart_${cloudUser.id}`) ||
          localStorage.getItem('botanical_storefront_cart');
        if (raw) localCart = JSON.parse(raw);
      } catch {}

      const cartMap = new Map();
      cloudCart.forEach((item) => {
        if (item?.variantId) cartMap.set(item.variantId, item);
      });
      localCart.forEach((item) => {
        if (item?.variantId) {
          if (cartMap.has(item.variantId)) {
            const existing = cartMap.get(item.variantId);
            cartMap.set(item.variantId, {
              ...existing,
              quantity: Math.max(existing.quantity || 1, item.quantity || 1),
            });
          } else {
            cartMap.set(item.variantId, item);
          }
        }
      });
      const mergedCart = Array.from(cartMap.values());
      setCart(mergedCart);
      localStorage.setItem('botanical_storefront_cart', JSON.stringify(mergedCart));
      localStorage.setItem(`botanical_user_cart_${cloudUser.id}`, JSON.stringify(mergedCart));

      if (mergedCart.length > cloudCart.length) {
        api.customerSyncCart(mergedCart).catch(() => {});
      }

      // 2. Synchronize Wishlist across devices
      const cloudWishlist = Array.isArray(cloudUser.wishlist) ? cloudUser.wishlist : [];
      let localWishlist = [];
      try {
        const raw =
          localStorage.getItem(`botanical_user_wishlist_${cloudUser.id}`) ||
          localStorage.getItem('botanical_storefront_wishlist');
        if (raw) localWishlist = JSON.parse(raw);
      } catch {}

      const wishlistMap = new Map();
      cloudWishlist.forEach((item) => {
        if (item?.id) wishlistMap.set(item.id, item);
      });
      localWishlist.forEach((item) => {
        if (item?.id) wishlistMap.set(item.id, item);
      });
      const mergedWishlist = Array.from(wishlistMap.values());
      setWishlist(mergedWishlist);
      localStorage.setItem('botanical_storefront_wishlist', JSON.stringify(mergedWishlist));
      localStorage.setItem(`botanical_user_wishlist_${cloudUser.id}`, JSON.stringify(mergedWishlist));

      if (mergedWishlist.length > cloudWishlist.length) {
        api.customerSyncWishlist(mergedWishlist).catch(() => {});
      }
    } catch (e) {
      console.warn('Failed syncing user data from cloud:', e);
    }
  };

  // Fetch Authenticated User Profile if Token Exists
  useEffect(() => {
    if (!token) {
      setUser(null);
      return;
    }

    async function loadUserProfile() {
      try {
        const res = await api.customerGetProfile();
        if (res.success && res.user) {
          setUser(res.user);
          syncUserDataFromCloud(res.user);
        }
      } catch (err) {
        console.warn('Session expired or invalid token:', err);
        localStorage.removeItem('botanical_customer_token');
        setToken(null);
        setUser(null);
      }
    }

    loadUserProfile();
  }, [token]);

  // Customer Authentication Actions
  const customerLogin = async (email, password) => {
    const res = await api.customerLogin({ email, password });
    if (res.success && res.token) {
      localStorage.setItem('botanical_customer_token', res.token);
      setToken(res.token);
      setUser(res.user);
      syncUserDataFromCloud(res.user);
      setAuthModalOpen(null);
      addToast(`Welcome back, ${res.user.name}!`);
      return res.user;
    }
  };

  const customerRegister = async (name, email, password) => {
    const res = await api.customerRegister({ name, email, password });
    if (res.success && res.token) {
      localStorage.setItem('botanical_customer_token', res.token);
      setToken(res.token);
      setUser(res.user);
      syncUserDataFromCloud(res.user);
      setAuthModalOpen(null);
      addToast(`Welcome to our botanical community, ${res.user.name}!`);
      return res.user;
    }
  };

  const customerSocialLogin = async (provider, profile = {}) => {
    const res = await api.customerSocialLogin({
      provider,
      email: profile.email,
      name: profile.name,
      avatar: profile.avatar,
      provider_id: profile.provider_id,
    });
    if (res.success && res.token) {
      localStorage.setItem('botanical_customer_token', res.token);
      setToken(res.token);
      setUser(res.user);
      syncUserDataFromCloud(res.user);
      setAuthModalOpen(null);
      addToast(`Signed in with ${provider.charAt(0).toUpperCase() + provider.slice(1)}! Welcome, ${res.user.name}.`);
      return res.user;
    }
  };

  const customerLogout = async () => {
    try {
      await api.customerLogout();
    } catch (e) {
      console.warn('Logout network error:', e);
    } finally {
      localStorage.removeItem('botanical_customer_token');
      localStorage.removeItem('botanical_storefront_cart');
      localStorage.removeItem('botanical_storefront_wishlist');
      setToken(null);
      setUser(null);
      setCart([]);
      setWishlist([]);
      setCustomerOrders([]);
      setAppliedCoupon(null);
      addToast('Signed out of your botanical account.', 'info');
    }
  };

  const updateUserProfile = async (payload) => {
    try {
      const res = await api.customerUpdateProfile(payload);
      if (res.success && res.user) {
        setUser(res.user);
        return res.user;
      }
    } catch (e) {
      console.warn('Failed to update profile:', e);
      throw e;
    }
  };

  const loadCustomerOrders = async () => {
    try {
      setLoadingOrders(true);
      const res = await api.customerGetOrders();
      if (res.success && Array.isArray(res.data)) {
        setCustomerOrders(res.data);
      }
    } catch (err) {
      console.error('Failed to load customer orders:', err);
    } finally {
      setLoadingOrders(false);
    }
  };

  // Wishlist Actions
  const toggleWishlist = (product) => {
    setWishlist((prev) => {
      const exists = prev.some((p) => p.id === product.id);
      if (exists) {
        addToast(`Removed "${product.name}" from saved favorites`, 'info');
        return prev.filter((p) => p.id !== product.id);
      } else {
        addToast(`Added "${product.name}" to your botanical wishlist!`, 'success');
        return [...prev, product];
      }
    });
    return true;
  };

  const isWishlisted = (productId) => {
    return wishlist.some((p) => p.id === productId);
  };

  // Derived Currency & Policies
  const currencySymbol = siteSettings?.general?.currency_symbol || '₹';
  const currencyCode = siteSettings?.general?.currency_code || 'INR';
  const siteName = siteSettings?.general?.site_name || 'Verdant Botanical Nursery & Garden';
  const siteTagline = siteSettings?.general?.site_tagline || 'Live-Plant Specialized Fulfillment & Rare Botanical Specimens';
  const freeShippingThreshold = parseFloat(siteSettings?.shipping?.free_shipping_threshold ?? 75.00);
  const defaultShippingFee = parseFloat(siteSettings?.shipping?.default_shipping_fee ?? 9.99);
  const thermalPackagingFee = parseFloat(siteSettings?.shipping?.thermal_packaging_fee ?? 4.50);
  const enableStateShipping = siteSettings?.shipping?.enable_state_shipping !== false && siteSettings?.shipping?.enable_state_shipping !== '0';
  const stateShippingRates = Array.isArray(siteSettings?.shipping?.state_shipping_rates) ? siteSettings.shipping.state_shipping_rates : [];

  const getStateShippingRate = (stateName) => {
    if (!stateName || !enableStateShipping) return null;
    if (!Array.isArray(stateShippingRates) || stateShippingRates.length === 0) return null;
    const clean = stateName.trim().toLowerCase();
    return stateShippingRates.find((r) => r && r.state && r.state.trim().toLowerCase() === clean) || null;
  };
  const maintenanceMode = Boolean(siteSettings?.status?.maintenance_mode);
  const maintenanceMessage = siteSettings?.status?.maintenance_message || 'Our greenhouses are undergoing scheduled care updates.';
  const announcement = siteSettings?.announcement;

  // Cart Calculations
  const cartSubtotal = useMemo(() => {
    return cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  }, [cart]);

  const cartCount = useMemo(() => {
    return cart.reduce((count, item) => count + item.quantity, 0);
  }, [cart]);

  const qualifiesForFreeShipping = cartSubtotal >= freeShippingThreshold;
  const estimatedShippingFee = cart.length === 0 ? 0 : (qualifiesForFreeShipping ? 0 : defaultShippingFee);
  const estimatedThermalFee = (cart.length > 0 && thermalPackRequested) ? thermalPackagingFee : 0;

  const estimatedTotal = useMemo(() => {
    let total = cartSubtotal + estimatedShippingFee + estimatedThermalFee;
    if (appliedCoupon && appliedCoupon.calculated_discount) {
      total = Math.max(0, total - appliedCoupon.calculated_discount);
    }
    return total;
  }, [cartSubtotal, estimatedShippingFee, estimatedThermalFee, appliedCoupon]);

  // Cart Actions
  const addToCart = (product, variant, quantity = 1) => {
    const variantId = variant?.id || product.default_variant?.id || product.variants?.[0]?.id;
    const variantTitle = variant?.title || product.default_variant?.title || 'Standard Nursery Pot';
    const price = parseFloat(variant?.price || product.base_price || 0);
    const sku = variant?.sku || product.sku || `PLANT-${product.id}`;
    const image = product.primary_image_url || '/placeholder.png';

    setCart((prev) => {
      const existingIndex = prev.findIndex((item) => item.variantId === variantId);
      if (existingIndex > -1) {
        const next = [...prev];
        next[existingIndex] = {
          ...next[existingIndex],
          quantity: next[existingIndex].quantity + quantity,
        };
        return next;
      }
      return [
        ...prev,
        {
          id: `${product.id}-${variantId}`,
          productId: product.id,
          productSlug: product.slug,
          productName: product.name,
          botanicalName: product.botanical_name,
          variantId,
          variantTitle,
          price,
          sku,
          image,
          quantity,
        },
      ];
    });

    addToast(`Added "${product.name}" (${variantTitle}) to your botanical cart!`);
    return true;
  };

  const updateQuantity = (variantId, delta) => {
    setCart((prev) => {
      return prev
        .map((item) => {
          if (item.variantId === variantId || item.id === variantId) {
            const nextQty = item.quantity + delta;
            return nextQty > 0 ? { ...item, quantity: nextQty } : null;
          }
          return item;
        })
        .filter(Boolean);
    });
  };

  const removeFromCart = (variantId) => {
    setCart((prev) => prev.filter((item) => item.variantId !== variantId && item.id !== variantId));
    addToast('Item removed from cart', 'info');
  };

  const clearCart = () => {
    setCart([]);
    setAppliedCoupon(null);
  };

  // Coupon Actions
  const applyCouponCode = async (code) => {
    if (!code || !code.trim()) return;
    try {
      setCouponLoading(true);
      const res = await api.validateCoupon(code.trim().toUpperCase(), cartSubtotal);
      if (res.success && res.coupon) {
        setAppliedCoupon(res.coupon);
        addToast(`Promo code "${res.coupon.code}" applied! Saved ${currencySymbol}${res.coupon.calculated_discount.toFixed(2)}`, 'success');
      }
    } catch (err) {
      addToast(err.message || 'Invalid or expired promotional code', 'error');
      setAppliedCoupon(null);
    } finally {
      setCouponLoading(false);
    }
  };

  const removeCoupon = () => {
    setAppliedCoupon(null);
    addToast('Promotional voucher removed', 'info');
  };

  // Navigation Helpers
  const navigateTo = (page, params = {}) => {
    setCurrentPage(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
    if (params.category !== undefined) setSelectedCategory(params.category);
    if (params.productSlug !== undefined) setActiveProductSlug(params.productSlug);
    if (params.trackingNumber !== undefined) setActiveTrackingNumber(params.trackingNumber);
    if (params.search !== undefined) setSearchQuery(params.search);
  };

  const openProductModal = (slug) => {
    setActiveProductSlug(slug);
  };

  const closeProductModal = () => {
    setActiveProductSlug(null);
  };

  return (
    <StoreContext.Provider
      value={{
        // Site Settings
        siteSettings,
        settingsLoading,
        siteName,
        siteTagline,
        currencySymbol,
        currencyCode,
        freeShippingThreshold,
        defaultShippingFee,
        thermalPackagingFee,
        enableStateShipping,
        stateShippingRates,
        getStateShippingRate,
        maintenanceMode,
        maintenanceMessage,
        announcement,

        // Customer Authentication
        user,
        token,
        authModalOpen,
        setAuthModalOpen,
        customerLogin,
        customerRegister,
        customerSocialLogin,
        customerLogout,
        updateUserProfile,
        customerOrders,
        loadingOrders,
        loadCustomerOrders,

        // Wishlist
        wishlist,
        wishlistCount: wishlist.length,
        toggleWishlist,
        isWishlisted,

        // Navigation
        currentPage,
        selectedCategory,
        searchQuery,
        activeProductSlug,
        activeTrackingNumber,
        cartOpen,
        checkoutOpen,
        setSelectedCategory,
        setSearchQuery,
        setCartOpen,
        setCheckoutOpen,
        navigateTo,
        openProductModal,
        closeProductModal,
        setActiveTrackingNumber,

        // Cart
        cart,
        cartSubtotal,
        cartCount,
        thermalPackRequested,
        setThermalPackRequested,
        qualifiesForFreeShipping,
        estimatedShippingFee,
        estimatedThermalFee,
        estimatedTotal,
        addToCart,
        updateQuantity,
        removeFromCart,
        clearCart,

        // Coupon
        appliedCoupon,
        couponLoading,
        applyCouponCode,
        removeCoupon,

        // Notifications
        toasts,
        addToast,
        removeToast,
      }}
    >
      {children}
    </StoreContext.Provider>
  );
}

export function useStore() {
  const context = useContext(StoreContext);
  if (!context) {
    throw new Error('useStore must be used within a StoreProvider');
  }
  return context;
}

export default StoreContext;
