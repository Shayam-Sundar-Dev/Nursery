/**
 * Botanical Nursery & Garden API Client
 */

const BASE_URL = import.meta.env.VITE_API_URL || '/api/v1';

async function request(endpoint, options = {}) {
  const url = endpoint.startsWith('http') ? endpoint : `${BASE_URL}${endpoint}`;
  
  const token = localStorage.getItem('botanical_customer_token');

  const headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
    ...(options.headers || {}),
  };

  try {
    const res = await fetch(url, { ...options, headers });
    const data = await res.json().catch(() => ({}));
    
    if (!res.ok) {
      const error = new Error(data.message || 'API request failed');
      error.status = res.status;
      error.data = data;
      throw error;
    }
    
    return data;
  } catch (err) {
    console.error(`API Error [${endpoint}]:`, err);
    throw err;
  }
}

export const api = {
  // Public Storefront Configuration
  getSiteSettings: () => request('/site-settings'),
  
  // Hero Sliders & Banners
  getSliders: () => request('/home/sliders'),
  
  // Product Catalog & Categories
  getCategories: () => request('/categories'),
  getProducts: (params = {}) => {
    const query = new URLSearchParams();
    Object.entries(params).forEach(([key, val]) => {
      if (val !== undefined && val !== null && val !== '') {
        query.append(key, val);
      }
    });
    const qs = query.toString();
    return request(`/products${qs ? `?${qs}` : ''}`);
  },
  getProductBySlug: (slug) => request(`/products/${slug}`),
  
  // Interactive Plant Finder Quiz
  plantFinderQuiz: (answers) => request('/plant-finder', {
    method: 'POST',
    body: JSON.stringify(answers),
  }),
  
  // Cart & Checkout
  validateCoupon: (code, subtotal) => request('/coupons/validate', {
    method: 'POST',
    body: JSON.stringify({ code, subtotal }),
  }),
  checkDeliverability: (postalCode) => request('/shipping/check-deliverability', {
    method: 'POST',
    body: JSON.stringify({ postal_code: postalCode }),
  }),
  getCheckoutSummary: (payload) => request('/checkout/summary', {
    method: 'POST',
    body: JSON.stringify(payload),
  }),
  createOrder: (orderPayload) => request('/checkout/orders', {
    method: 'POST',
    body: JSON.stringify(orderPayload),
  }),
  
  // Live Transit Order Tracking
  trackOrder: (orderNumber) => request(`/orders/${orderNumber}/track`),
  

  // Customer Authentication & Social Sign-In
  customerRegister: (payload) => request('/auth/register', {
    method: 'POST',
    body: JSON.stringify(payload),
  }),
  customerLogin: (payload) => request('/auth/login', {
    method: 'POST',
    body: JSON.stringify(payload),
  }),
  customerForgotPassword: (email) => request('/auth/forgot-password', {
    method: 'POST',
    body: JSON.stringify({ email }),
  }),
  customerResetPassword: (payload) => request('/auth/reset-password', {
    method: 'POST',
    body: JSON.stringify(payload),
  }),
  customerSocialLogin: (payload) => request('/auth/social', {
    method: 'POST',
    body: JSON.stringify(payload),
  }),
  customerGetProfile: () => request('/auth/user'),
  customerUpdateProfile: (payload) => request('/auth/profile', {
    method: 'PUT',
    body: JSON.stringify(payload),
  }),
  customerSyncCart: (items, merge = false) => request('/auth/cart/sync', {
    method: 'POST',
    body: JSON.stringify({ items, merge }),
  }),
  customerSyncWishlist: (items, merge = false) => request('/auth/wishlist/sync', {
    method: 'POST',
    body: JSON.stringify({ items, merge }),
  }),
  customerGetOrders: () => request('/auth/orders'),
  customerLogout: () => request('/auth/logout', {
    method: 'POST',
  }),
};

export default api;
