import React, { useState, useEffect } from 'react';
import api from '../api/client';
import { useStore } from '../context/StoreContext';
import {
  Truck,
  Search,
  CheckCircle2,
  Clock,
  Package,
  ThermometerSnowflake,
  ShieldCheck,
  AlertCircle,
  MapPin,
  Calendar,
  Sparkles,
  Leaf,
  ArrowRight,
} from 'lucide-react';

export default function OrderTrackingView() {
  const { activeTrackingNumber, currencySymbol, navigateTo } = useStore();
  const [orderNumberInput, setOrderNumberInput] = useState(activeTrackingNumber || '');
  const [trackingData, setTrackingData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const fetchTracking = async (num) => {
    if (!num || !num.trim()) return;
    try {
      setLoading(true);
      setError(null);
      const res = await api.trackOrder(num.trim());
      if (res.success && res.order) {
        setTrackingData(res.order);
      } else {
        setError('Order not found. Please verify your tracking or order number.');
      }
    } catch (err) {
      console.error('Tracking fetch error:', err);
      setError(err.message || 'No live plant transit data found for this tracking code.');
      setTrackingData(null);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (activeTrackingNumber) {
      setOrderNumberInput(activeTrackingNumber);
      fetchTracking(activeTrackingNumber);
    }
  }, [activeTrackingNumber]);

  const handleSubmit = (e) => {
    e.preventDefault();
    fetchTracking(orderNumberInput);
  };

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 py-10 sm:py-16 space-y-10">
      {/* Title */}
      <div className="text-center max-w-xl mx-auto space-y-3">
        <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-botanical-100/80 border border-botanical-300/40 text-botanical-800 text-[11px] font-bold uppercase tracking-wider shadow-2xs">
          <Truck className="w-3.5 h-3.5 text-botanical-700" />
          <span>Climate-Safe Live Transit</span>
        </div>
        <h1 className="font-serif text-3xl sm:text-5xl font-bold text-stone-900 tracking-tight leading-tight text-balance">
          Track Botanical Shipment
        </h1>
        <p className="text-xs sm:text-sm text-stone-500 max-w-md mx-auto leading-relaxed text-pretty">
          Monitor your plant's greenhouse departure, thermal packaging status, and doorstep delivery timeline.
        </p>
      </div>

      {/* Search Bar: Rounded-Full Architecture */}
      <form onSubmit={handleSubmit} className="max-w-md mx-auto flex items-center bg-white rounded-full p-1.5 border border-stone-200/90 shadow-card focus-within:ring-2 focus-within:ring-botanical-500/20 focus-within:border-botanical-600 transition">
        <div className="relative flex-1 flex items-center pl-4">
          <Search className="w-4 h-4 text-stone-400 mr-2.5 flex-shrink-0" />
          <input
            type="text"
            value={orderNumberInput}
            onChange={(e) => setOrderNumberInput(e.target.value)}
            placeholder="Order # (e.g. BOT-20260927-9653)"
            className="w-full bg-transparent text-xs font-mono font-semibold placeholder:font-sans placeholder:normal-case focus:outline-none text-stone-900"
          />
        </div>
        <button
          type="submit"
          disabled={loading || !orderNumberInput.trim()}
          className="px-6 py-2.5 rounded-full bg-botanical-800 hover:bg-botanical-900 disabled:opacity-50 text-white font-bold text-xs uppercase tracking-wider transition active:scale-95 cursor-pointer shadow-xs"
        >
          {loading ? 'Locating...' : 'Track'}
        </button>
      </form>

      {/* Error state */}
      {error && (
        <div className="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-3 max-w-md mx-auto shadow-2xs">
          <AlertCircle className="w-5 h-5 text-rose-500 flex-shrink-0" />
          <span>{error}</span>
        </div>
      )}

      {/* Tracking Result View */}
      {trackingData && (
        <div className="space-y-6 animate-in fade-in duration-300">
          {/* Status Header Card (Double-Bezel) */}
          <div className="p-2 sm:p-2.5 rounded-[2.5rem] bg-stone-900/[0.03] border border-stone-200/80 shadow-card">
            <div className="rounded-[calc(2.5rem-0.5rem)] bg-white p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-5 border border-stone-100">
              <div>
                <div className="flex flex-wrap items-center gap-2.5">
                  <span className="font-mono text-sm sm:text-base font-bold text-stone-900 bg-sand-100 px-3 py-1 rounded-xl">
                    #{trackingData.order_number}
                  </span>
                  <span
                    className={`px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ${
                      trackingData.status === 'delivered'
                        ? 'bg-emerald-100 text-emerald-800'
                        : trackingData.status === 'transit'
                        ? 'bg-indigo-100 text-indigo-800 animate-pulse'
                        : 'bg-amber-100 text-amber-800'
                    }`}
                  >
                    {trackingData.status === 'transit'
                      ? 'In Climate-Controlled Transit'
                      : trackingData.status}
                  </span>
                </div>
                <div className="text-xs text-stone-500 mt-2.5 flex flex-wrap items-center gap-3">
                  <span className="flex items-center gap-1.5">
                    <Calendar className="w-3.5 h-3.5 text-stone-400" />
                    <span>Ordered: {new Date(trackingData.created_at).toLocaleDateString()}</span>
                  </span>
                  <span>&bull;</span>
                  <span className="flex items-center gap-1.5">
                    <Truck className="w-3.5 h-3.5 text-stone-400" />
                    <span>Carrier: {trackingData.shipping_carrier || 'Greenhouse Express Network'}</span>
                  </span>
                </div>
              </div>

              <div className="sm:text-right pt-3 sm:pt-0 border-t sm:border-t-0 border-stone-100 tabular-nums">
                <div className="text-[10px] text-stone-400 uppercase font-bold tracking-wider">Total Paid</div>
                <div className="font-serif text-2xl font-bold text-stone-900 mt-0.5">
                  {currencySymbol}{parseFloat(trackingData.total_amount).toFixed(2)}
                </div>
              </div>
            </div>
          </div>

          {/* Thermal Insulation Banner */}
          {trackingData.thermal_packaging_fee > 0 && (
            <div className="p-4 sm:p-5 rounded-3xl bg-sky-50/80 border border-sky-200/80 text-sky-950 text-xs flex items-center gap-3.5 shadow-2xs">
              <ThermometerSnowflake className="w-6 h-6 text-sky-600 flex-shrink-0" />
              <div>
                <span className="font-bold">Thermal Protection Pod Active:</span> Living roots are cushioned inside moisture-retentive coir wraps with climate shielding.
              </div>
            </div>
          )}

          {/* Timeline Visualizer */}
          <div className="p-6 sm:p-8 rounded-3xl bg-white border border-stone-200/80 shadow-card space-y-6">
            <h3 className="text-xs font-bold uppercase tracking-widest text-stone-400">
              Live Fulfillment Progression
            </h3>

            <div className="relative pl-6 sm:pl-8 space-y-8 before:absolute before:left-2.5 sm:before:left-3.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-sand-200">
              {/* Step 1: Placed */}
              <div className="relative">
                <div className="absolute -left-6 sm:-left-8 w-5 h-5 sm:w-7 sm:h-7 rounded-full bg-botanical-700 text-white flex items-center justify-center ring-4 ring-white shadow-2xs">
                  <CheckCircle2 className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                </div>
                <div className="font-bold text-xs sm:text-sm text-stone-900">Greenhouse Selection & Botanical Allocation</div>
                <div className="text-[11px] text-stone-500 mt-0.5">Healthy specimen harvested from nursery benches for root hydration inspection.</div>
              </div>

              {/* Step 2: Inspection */}
              <div className="relative">
                <div className="absolute -left-6 sm:-left-8 w-5 h-5 sm:w-7 sm:h-7 rounded-full bg-botanical-700 text-white flex items-center justify-center ring-4 ring-white shadow-2xs">
                  <CheckCircle2 className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                </div>
                <div className="font-bold text-xs sm:text-sm text-stone-900">Botanist Quality Audit & Thermal Pod Packaging</div>
                <div className="text-[11px] text-stone-500 mt-0.5">Root ball sealed with moisture protection and secured into corrugated shock pods.</div>
              </div>

              {/* Step 3: Transit */}
              <div className="relative">
                <div className={`absolute -left-6 sm:-left-8 w-5 h-5 sm:w-7 sm:h-7 rounded-full flex items-center justify-center ring-4 ring-white shadow-2xs ${
                  trackingData.status === 'transit' || trackingData.status === 'delivered'
                    ? 'bg-botanical-700 text-white'
                    : 'bg-sand-200 text-stone-400'
                }`}>
                  <Truck className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                </div>
                <div className="font-bold text-xs sm:text-sm text-stone-900">Climate-Safe Courier Transit</div>
                <div className="text-[11px] text-stone-500 mt-0.5">En route to your doorstep via specialized nursery priority delivery network.</div>
              </div>

              {/* Step 4: Delivered */}
              <div className="relative">
                <div className={`absolute -left-6 sm:-left-8 w-5 h-5 sm:w-7 sm:h-7 rounded-full flex items-center justify-center ring-4 ring-white shadow-2xs ${
                  trackingData.status === 'delivered'
                    ? 'bg-emerald-600 text-white'
                    : 'bg-sand-200 text-stone-400'
                }`}>
                  <CheckCircle2 className="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                </div>
                <div className="font-bold text-xs sm:text-sm text-stone-900">Delivered to Botanical Sanctuary</div>
                <div className="text-[11px] text-stone-500 mt-0.5">Unpack within 12 hours, hydrate root base, and settle in bright indirect light.</div>
              </div>
            </div>
          </div>

          {/* Plant Package Items */}
          {Array.isArray(trackingData.items) && trackingData.items.length > 0 && (
            <div className="p-6 sm:p-8 rounded-3xl bg-white border border-stone-200/80 shadow-card space-y-4">
              <h3 className="text-xs font-bold uppercase tracking-widest text-stone-400">
                Package Contents ({trackingData.items.length} living {trackingData.items.length === 1 ? 'specimen' : 'specimens'})
              </h3>
              <div className="divide-y divide-stone-100">
                {trackingData.items.map((it, idx) => (
                  <div key={idx} className="py-3.5 first:pt-0 last:pb-0 flex items-center justify-between text-xs">
                    <div>
                      <div className="font-bold text-stone-900">{it.product_name}</div>
                      <div className="text-[11px] text-stone-500 mt-0.5">{it.variant_title} &bull; Qty: {it.quantity}</div>
                    </div>
                    <div className="font-serif font-bold text-stone-900 text-sm tabular-nums">
                      {currencySymbol}{parseFloat(it.total_price).toFixed(2)}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
