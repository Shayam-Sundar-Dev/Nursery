import React, { useState, useEffect } from 'react';
import api from '../api/client';
import { useStore } from '../context/StoreContext';
import HeroSlider from './HeroSlider';
import CategoryStrip from './CategoryStrip';
import ProductCard from './ProductCard';
import {
  Sparkles,
  ShieldCheck,
  Truck,
  Droplets,
  ArrowRight,
  Heart,
  Leaf,
  CheckCircle2,
  Clock,
  Award,
  ThermometerSnowflake,
  Compass,
  Sprout,
} from 'lucide-react';

export default function HomeView() {
  const { navigateTo, setSelectedCategory, setSearchQuery } = useStore();
  const [featuredProducts, setFeaturedProducts] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadFeatured() {
      try {
        setLoading(true);
        const res = await api.getProducts({ limit: 8 });
        if (res.success && Array.isArray(res.data)) {
          setFeaturedProducts(res.data.slice(0, 8));
        }
      } catch (err) {
        console.error('Failed to load featured products:', err);
      } finally {
        setLoading(false);
      }
    }

    loadFeatured();
  }, []);

  return (
    <div className="space-y-16 sm:space-y-24 pb-20">
      {/* 1. Dynamic Hero Carousel Slider */}
      <HeroSlider />

      {/* 2. Botanical Category Strip */}
      <CategoryStrip />

      {/* 3. Featured Botanical Plants Grid */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col sm:flex-row sm:items-end justify-between mb-8 sm:mb-12 gap-4">
          <div>
            <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-botanical-100/80 border border-botanical-300/40 text-botanical-800 text-[11px] font-bold uppercase tracking-wider mb-2">
              <Sparkles className="w-3.5 h-3.5 text-amber-500" />
              <span>Greenhouse Favorites</span>
            </div>
            <h2 className="font-serif text-2xl sm:text-4xl font-bold text-stone-900 tracking-tight text-balance">
              Featured Botanical Specimens
            </h2>
            <p className="text-xs sm:text-sm text-stone-500 mt-1.5 max-w-xl leading-relaxed text-pretty">
              Acclimatized indoor species inspected by horticulturists, prepared with specialized climate packaging, and dispatched directly from our nursery.
            </p>
          </div>

          <button
            onClick={() => {
              setSelectedCategory(null);
              setSearchQuery('');
              navigateTo('catalog', { category: null, search: '' });
            }}
            className="group inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white border border-stone-200/90 hover:border-botanical-500 text-xs font-bold text-stone-800 hover:text-botanical-800 shadow-2xs hover:shadow-card transition-all duration-300 self-start sm:self-auto cursor-pointer"
          >
            <span>Browse Full Collection</span>
            <span className="w-6 h-6 rounded-full bg-stone-100 group-hover:bg-botanical-100 flex items-center justify-center transition-colors">
              <ArrowRight className="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
            </span>
          </button>
        </div>

        {loading ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            {[...Array(4)].map((_, i) => (
              <div key={i} className="h-96 rounded-3xl bg-sand-200/60 animate-pulse" />
            ))}
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 sm:gap-8">
            {featuredProducts.map((p) => (
              <ProductCard key={p.id} product={p} />
            ))}
          </div>
        )}
      </section>

      {/* 4. Nursery Science & Assurance — Asymmetrical Bento Grid */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-2xl mx-auto mb-12 sm:mb-16">
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-botanical-100/70 text-botanical-800 text-[11px] font-bold uppercase tracking-wider mb-2">
            <Sprout className="w-3 h-3 text-botanical-600" />
            <span>Nursery Standards</span>
          </div>
          <h2 className="font-serif text-3xl sm:text-4xl font-bold text-stone-900 tracking-tight text-balance">
            Engineered for Root Health & Safe Transit
          </h2>
          <p className="text-xs sm:text-sm text-stone-500 mt-2 leading-relaxed text-pretty">
            Unlike mass warehouse shipping, every specimen is treated as a delicate living organism with tailored moisture, insulation, and post-arrival care.
          </p>
        </div>

        {/* Bento Grid */}
        <div className="grid grid-cols-1 md:grid-cols-12 gap-6">
          
          {/* Bento Card 1 (7 cols) - Climate Pods */}
          <div className="md:col-span-7 bg-white rounded-[2rem] p-8 sm:p-10 border border-stone-200/80 shadow-card flex flex-col justify-between relative overflow-hidden group hover:border-botanical-400/50 transition-all duration-300">
            <div className="space-y-4 max-w-lg z-10">
              <span className="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-widest text-botanical-700 bg-botanical-50 px-3 py-1 rounded-full">
                <ThermometerSnowflake className="w-3 h-3" />
                <span>Thermal Transit Technology</span>
              </span>
              <h3 className="font-serif text-2xl sm:text-3xl font-bold text-stone-900 leading-tight">
                72-Hour Climate-Protected Pods
              </h3>
              <p className="text-xs sm:text-sm text-stone-500 leading-relaxed">
                Dispatched in moisture-sealed corrugated pods with thermal heat wraps and organic straw cushioning. Living root balls maintain optimal humidity and temperature even during transit weather extremes.
              </p>
            </div>

            <div className="mt-8 pt-6 border-t border-stone-100 flex flex-wrap items-center justify-between gap-4 z-10">
              <div className="flex items-center gap-3">
                <span className="text-2xl font-serif font-bold text-botanical-800 tabular-nums">99.4%</span>
                <span className="text-xs text-stone-500 max-w-[120px] leading-tight font-medium">Arrival vitality success rate</span>
              </div>
              <span className="inline-flex items-center gap-1 text-xs font-semibold text-botanical-700">
                <span>Inspected Before Dispatch</span>
                <CheckCircle2 className="w-4 h-4 text-emerald-600" />
              </span>
            </div>

            <div className="absolute -right-8 -bottom-8 w-44 h-44 rounded-full bg-botanical-50/60 pointer-events-none group-hover:scale-110 transition-transform duration-500" />
          </div>

          {/* Bento Card 2 (5 cols) - Root Guarantee */}
          <div className="md:col-span-5 bg-botanical-900 text-white rounded-[2rem] p-8 sm:p-10 border border-botanical-800 shadow-card flex flex-col justify-between relative overflow-hidden group">
            <div className="space-y-4 z-10">
              <span className="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-widest text-emerald-300 bg-white/10 px-3 py-1 rounded-full border border-white/10">
                <ShieldCheck className="w-3 h-3 text-emerald-400" />
                <span>Greenhouse Warranty</span>
              </span>
              <h3 className="font-serif text-2xl sm:text-3xl font-bold leading-tight">
                30-Day Root Vitality Guarantee
              </h3>
              <p className="text-xs sm:text-sm text-botanical-200/90 leading-relaxed">
                If your plant shows signs of transit shock or root failure within 30 days of arrival, our botanists will diagnose it or dispatch a replacement at zero charge.
              </p>
            </div>

            <div className="mt-8 pt-6 border-t border-white/10 flex items-center justify-between z-10">
              <span className="text-xs font-bold text-emerald-300">100% Living Guarantee</span>
              <Award className="w-7 h-7 text-amber-400" />
            </div>

            <Leaf className="w-56 h-56 absolute -right-10 -bottom-10 text-white/[0.04] pointer-events-none" />
          </div>

          {/* Bento Card 3 (5 cols) - Substrate Science */}
          <div className="md:col-span-5 bg-sand-100/90 rounded-[2rem] p-8 sm:p-10 border border-sand-300/60 shadow-subtle flex flex-col justify-between">
            <div className="space-y-4">
              <span className="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-widest text-stone-700 bg-sand-200/70 px-3 py-1 rounded-full">
                <Droplets className="w-3 h-3 text-botanical-700" />
                <span>Custom Substrates</span>
              </span>
              <h3 className="font-serif text-2xl font-bold text-stone-900 leading-tight">
                Horticultural Potting Blends
              </h3>
              <p className="text-xs sm:text-sm text-stone-600 leading-relaxed">
                Potted in tailored aroid and tropical mixes using aged pine bark, horticultural perlite, and charcoal for maximum root oxygenation and drainage.
              </p>
            </div>

            <div className="mt-8 pt-6 border-t border-sand-200/80 flex items-center gap-2 text-xs font-semibold text-stone-700">
              <CheckCircle2 className="w-4 h-4 text-botanical-700" />
              <span>Zero dense peat moss or soggy root rot</span>
            </div>
          </div>

          {/* Bento Card 4 (7 cols) - Expert Care Support */}
          <div className="md:col-span-7 bg-white rounded-[2rem] p-8 sm:p-10 border border-stone-200/80 shadow-card flex flex-col justify-between">
            <div className="space-y-4 max-w-lg">
              <span className="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-widest text-botanical-700 bg-botanical-50 px-3 py-1 rounded-full">
                <Compass className="w-3 h-3" />
                <span>Personal Care Mentorship</span>
              </span>
              <h3 className="font-serif text-2xl sm:text-3xl font-bold text-stone-900 leading-tight">
                Lifetime Botanical Guidance
              </h3>
              <p className="text-xs sm:text-sm text-stone-500 leading-relaxed">
                Every plant includes QR-linked hydration guides, light calibration instructions, and direct messaging support from our nursery team whenever leaves yellow or seasonal repotting is required.
              </p>
            </div>

            <div className="mt-8 pt-6 border-t border-stone-100 flex flex-wrap items-center justify-between gap-4">
              <div className="flex items-center gap-2 text-xs font-medium text-stone-500">
                <Clock className="w-4 h-4 text-stone-400" />
                <span>Average botanist response under 2 hours</span>
              </div>
              <button
                onClick={() => navigateTo('quiz')}
                className="text-xs font-bold text-botanical-800 hover:text-botanical-900 inline-flex items-center gap-1 cursor-pointer"
              >
                <span>Find Your Match</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </button>
            </div>
          </div>

        </div>
      </section>

      {/* 5. Plant Matcher Quiz Teaser Banner */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="relative rounded-[2.5rem] overflow-hidden bg-gradient-to-br from-botanical-950 via-botanical-900 to-stone-950 p-8 sm:p-14 lg:p-16 text-white shadow-float border border-botanical-800/80">
          {/* Subtle Ambient Radial Glow */}
          <div className="absolute top-0 right-0 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none" />

          <div className="relative z-10 max-w-2xl space-y-6">
            <span className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-400/20 text-amber-300 text-xs font-bold uppercase tracking-wider border border-amber-400/30">
              <Sparkles className="w-3.5 h-3.5 text-amber-400" />
              <span>Smart Botanical Matcher</span>
            </span>

            <h3 className="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight leading-tight text-balance">
              Find Plants Calibrated to Your Exact Lighting
            </h3>

            <p className="text-stone-300 text-xs sm:text-sm leading-relaxed font-normal max-w-xl text-pretty">
              Answer 4 quick lifestyle questions. Our algorithm maps natural window sunlight, watering rhythm, and pet safety to recommend houseplant varieties guaranteed to flourish.
            </p>

            {/* Equal Sized Paired CTAs */}
            <div className="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4">
              <button
                onClick={() => navigateTo('quiz')}
                className="group h-12 sm:h-13 px-6 rounded-full bg-amber-400 hover:bg-amber-300 text-stone-950 font-bold text-xs uppercase tracking-widest shadow-xl shadow-amber-400/20 transition-all duration-300 active:scale-[0.98] inline-flex items-center justify-center gap-3 cursor-pointer"
              >
                <span>Take 60-Second Matcher</span>
                <ArrowRight className="w-4 h-4 text-stone-950 group-hover:translate-x-0.5 transition-transform" />
              </button>

              <button
                onClick={() => {
                  setSelectedCategory(null);
                  setSearchQuery('');
                  navigateTo('catalog');
                }}
                className="h-12 sm:h-13 px-6 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-md text-white font-bold text-xs uppercase tracking-widest transition border border-white/15 cursor-pointer active:scale-[0.98] inline-flex items-center justify-center"
              >
                Explore All Species
              </button>
            </div>
          </div>

          {/* Decorative Leaf Silhouette */}
          <Leaf className="w-80 h-80 lg:w-96 lg:h-96 absolute -right-16 -bottom-16 text-white/[0.04] pointer-events-none" />
        </div>
      </section>
    </div>
  );
}
