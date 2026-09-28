import React from 'react';
import { useStore } from './context/StoreContext';
import Header from './components/Header';
import HomeView from './components/HomeView';
import CatalogView from './components/CatalogView';
import PlantFinderQuiz from './components/PlantFinderQuiz';
import OrderTrackingView from './components/OrderTrackingView';

import WishlistView from './components/WishlistView';
import OrdersView from './components/OrdersView';
import CheckoutView from './components/CheckoutView';
import ProductDetailModal from './components/ProductDetailModal';
import CartDrawer from './components/CartDrawer';
import CheckoutModal from './components/CheckoutModal';
import AuthModal from './components/AuthModal';
import ToastContainer from './components/ToastContainer';
import Footer from './components/Footer';
import MaintenanceBanner from './components/MaintenanceBanner';
import ErrorBoundary from './components/ErrorBoundary';

export default function App() {
  const { currentPage } = useStore();

  return (
    <div className="min-h-[100dvh] flex flex-col">
      {/* Keyboard Accessibility Skip Link */}
      <a
        href="#main-content"
        className="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 z-50 px-4 py-2.5 rounded-xl bg-botanical-800 text-white text-xs font-bold shadow-xl border border-botanical-600 outline-none transition"
      >
        Skip to main content
      </a>

      {/* 1. Maintenance Status Bar (if active) */}
      <MaintenanceBanner />

      {/* 2. Main Storefront Header */}
      <Header />

      {/* 3. Dynamic Page View Container */}
      <main id="main-content" className="flex-1">
        <ErrorBoundary>
          {currentPage === 'home' && <HomeView />}
          {currentPage === 'catalog' && <CatalogView />}
          {currentPage === 'quiz' && <PlantFinderQuiz />}
          {currentPage === 'tracking' && <OrderTrackingView />}
          {currentPage === 'checkout' && <CheckoutView />}
          {currentPage === 'wishlist' && <WishlistView />}
          {currentPage === 'orders' && <OrdersView />}
        </ErrorBoundary>
      </main>

      {/* 4. Global Modals & Drawers */}
      <ProductDetailModal />
      <CartDrawer />
      <CheckoutModal />
      <AuthModal />

      {/* 5. Real-time Toast Notifications */}
      <ToastContainer />

      {/* 6. Dynamic Storefront Footer */}
      <Footer />
    </div>
  );
}
