import React, { useState, useEffect, useMemo } from 'react';
import { useStore } from '../context/StoreContext';
import {
  Package,
  Truck,
  Calendar,
  CheckCircle2,
  Clock,
  AlertCircle,
  ExternalLink,
  ChevronDown,
  ShieldCheck,
  ShoppingBag,
  Sparkles,
  ArrowRight,
  User,
  Search,
  Copy,
  Check,
  Filter,
  Printer,
  RefreshCw,
  Leaf,
  MapPin,
  Heart,
  X,
  FileText,
  DollarSign,
  ThermometerSnowflake,
  RotateCcw,
} from 'lucide-react';

export default function OrdersView() {
  const {
    user,
    token,
    setAuthModalOpen,
    customerOrders,
    loadingOrders,
    loadCustomerOrders,
    currencySymbol,
    navigateTo,
    setActiveTrackingNumber,
    addToCart,
    addToast,
  } = useStore();

  const [searchQuery, setSearchQuery] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [expandedOrders, setExpandedOrders] = useState({});
  const [copiedId, setCopiedId] = useState(null);
  const [invoiceOrder, setInvoiceOrder] = useState(null);
  const [isRefreshing, setIsRefreshing] = useState(false);

  useEffect(() => {
    if (token) {
      loadCustomerOrders();
    }
  }, [token]);

  const handleRefresh = async () => {
    setIsRefreshing(true);
    try {
      await loadCustomerOrders();
      addToast('Order history updated', 'info');
    } catch {
      addToast('Could not refresh orders', 'error');
    } finally {
      setIsRefreshing(false);
    }
  };

  const copyToClipboard = (text, label = 'Order ID') => {
    if (!text) return;
    navigator.clipboard.writeText(text);
    setCopiedId(text);
    addToast(`Copied ${label} #${text}`, 'success');
    setTimeout(() => {
      setCopiedId((curr) => (curr === text ? null : curr));
    }, 2000);
  };

  const toggleOrderExpand = (orderId) => {
    setExpandedOrders((prev) => ({
      ...prev,
      [orderId]: !prev[orderId],
    }));
  };

  const handleTrackOrder = (order) => {
    const trackingCode = order.tracking_code || order.tracking_number || order.order_number;
    if (trackingCode) {
      setActiveTrackingNumber(trackingCode);
      navigateTo('tracking');
    }
  };

  const handleReorderItem = (item) => {
    const product = item.variant?.product || {
      id: item.product_id,
      name: item.product_name,
      slug: item.variant?.product?.slug || '',
      primary_image_url: item.variant?.product?.primary_image_url,
      base_price: item.unit_price,
    };
    const variant = item.variant || {
      id: item.product_variant_id,
      title: item.variant_title || 'Standard Pot',
      price: item.unit_price,
    };

    addToCart(product, variant, item.quantity || 1);
  };

  const handleReorderAll = (order) => {
    if (!order.items || order.items.length === 0) return;
    let addedCount = 0;
    order.items.forEach((item) => {
      const product = item.variant?.product || {
        id: item.product_id,
        name: item.product_name,
        slug: item.variant?.product?.slug || '',
        primary_image_url: item.variant?.product?.primary_image_url,
        base_price: item.unit_price,
      };
      const variant = item.variant || {
        id: item.product_variant_id,
        title: item.variant_title || 'Standard Pot',
        price: item.unit_price,
      };
      addToCart(product, variant, item.quantity || 1);
      addedCount++;
    });
    addToast(`Added ${addedCount} items from #${order.order_number} to cart!`, 'success');
  };

  // Status Counts
  const orderStats = useMemo(() => {
    const stats = {
      total: customerOrders.length,
      inTransit: 0,
      delivered: 0,
      processing: 0,
      totalSpecimens: 0,
    };

    customerOrders.forEach((order) => {
      const st = order.status?.toLowerCase();
      if (['shipped', 'in_transit', 'transit'].includes(st)) stats.inTransit++;
      else if (st === 'delivered') stats.delivered++;
      else if (['processing', 'pending', 'paid'].includes(st)) stats.processing++;

      if (Array.isArray(order.items)) {
        order.items.forEach((item) => {
          stats.totalSpecimens += parseInt(item.quantity || 1, 10);
        });
      }
    });

    return stats;
  }, [customerOrders]);

  // Filtered Orders
  const filteredOrders = useMemo(() => {
    return customerOrders.filter((order) => {
      const st = order.status?.toLowerCase() || '';

      if (statusFilter === 'in_transit' && !['shipped', 'in_transit', 'transit'].includes(st)) return false;
      if (statusFilter === 'delivered' && st !== 'delivered') return false;
      if (statusFilter === 'processing' && !['processing', 'pending', 'paid'].includes(st)) return false;
      if (statusFilter === 'cancelled' && st !== 'cancelled') return false;

      if (searchQuery.trim()) {
        const query = searchQuery.toLowerCase().trim();
        const numMatch = order.order_number?.toLowerCase().includes(query);
        const cityMatch = (
          order.shipping_address?.city ||
          order.city ||
          ''
        ).toLowerCase().includes(query);
        const itemMatch = order.items?.some(
          (item) =>
            item.product_name?.toLowerCase().includes(query) ||
            item.plant_name?.toLowerCase().includes(query) ||
            item.variant_title?.toLowerCase().includes(query)
        );
        return numMatch || cityMatch || itemMatch;
      }

      return true;
    });
  }, [customerOrders, statusFilter, searchQuery]);

  // Status Badge Helper
  const getStatusBadge = (status) => {
    switch (status?.toLowerCase()) {
      case 'delivered':
        return (
          <span className="inline-flex items-center gap-1 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10px] sm:text-[11px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-300/70 shrink-0">
            <CheckCircle2 className="w-3 h-3 sm:w-3.5 sm:h-3.5 text-emerald-700" />
            <span>Delivered</span>
          </span>
        );
      case 'shipped':
      case 'in_transit':
      case 'transit':
        return (
          <span className="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10px] sm:text-[11px] font-bold uppercase tracking-wider bg-amber-100 text-amber-950 border border-amber-300/80 shrink-0">
            <span className="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping" />
            <Truck className="w-3 h-3 sm:w-3.5 sm:h-3.5 text-amber-800" />
            <span>In Transit</span>
          </span>
        );
      case 'processing':
      case 'pending':
        return (
          <span className="inline-flex items-center gap-1 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10px] sm:text-[11px] font-bold uppercase tracking-wider bg-sky-100 text-sky-950 border border-sky-300/70 shrink-0">
            <Leaf className="w-3 h-3 sm:w-3.5 sm:h-3.5 text-sky-700 animate-pulse" />
            <span>Preparing</span>
          </span>
        );
      case 'cancelled':
        return (
          <span className="inline-flex items-center gap-1 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10px] sm:text-[11px] font-bold uppercase tracking-wider bg-rose-100 text-rose-900 border border-rose-300/70 shrink-0">
            <AlertCircle className="w-3 h-3 sm:w-3.5 sm:h-3.5 text-rose-600" />
            <span>Cancelled</span>
          </span>
        );
      default:
        return (
          <span className="inline-flex items-center gap-1 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full text-[10px] sm:text-[11px] font-bold uppercase tracking-wider bg-stone-100 text-stone-800 border border-stone-300/70 shrink-0">
            <Clock className="w-3 h-3 sm:w-3.5 sm:h-3.5 text-stone-500" />
            <span>Placed</span>
          </span>
        );
    }
  };

  // Progress Step Helper (0 to 3)
  const getProgressStep = (status) => {
    switch (status?.toLowerCase()) {
      case 'delivered':
        return 3;
      case 'shipped':
      case 'in_transit':
      case 'transit':
        return 2;
      case 'processing':
      case 'pending':
        return 1;
      case 'cancelled':
        return -1;
      default:
        return 0;
    }
  };

  const getStageTitle = (step) => {
    switch (step) {
      case 0:
        return 'Stage 1 of 4: Order Confirmed';
      case 1:
        return 'Stage 2 of 4: Greenhouse Conditioning & Root Hydration';
      case 2:
        return 'Stage 3 of 4: In Climate-Insulated Transit';
      case 3:
        return 'Stage 4 of 4: Delivered & Flourishing';
      default:
        return 'Order Cancelled';
    }
  };

  // Unauthenticated State
  if (!user && !token) {
    return (
      <div className="max-w-3xl mx-auto px-4 py-12 sm:py-20">
        <div className="p-1 sm:p-2 rounded-2xl sm:rounded-[2.25rem] bg-gradient-to-b from-stone-200/60 to-stone-100/40 border border-stone-200 shadow-card">
          <div className="rounded-xl sm:rounded-[calc(2.25rem-0.375rem)] bg-white p-6 sm:p-12 text-center space-y-6">
            <div className="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-2xl sm:rounded-3xl bg-botanical-50 border border-botanical-200/80 flex items-center justify-center text-botanical-700 shadow-subtle">
              <User className="w-8 h-8 sm:w-10 sm:h-10" />
            </div>

            <div className="space-y-2 max-w-md mx-auto">
              <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-botanical-100 text-botanical-800 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider">
                <Sparkles className="w-3.5 h-3.5 text-amber-500" />
                <span>Customer Care & History</span>
              </div>
              <h2 className="font-serif text-2xl sm:text-4xl font-bold text-stone-900 tracking-tight">
                Sign in to view your orders
              </h2>
              <p className="text-stone-500 text-xs sm:text-sm leading-relaxed">
                Connect your account or use social sign-in to review your nursery acquisitions, inspect thermal packing audits, and track climate-controlled live shipments in real time.
              </p>
            </div>

            <div className="flex flex-col sm:flex-row justify-center gap-3 pt-2">
              <button
                onClick={() => setAuthModalOpen('login')}
                className="w-full sm:w-auto px-6 py-3 rounded-full bg-botanical-800 hover:bg-botanical-900 text-white font-bold text-xs uppercase tracking-wider shadow-md transition flex items-center justify-center gap-2.5 active:scale-95 cursor-pointer"
              >
                <span>Sign In to Account</span>
                <ArrowRight className="w-3.5 h-3.5 text-emerald-200" />
              </button>

              <button
                onClick={() => navigateTo('tracking')}
                className="w-full sm:w-auto px-6 py-3 rounded-full border border-stone-300 hover:bg-stone-50 text-stone-700 font-bold text-xs transition flex items-center justify-center gap-2 cursor-pointer active:scale-95"
              >
                <Truck className="w-4 h-4 text-stone-400" />
                <span>Track Order via Code</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-12 space-y-5 sm:space-y-8">
      {/* 1. Header & Patron Dashboard Banner */}
      <div className="p-1 sm:p-2 rounded-2xl sm:rounded-[2.25rem] bg-gradient-to-b from-stone-200/70 via-stone-100/50 to-white/30 border border-stone-200 shadow-card">
        <div className="rounded-xl sm:rounded-[calc(2.25rem-0.375rem)] bg-gradient-to-br from-sand-50 via-white to-botanical-50/40 p-4 sm:p-8 border border-stone-100">
          
          {/* Top Row: User info and Buttons */}
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 sm:pb-6 border-b border-stone-200/80">
            <div className="flex items-center gap-3 sm:gap-4">
              <div className="relative shrink-0">
                <div className="w-12 h-12 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-botanical-700 to-botanical-900 text-white flex items-center justify-center font-serif text-xl sm:text-2xl font-bold shadow-md shadow-botanical-900/20 border-2 border-white">
                  {user?.name ? user.name.charAt(0).toUpperCase() : <Leaf className="w-6 h-6 text-emerald-300" />}
                </div>
                <div className="absolute -bottom-0.5 -right-0.5 w-5 h-5 rounded-full bg-emerald-500 border-2 border-white flex items-center justify-center" title="Active Account">
                  <Check className="w-3 h-3 text-white" />
                </div>
              </div>

              <div className="min-w-0 flex-1">
                <div className="flex items-center gap-1.5 flex-wrap">
                  <span className="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-botanical-700 bg-botanical-100 px-2 py-0.5 rounded-full border border-botanical-200/80 shrink-0">
                    Botanical Patron
                  </span>
                  <span className="text-stone-300 text-xs hidden xs:inline">•</span>
                  <span className="text-xs text-stone-500 font-medium truncate max-w-[150px] xs:max-w-[200px] sm:max-w-none">
                    {user?.email}
                  </span>
                </div>
                <h1 className="font-serif text-xl sm:text-3xl font-bold text-stone-900 tracking-tight mt-0.5 truncate">
                  {user?.name ? `${user.name}'s Orders` : 'My Botanical Orders'}
                </h1>
              </div>
            </div>

            {/* Top Action Buttons on Mobile */}
            <div className="flex items-center gap-2 pt-1 sm:pt-0">
              <button
                onClick={handleRefresh}
                disabled={loadingOrders || isRefreshing}
                className="flex-1 sm:flex-initial px-3.5 py-2 rounded-full bg-white hover:bg-stone-50 border border-stone-200 text-stone-700 text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs active:scale-95 disabled:opacity-60"
              >
                <RefreshCw className={`w-3.5 h-3.5 text-stone-500 ${isRefreshing ? 'animate-spin' : ''}`} />
                <span>{isRefreshing ? 'Updating...' : 'Refresh'}</span>
              </button>

              <button
                onClick={() => navigateTo('catalog')}
                className="flex-1 sm:flex-initial px-4 py-2 rounded-full bg-botanical-800 hover:bg-botanical-900 text-white text-xs font-bold uppercase tracking-wider shadow-sm transition flex items-center justify-center gap-1.5 cursor-pointer active:scale-95"
              >
                <span>Nursery</span>
                <ArrowRight className="w-3 h-3 text-emerald-200" />
              </button>
            </div>
          </div>

          {/* Quick Metrics Bar: 2x2 grid on mobile */}
          <div className="grid grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-4 pt-4 sm:pt-5">
            <div className="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-white border border-stone-200/80 shadow-2xs">
              <div className="flex items-center gap-1.5 text-stone-500 text-[11px] sm:text-xs font-semibold mb-0.5">
                <Package className="w-3.5 h-3.5 text-botanical-700" />
                <span>Total Orders</span>
              </div>
              <div className="font-serif text-xl sm:text-2xl font-bold text-stone-900 tabular-nums">
                {orderStats.total}
              </div>
            </div>

            <div className="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-white border border-stone-200/80 shadow-2xs">
              <div className="flex items-center gap-1.5 text-stone-500 text-[11px] sm:text-xs font-semibold mb-0.5">
                <Truck className="w-3.5 h-3.5 text-amber-600" />
                <span>In Transit</span>
              </div>
              <div className="font-serif text-xl sm:text-2xl font-bold text-amber-700 tabular-nums flex items-center gap-1.5">
                <span>{orderStats.inTransit}</span>
                {orderStats.inTransit > 0 && (
                  <span className="w-2 h-2 rounded-full bg-amber-500 animate-ping inline-block" />
                )}
              </div>
            </div>

            <div className="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-white border border-stone-200/80 shadow-2xs">
              <div className="flex items-center gap-1.5 text-stone-500 text-[11px] sm:text-xs font-semibold mb-0.5">
                <Leaf className="w-3.5 h-3.5 text-emerald-600" />
                <span>Specimens</span>
              </div>
              <div className="font-serif text-xl sm:text-2xl font-bold text-emerald-800 tabular-nums">
                {orderStats.totalSpecimens}
              </div>
            </div>

            <div className="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-white border border-stone-200/80 shadow-2xs">
              <div className="flex items-center gap-1.5 text-stone-500 text-[11px] sm:text-xs font-semibold mb-0.5">
                <ShieldCheck className="w-3.5 h-3.5 text-sky-600" />
                <span>Arrival Transit</span>
              </div>
              <div className="font-serif text-xs sm:text-sm font-bold text-emerald-700 mt-1">
                100% Insured
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* 2. Search & Filter Bar on Mobile */}
      <div className="space-y-3">
        {/* Horizontal Scrollable Tabs */}
        <div className="flex items-center gap-1.5 p-1 rounded-full bg-sand-100 border border-stone-200/80 overflow-x-auto no-scrollbar -webkit-overflow-scrolling-touch">
          {[
            { id: 'all', label: 'All', count: orderStats.total },
            { id: 'in_transit', label: 'In Transit', count: orderStats.inTransit },
            { id: 'delivered', label: 'Delivered', count: orderStats.delivered },
            { id: 'processing', label: 'Processing', count: orderStats.processing },
          ].map((tab) => (
            <button
              key={tab.id}
              onClick={() => setStatusFilter(tab.id)}
              className={`px-3 sm:px-4 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap flex items-center gap-1.5 shrink-0 cursor-pointer ${
                statusFilter === tab.id
                  ? 'bg-botanical-800 text-white shadow-xs'
                  : 'text-stone-600 hover:text-stone-900 hover:bg-white/60'
              }`}
            >
              <span>{tab.label}</span>
              <span
                className={`text-[10px] px-1.5 py-0.2 rounded-full ${
                  statusFilter === tab.id
                    ? 'bg-white/20 text-white'
                    : 'bg-stone-200 text-stone-600'
                }`}
              >
                {tab.count}
              </span>
            </button>
          ))}
        </div>

        {/* Full-width Search Input */}
        <div className="relative w-full">
          <Search className="w-4 h-4 text-stone-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            placeholder="Search order #, plant, or city..."
            className="w-full pl-9 pr-8 py-2 rounded-full bg-white border border-stone-200 text-xs font-medium placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-botanical-500/20 focus:border-botanical-700 transition"
          />
          {searchQuery && (
            <button
              onClick={() => setSearchQuery('')}
              className="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 p-0.5 cursor-pointer"
            >
              <X className="w-3.5 h-3.5" />
            </button>
          )}
        </div>
      </div>

      {/* 3. Loading State */}
      {loadingOrders && customerOrders.length === 0 && (
        <div className="space-y-4">
          {[1, 2].map((n) => (
            <div
              key={n}
              className="p-1 sm:p-2 rounded-2xl sm:rounded-[2.25rem] bg-stone-100 border border-stone-200 animate-pulse"
            >
              <div className="rounded-xl sm:rounded-[calc(2.25rem-0.375rem)] bg-white p-5 sm:p-6 space-y-4">
                <div className="flex justify-between items-center">
                  <div className="h-5 bg-sand-200 rounded-full w-36" />
                  <div className="h-6 bg-sand-200 rounded-full w-24" />
                </div>
                <div className="h-16 bg-sand-100 rounded-xl" />
                <div className="h-8 bg-sand-200/80 rounded-lg" />
              </div>
            </div>
          ))}
        </div>
      )}

      {/* 4. Empty State */}
      {!loadingOrders && customerOrders.length === 0 && (
        <div className="p-1 sm:p-2 rounded-2xl sm:rounded-[2.25rem] bg-gradient-to-b from-stone-200/60 to-stone-100/40 border border-stone-200 shadow-card">
          <div className="rounded-xl sm:rounded-[calc(2.25rem-0.375rem)] bg-white p-8 sm:p-14 text-center space-y-5">
            <div className="w-16 h-16 mx-auto rounded-2xl bg-botanical-50 border border-botanical-200/80 flex items-center justify-center text-botanical-700 shadow-subtle">
              <ShoppingBag className="w-8 h-8" />
            </div>

            <div className="space-y-1.5 max-w-sm mx-auto">
              <h3 className="font-serif text-xl sm:text-2xl font-bold text-stone-900">
                No orders placed yet
              </h3>
              <p className="text-stone-500 text-xs sm:text-sm leading-relaxed">
                Start your botanical collection today. Discover healthy tropicals, indoor trees, and acclimated plant specimens.
              </p>
            </div>

            <button
              onClick={() => navigateTo('catalog')}
              className="px-6 py-3 rounded-full bg-botanical-800 hover:bg-botanical-900 text-white font-bold text-xs uppercase tracking-wider shadow-md transition inline-flex items-center gap-2 cursor-pointer active:scale-95"
            >
              <span>Explore Catalog</span>
              <ArrowRight className="w-3.5 h-3.5 text-emerald-200" />
            </button>
          </div>
        </div>
      )}

      {/* 5. Filter Result Empty */}
      {!loadingOrders && customerOrders.length > 0 && filteredOrders.length === 0 && (
        <div className="p-6 sm:p-10 text-center bg-white rounded-2xl border border-stone-200 space-y-3">
          <Filter className="w-7 h-7 text-stone-400 mx-auto" />
          <h4 className="font-serif text-base sm:text-lg font-bold text-stone-800">
            No matching orders
          </h4>
          <p className="text-xs text-stone-500 max-w-xs mx-auto">
            Try adjusting your search query or switching your active filter tab.
          </p>
          <button
            onClick={() => {
              setSearchQuery('');
              setStatusFilter('all');
            }}
            className="px-4 py-2 rounded-full border border-stone-300 text-stone-700 text-xs font-bold hover:bg-stone-50 transition cursor-pointer"
          >
            Clear Filters
          </button>
        </div>
      )}

      {/* 6. Orders List (Mobile-Optimized Double Bezel) */}
      <div className="space-y-5 sm:space-y-7">
        {filteredOrders.map((order) => {
          const isExpanded = expandedOrders[order.id];
          const formattedDate = order.created_at
            ? new Date(order.created_at).toLocaleDateString('en-IN', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
              })
            : 'Recent';

          const subtotal = parseFloat(order.subtotal || 0);
          const discount = parseFloat(order.discount_amount || 0);
          const tax = parseFloat(order.tax_amount || 0);
          const shipping = parseFloat(order.shipping_fee || 0);
          const insulation = parseFloat(order.insulation_packaging_fee || 0);
          const total = parseFloat(order.total_amount || 0);

          const step = getProgressStep(order.status);
          const shippingAddr =
            typeof order.shipping_address === 'object' && order.shipping_address !== null
              ? order.shipping_address
              : {
                  street: order.street || '',
                  city: order.city || '',
                  state: order.state || '',
                  postal_code: order.postal_code || '',
                };

          const trackingCode = order.tracking_code || order.tracking_number;
          const hasThermal = insulation > 0 || order.thermal_packaging;

          return (
            <div
              key={order.id}
              className="p-1 sm:p-2 rounded-2xl sm:rounded-[2.25rem] bg-gradient-to-b from-stone-200/60 via-stone-100/40 to-stone-200/50 border border-stone-200/90 shadow-card hover:shadow-card-hover transition-all duration-300"
            >
              <div className="rounded-xl sm:rounded-[calc(2.25rem-0.375rem)] bg-white overflow-hidden border border-stone-100/80">
                
                {/* A. Order Meta Banner */}
                <div className="p-3.5 sm:p-6 bg-sand-50/70 border-b border-stone-200/80 space-y-2.5">
                  {/* Top Line: Order ID + Status + Receipt button */}
                  <div className="flex items-center justify-between gap-2">
                    <div className="flex items-center gap-2 min-w-0">
                      <div className="flex items-center gap-1.5 bg-white px-2.5 py-1 rounded-lg border border-stone-200/90 shadow-2xs shrink-0">
                        <span className="font-mono text-xs sm:text-sm font-bold text-stone-900">
                          #{order.order_number}
                        </span>
                        <button
                          onClick={() => copyToClipboard(order.order_number)}
                          title="Copy Order ID"
                          className="text-stone-400 hover:text-stone-700 transition p-0.5 cursor-pointer"
                        >
                          {copiedId === order.order_number ? (
                            <Check className="w-3.5 h-3.5 text-emerald-600" />
                          ) : (
                            <Copy className="w-3.5 h-3.5" />
                          )}
                        </button>
                      </div>

                      {getStatusBadge(order.status)}
                    </div>

                    <button
                      onClick={() => setInvoiceOrder(order)}
                      className="p-1.5 sm:p-2 rounded-full border border-stone-200 bg-white hover:bg-stone-50 text-stone-600 text-xs font-semibold transition flex items-center justify-center cursor-pointer shadow-2xs shrink-0"
                      title="View Invoice"
                    >
                      <FileText className="w-3.5 h-3.5 sm:w-4 sm:h-4 text-stone-500" />
                    </button>
                  </div>

                  {/* Subline: Date, City, Badges */}
                  <div className="flex flex-wrap items-center gap-2 sm:gap-3 text-[11px] sm:text-xs text-stone-500">
                    <span className="flex items-center gap-1">
                      <Calendar className="w-3 h-3 text-stone-400" />
                      <span>{formattedDate}</span>
                    </span>

                    {shippingAddr?.city && (
                      <>
                        <span className="text-stone-300">•</span>
                        <span className="flex items-center gap-1">
                          <MapPin className="w-3 h-3 text-stone-400" />
                          <span className="truncate max-w-[140px] xs:max-w-none">
                            {shippingAddr.city}, {shippingAddr.state}
                          </span>
                        </span>
                      </>
                    )}

                    {hasThermal && (
                      <span className="inline-flex items-center gap-1 text-[10px] font-bold text-amber-900 bg-amber-100 px-2 py-0.5 rounded-full border border-amber-200">
                        <ThermometerSnowflake className="w-3 h-3 text-amber-700" />
                        <span>Thermal Insulated</span>
                      </span>
                    )}

                    <span className="inline-flex items-center gap-1 text-[10px] font-semibold text-stone-600 bg-white px-2 py-0.5 rounded-full border border-stone-200">
                      <ShieldCheck className="w-3 h-3 text-emerald-600" />
                      <span className="capitalize">{order.payment_method || 'Card'}</span>
                    </span>
                  </div>
                </div>

                {/* B. Live Shipment Progress Stepper */}
                {step >= 0 && (
                  <div className="px-4 sm:px-8 py-3.5 sm:py-5 bg-sand-50/30 border-b border-stone-100 space-y-2">
                    <div className="relative flex items-center justify-between">
                      {/* Background Bar */}
                      <div className="absolute top-1/2 left-0 right-0 -translate-y-1/2 h-1 bg-stone-200/80 z-0" />

                      {/* Active Fill */}
                      <div
                        className="absolute top-1/2 left-0 -translate-y-1/2 h-1 bg-gradient-to-r from-botanical-700 via-botanical-600 to-emerald-500 transition-all duration-700 z-0"
                        style={{
                          width:
                            step === 0 ? '5%' : step === 1 ? '35%' : step === 2 ? '70%' : '100%',
                        }}
                      />

                      {/* Step 1: Placed */}
                      <div className="relative z-10 flex flex-col items-center">
                        <div
                          className={`w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-all ${
                            step >= 0
                              ? 'bg-botanical-800 text-white border-botanical-800 shadow-2xs'
                              : 'bg-white text-stone-400 border-stone-300'
                          }`}
                        >
                          <Check className="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                        </div>
                        <span className="text-[10px] font-bold uppercase tracking-wider text-stone-700 mt-1.5 hidden sm:block">
                          Confirmed
                        </span>
                      </div>

                      {/* Step 2: Prep */}
                      <div className="relative z-10 flex flex-col items-center">
                        <div
                          className={`w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-all ${
                            step >= 1
                              ? 'bg-botanical-800 text-white border-botanical-800 shadow-2xs'
                              : 'bg-white text-stone-400 border-stone-300'
                          }`}
                        >
                          {step > 1 ? (
                            <Check className="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                          ) : (
                            <Leaf className="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                          )}
                        </div>
                        <span className="text-[10px] font-bold uppercase tracking-wider text-stone-700 mt-1.5 hidden sm:block">
                          Greenhouse Prep
                        </span>
                      </div>

                      {/* Step 3: Transit */}
                      <div className="relative z-10 flex flex-col items-center">
                        <div
                          className={`w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-all ${
                            step >= 2
                              ? 'bg-botanical-800 text-white border-botanical-800 shadow-2xs'
                              : 'bg-white text-stone-400 border-stone-300'
                          }`}
                        >
                          {step > 2 ? (
                            <Check className="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                          ) : (
                            <Truck className="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                          )}
                        </div>
                        <span className="text-[10px] font-bold uppercase tracking-wider text-stone-700 mt-1.5 hidden sm:block">
                          Climate Transit
                        </span>
                      </div>

                      {/* Step 4: Delivered */}
                      <div className="relative z-10 flex flex-col items-center">
                        <div
                          className={`w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-all ${
                            step >= 3
                              ? 'bg-emerald-600 text-white border-emerald-600 shadow-2xs'
                              : 'bg-white text-stone-400 border-stone-300'
                          }`}
                        >
                          <CheckCircle2 className="w-3 h-3 sm:w-3.5 sm:h-3.5" />
                        </div>
                        <span className="text-[10px] font-bold uppercase tracking-wider text-stone-700 mt-1.5 hidden sm:block">
                          Delivered
                        </span>
                      </div>
                    </div>

                    {/* Mobile Stage Description Pill */}
                    <div className="sm:hidden text-center pt-1">
                      <span className="inline-block text-[10px] font-semibold text-stone-600 bg-sand-100 px-2.5 py-0.5 rounded-full border border-stone-200/80">
                        {getStageTitle(step)}
                      </span>
                    </div>
                  </div>
                )}

                {/* C. Order Items Showcase (Mobile Optimized) */}
                <div className="p-3.5 sm:p-6 divide-y divide-stone-100">
                  {order.items?.map((item) => {
                    const itemPrice = parseFloat(item.unit_price || 0);
                    const itemSubtotal = parseFloat(item.subtotal || itemPrice * item.quantity);
                    const imageUrl =
                      item.variant?.product?.primary_image_url ||
                      item.product?.primary_image_url ||
                      'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?auto=format&fit=crop&w=400&q=80';

                    const productName = item.product_name || item.plant_name || 'Botanical Specimen';
                    const variantTitle = item.variant_title || item.variant_name || 'Standard Pot';
                    const botanicalName = item.variant?.product?.botanical_name || '';

                    return (
                      <div
                        key={item.id}
                        className="py-3 sm:py-4 first:pt-0 last:pb-0 space-y-2.5"
                      >
                        {/* Top: Image + Info + Price */}
                        <div className="flex items-start gap-3">
                          <div className="relative w-16 h-16 sm:w-20 sm:h-20 rounded-xl sm:rounded-2xl overflow-hidden bg-sand-100 border border-stone-200/80 shrink-0">
                            <img
                              src={imageUrl}
                              alt={productName}
                              className="w-full h-full object-cover"
                            />
                            <div className="absolute top-1 left-1 px-1.5 py-0.5 rounded bg-stone-900/80 text-white text-[9px] font-mono font-bold sm:hidden">
                              ×{item.quantity}
                            </div>
                          </div>

                          <div className="flex-1 min-w-0">
                            <div className="flex items-start justify-between gap-2">
                              <div>
                                <h4 className="font-serif text-sm sm:text-base font-bold text-stone-900 leading-snug line-clamp-1">
                                  {productName}
                                </h4>
                                {botanicalName && (
                                  <p className="text-[11px] italic text-stone-400 font-serif line-clamp-1">
                                    {botanicalName}
                                  </p>
                                )}
                              </div>

                              <div className="text-right shrink-0 tabular-nums">
                                <div className="font-serif font-bold text-stone-900 text-sm sm:text-base">
                                  {currencySymbol}{itemSubtotal.toFixed(2)}
                                </div>
                                <div className="text-[10px] text-stone-400">
                                  {currencySymbol}{itemPrice.toFixed(2)} ea
                                </div>
                              </div>
                            </div>

                            <div className="flex flex-wrap items-center gap-1.5 pt-1">
                              <span className="text-[10px] sm:text-xs font-semibold text-stone-700 bg-sand-100 px-2 py-0.5 rounded border border-stone-200/60">
                                {variantTitle}
                              </span>
                              <span className="text-stone-300 text-xs hidden sm:inline">•</span>
                              <span className="text-xs text-stone-500 tabular-nums hidden sm:inline">
                                Qty: <strong>{item.quantity}</strong>
                              </span>
                            </div>
                          </div>
                        </div>

                        {/* Bottom Row on Mobile: Qty + Reorder Item */}
                        <div className="flex items-center justify-between pt-1 sm:pt-0">
                          <span className="text-xs text-stone-500 tabular-nums sm:hidden">
                            Quantity: <strong>{item.quantity}</strong>
                          </span>

                          <button
                            onClick={() => handleReorderItem(item)}
                            className="ml-auto px-2.5 py-1 rounded-full border border-stone-200 hover:bg-stone-50 text-stone-700 text-[10px] sm:text-[11px] font-bold transition flex items-center gap-1 cursor-pointer shadow-2xs active:scale-95"
                          >
                            <RotateCcw className="w-3 h-3 text-stone-400" />
                            <span>Buy Again</span>
                          </button>
                        </div>
                      </div>
                    );
                  })}
                </div>

                {/* D. Expandable Breakdown & Address */}
                {isExpanded && (
                  <div className="px-3.5 sm:px-6 py-4 bg-sand-50/70 border-t border-stone-100 text-xs text-stone-600 space-y-3.5">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-6">
                      {/* Destination Card */}
                      <div className="p-3.5 rounded-xl bg-white border border-stone-200/80 space-y-1">
                        <div className="font-bold text-stone-800 uppercase tracking-wider text-[10px] flex items-center gap-1.5 mb-1.5">
                          <MapPin className="w-3.5 h-3.5 text-botanical-700" />
                          <span>Delivery Destination</span>
                        </div>
                        <p className="font-semibold text-stone-900">{order.customer_name || user?.name}</p>
                        <p>{shippingAddr?.street}</p>
                        <p>
                          {shippingAddr?.city}, {shippingAddr?.state} {shippingAddr?.postal_code}
                        </p>
                        {order.customer_phone && (
                          <p className="text-stone-500 pt-0.5">Phone: {order.customer_phone}</p>
                        )}
                        {trackingCode && (
                          <div className="pt-1.5 flex items-center gap-2">
                            <span className="font-mono text-stone-800 bg-sand-100 px-2 py-0.5 rounded border border-stone-200 text-[10px] sm:text-[11px] truncate max-w-[200px]">
                              {trackingCode}
                            </span>
                            <button
                              onClick={() => copyToClipboard(trackingCode, 'Tracking Code')}
                              className="text-stone-400 hover:text-stone-700 cursor-pointer p-0.5"
                            >
                              <Copy className="w-3 h-3" />
                            </button>
                          </div>
                        )}
                      </div>

                      {/* Accounting Breakdown */}
                      <div className="p-3.5 rounded-xl bg-white border border-stone-200/80 space-y-1.5">
                        <div className="font-bold text-stone-800 uppercase tracking-wider text-[10px] flex items-center gap-1.5 mb-1.5">
                          <DollarSign className="w-3.5 h-3.5 text-botanical-700" />
                          <span>Itemized Breakdown</span>
                        </div>

                        <div className="flex justify-between py-0.5 border-b border-stone-100 text-xs">
                          <span className="text-stone-500">Subtotal:</span>
                          <span className="font-medium text-stone-800 tabular-nums">
                            {currencySymbol}{subtotal.toFixed(2)}
                          </span>
                        </div>

                        {discount > 0 && (
                          <div className="flex justify-between py-0.5 border-b border-stone-100 text-emerald-700 text-xs">
                            <span className="flex items-center gap-1">
                              <span>Coupon:</span>
                              {order.coupon_code && (
                                <span className="font-mono text-[9px] bg-emerald-50 px-1 rounded border border-emerald-200">
                                  {order.coupon_code}
                                </span>
                              )}
                            </span>
                            <span className="font-medium tabular-nums">
                              -{currencySymbol}{discount.toFixed(2)}
                            </span>
                          </div>
                        )}

                        <div className="flex justify-between py-0.5 border-b border-stone-100 text-xs">
                          <span className="text-stone-500">Shipping:</span>
                          <span className="font-medium text-stone-800 tabular-nums">
                            {shipping === 0 ? (
                              <strong className="text-emerald-700">FREE</strong>
                            ) : (
                              `${currencySymbol}${shipping.toFixed(2)}`
                            )}
                          </span>
                        </div>

                        {insulation > 0 && (
                          <div className="flex justify-between py-0.5 border-b border-stone-100 text-xs">
                            <span className="text-stone-500">Thermal Packaging:</span>
                            <span className="font-medium text-stone-800 tabular-nums">
                              {currencySymbol}{insulation.toFixed(2)}
                            </span>
                          </div>
                        )}

                        <div className="flex justify-between py-0.5 border-b border-stone-100 text-xs">
                          <span className="text-stone-500">Estimated Tax (8%):</span>
                          <span className="font-medium text-stone-800 tabular-nums">
                            {currencySymbol}{tax.toFixed(2)}
                          </span>
                        </div>

                        <div className="flex justify-between pt-1 font-bold text-stone-900 text-xs sm:text-sm">
                          <span>Total Paid:</span>
                          <span className="font-serif tabular-nums">
                            {currencySymbol}{total.toFixed(2)}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {/* E. Order Card Footer & Master Action Row (Mobile Optimized) */}
                <div className="p-3.5 sm:p-5 bg-sand-50/50 border-t border-stone-100 space-y-3">
                  {/* Row 1: Total and Accordion Toggle */}
                  <div className="flex items-center justify-between gap-3">
                    <button
                      onClick={() => toggleOrderExpand(order.id)}
                      className="inline-flex items-center gap-1 text-[11px] sm:text-xs font-bold text-stone-600 hover:text-stone-900 transition cursor-pointer"
                    >
                      <span>{isExpanded ? 'Hide Details' : 'View Details & Pricing'}</span>
                      <ChevronDown
                        className={`w-3.5 h-3.5 transition-transform duration-200 ${
                          isExpanded ? 'rotate-180' : ''
                        }`}
                      />
                    </button>

                    <div className="text-right tabular-nums">
                      <span className="text-[10px] uppercase font-bold text-stone-400 mr-1.5 sm:hidden">Total:</span>
                      <span className="text-lg sm:text-2xl font-serif font-bold text-stone-900">
                        {currencySymbol}{total.toFixed(2)}
                      </span>
                    </div>
                  </div>

                  {/* Row 2: Thumb-Friendly Actions */}
                  <div className="flex items-center gap-2 pt-1">
                    {trackingCode ? (
                      <button
                        onClick={() => handleTrackOrder(order)}
                        className="flex-1 py-2.5 px-3 rounded-full bg-botanical-800 hover:bg-botanical-900 text-white text-xs font-bold shadow-xs transition flex items-center justify-center gap-2 cursor-pointer active:scale-95"
                      >
                        <Truck className="w-3.5 h-3.5 text-emerald-200" />
                        <span>Track Shipment</span>
                      </button>
                    ) : null}

                    <button
                      onClick={() => handleReorderAll(order)}
                      className={`py-2.5 px-4 rounded-full border border-botanical-800/30 hover:bg-botanical-50 text-botanical-900 text-xs font-bold shadow-2xs transition flex items-center justify-center gap-1.5 cursor-pointer active:scale-95 ${
                        trackingCode ? 'flex-1' : 'w-full bg-botanical-800 text-white hover:bg-botanical-900'
                      }`}
                    >
                      <ShoppingBag className="w-3.5 h-3.5" />
                      <span>Reorder All</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {/* 7. Printable Invoice Modal (Mobile View-Optimized) */}
      {invoiceOrder && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-stone-900/60 backdrop-blur-xs animate-in fade-in duration-200">
          <div className="relative w-full max-w-xl max-h-[92vh] overflow-y-auto bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-8 shadow-2xl border border-stone-200 space-y-5">
            {/* Modal Header */}
            <div className="flex items-start justify-between pb-4 border-b border-stone-200">
              <div className="flex items-center gap-2 text-botanical-800 font-serif font-bold text-lg sm:text-xl">
                <Leaf className="w-5 h-5 text-emerald-600 shrink-0" />
                <span>The Botanical Haven</span>
              </div>

              <div className="flex items-center gap-2">
                <button
                  onClick={() => window.print()}
                  className="px-3 py-1.5 rounded-full border border-stone-200 bg-sand-50 hover:bg-sand-100 text-stone-700 text-xs font-bold flex items-center gap-1 cursor-pointer"
                >
                  <Printer className="w-3.5 h-3.5" />
                  <span className="hidden xs:inline">Print</span>
                </button>
                <button
                  onClick={() => setInvoiceOrder(null)}
                  className="p-1.5 rounded-full text-stone-400 hover:text-stone-700 hover:bg-stone-100 cursor-pointer"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>
            </div>

            {/* Invoice Meta */}
            <div className="grid grid-cols-1 xs:grid-cols-2 gap-3 text-xs">
              <div>
                <span className="text-stone-400 block uppercase font-bold text-[10px]">Order Number</span>
                <span className="font-mono font-bold text-stone-900 text-xs sm:text-sm">
                  #{invoiceOrder.order_number}
                </span>
                <span className="text-stone-500 block text-[11px] mt-0.5">
                  Date: {new Date(invoiceOrder.created_at || Date.now()).toLocaleDateString('en-IN', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                  })}
                </span>
              </div>

              <div className="xs:text-right">
                <span className="text-stone-400 block uppercase font-bold text-[10px]">Shipped To</span>
                <span className="font-bold text-stone-900">{invoiceOrder.customer_name || user?.name}</span>
                <span className="text-stone-500 block text-[11px]">
                  {invoiceOrder.shipping_address?.street || invoiceOrder.street}
                </span>
                <span className="text-stone-500 block text-[11px]">
                  {invoiceOrder.shipping_address?.city || invoiceOrder.city},{' '}
                  {invoiceOrder.shipping_address?.state || invoiceOrder.state}{' '}
                  {invoiceOrder.shipping_address?.postal_code || invoiceOrder.postal_code}
                </span>
              </div>
            </div>

            {/* Responsive Table with horizontal scroll wrapper */}
            <div className="border border-stone-200 rounded-xl overflow-x-auto">
              <table className="w-full text-left text-xs divide-y divide-stone-200 min-w-[320px]">
                <thead className="bg-sand-50/80 text-stone-500 font-bold uppercase text-[9px] sm:text-[10px]">
                  <tr>
                    <th className="py-2 px-3">Specimen</th>
                    <th className="py-2 px-2 text-center">Qty</th>
                    <th className="py-2 px-3 text-right">Price</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-stone-100">
                  {invoiceOrder.items?.map((item) => {
                    const price = parseFloat(item.unit_price || 0);
                    const sub = parseFloat(item.subtotal || price * item.quantity);
                    return (
                      <tr key={item.id} className="text-stone-800">
                        <td className="py-2.5 px-3">
                          <div className="font-semibold text-xs leading-snug">
                            {item.product_name || item.plant_name}
                          </div>
                          <div className="text-[10px] text-stone-400">
                            {item.variant_title || item.variant_name || 'Standard'}
                          </div>
                        </td>
                        <td className="py-2.5 px-2 text-center tabular-nums font-bold">
                          {item.quantity}
                        </td>
                        <td className="py-2.5 px-3 text-right tabular-nums font-bold">
                          {currencySymbol}{sub.toFixed(2)}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>

            {/* Totals */}
            <div className="flex justify-end text-xs">
              <div className="w-56 space-y-1 tabular-nums">
                <div className="flex justify-between text-stone-500">
                  <span>Subtotal:</span>
                  <span>{currencySymbol}{parseFloat(invoiceOrder.subtotal || 0).toFixed(2)}</span>
                </div>
                {parseFloat(invoiceOrder.discount_amount || 0) > 0 && (
                  <div className="flex justify-between text-emerald-700">
                    <span>Discount:</span>
                    <span>-{currencySymbol}{parseFloat(invoiceOrder.discount_amount).toFixed(2)}</span>
                  </div>
                )}
                <div className="flex justify-between text-stone-500">
                  <span>Shipping:</span>
                  <span>
                    {parseFloat(invoiceOrder.shipping_fee || 0) === 0
                      ? 'FREE'
                      : `${currencySymbol}${parseFloat(invoiceOrder.shipping_fee || 0).toFixed(2)}`}
                  </span>
                </div>
                {parseFloat(invoiceOrder.insulation_packaging_fee || 0) > 0 && (
                  <div className="flex justify-between text-stone-500">
                    <span>Thermal:</span>
                    <span>{currencySymbol}{parseFloat(invoiceOrder.insulation_packaging_fee).toFixed(2)}</span>
                  </div>
                )}
                <div className="flex justify-between text-stone-500">
                  <span>Tax:</span>
                  <span>{currencySymbol}{parseFloat(invoiceOrder.tax_amount || 0).toFixed(2)}</span>
                </div>
                <div className="flex justify-between pt-1.5 border-t border-stone-200 font-bold text-stone-900 text-sm">
                  <span>Total Paid:</span>
                  <span className="font-serif">
                    {currencySymbol}{parseFloat(invoiceOrder.total_amount || 0).toFixed(2)}
                  </span>
                </div>
              </div>
            </div>

            {/* Guarantee */}
            <div className="p-3 rounded-xl bg-botanical-50 border border-botanical-200/60 text-[11px] text-botanical-900 flex items-center gap-2">
              <ShieldCheck className="w-4 h-4 text-botanical-700 shrink-0" />
              <span>100% Live Arrival & Acclimation Guarantee on all shipments.</span>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
