import React, { useState, useEffect } from 'react';
import api from '../api/client';
import { useStore } from '../context/StoreContext';
import { Sprout, Sun, Droplets, Sparkles, Flower2, TreePine, ArrowRight, Leaf, Layers } from 'lucide-react';

const CATEGORY_ICONS = {
  'indoor-plants': Sprout,
  'low-light-plants': Sun,
  'pots-and-planters': Layers,
  'soil-and-nutrition': Droplets,
  'rare-botanicals': Sparkles,
  'low-light-tolerant': Sun,
  'flowering-varieties': Flower2,
  'air-purifying': Leaf,
  'bonsai-trees': TreePine,
};

export default function CategoryStrip() {
  const { selectedCategory, setSelectedCategory, setSearchQuery, navigateTo } = useStore();
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadCategories() {
      try {
        setLoading(true);
        const res = await api.getCategories();
        if (res.success && Array.isArray(res.data)) {
          setCategories(res.data);
        }
      } catch (err) {
        console.error('Failed to load categories:', err);
      } finally {
        setLoading(false);
      }
    }
    loadCategories();
  }, []);

  const handleSelect = (slug) => {
    setSelectedCategory(slug === selectedCategory ? null : slug);
    navigateTo('catalog', { category: slug === selectedCategory ? null : slug });
  };

  return (
    <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-14">
      {/* Section Header */}
      <div className="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6 sm:mb-8">
        <div>
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-botanical-100/70 text-botanical-800 text-[11px] font-bold uppercase tracking-wider mb-2">
            <Leaf className="w-3 h-3 text-botanical-600" />
            <span>Botanical Classifications</span>
          </div>
          <h2 className="font-serif text-2xl sm:text-3xl font-bold text-stone-900 tracking-tight">
            Explore By Botanical Variety
          </h2>
          <p className="text-xs sm:text-sm text-stone-500 mt-1 max-w-xl">
            Acclimatized indoor species arranged by lighting, air-purifying properties, and care complexity.
          </p>
        </div>

        <button
          onClick={() => {
            setSelectedCategory(null);
            setSearchQuery('');
            navigateTo('catalog', { category: null, search: '' });
          }}
          className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-stone-200/80 hover:border-botanical-400 text-xs font-bold text-stone-800 hover:text-botanical-800 shadow-2xs hover:shadow-subtle transition self-start sm:self-auto cursor-pointer"
        >
          <span>View All Flora</span>
          <ArrowRight className="w-3.5 h-3.5" />
        </button>
      </div>

      {/* Categories Grid */}
      {loading ? (
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 sm:gap-4">
          {[...Array(6)].map((_, i) => (
            <div key={i} className="h-28 sm:h-32 rounded-2xl sm:rounded-3xl bg-sand-200/60 animate-pulse" />
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 sm:gap-4">
          {categories.map((cat) => {
            const isSelected = selectedCategory === cat.slug;
            const IconComponent = CATEGORY_ICONS[cat.slug] || Sprout;
            const count = Number(cat.products_count ?? cat.products?.length ?? 0);
            const countLabel = cat.slug === 'pots-and-planters'
              ? `${count} ${count === 1 ? 'Design' : 'Designs'}`
              : cat.slug === 'soil-and-nutrition'
                ? `${count} ${count === 1 ? 'Blend' : 'Blends'}`
                : `${count} ${count === 1 ? 'Variety' : 'Varieties'}`;

            return (
              <button
                key={cat.id}
                onClick={() => handleSelect(cat.slug)}
                className={`flex flex-col items-center text-center p-3.5 sm:p-5 rounded-2xl sm:rounded-3xl border transition-all duration-300 group cursor-pointer ${
                  isSelected
                    ? 'bg-botanical-850 text-white border-botanical-900 shadow-card ring-2 ring-botanical-600/40 transform -translate-y-0.5'
                    : 'bg-white hover:bg-cream-50/80 border-stone-200/80 text-stone-800 shadow-subtle hover:shadow-card hover:-translate-y-0.5'
                }`}
              >
                <div
                  className={`w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center mb-2 sm:mb-3 transition-transform duration-300 group-hover:scale-110 ${
                    isSelected
                      ? 'bg-botanical-700 text-emerald-200 shadow-inner'
                      : 'bg-botanical-50 text-botanical-800 group-hover:bg-botanical-100 group-hover:text-botanical-900'
                  }`}
                >
                  <IconComponent className="w-5 h-5 sm:w-6 sm:h-6" />
                </div>
                <span className="font-serif text-xs sm:text-sm font-bold tracking-tight line-clamp-1">{cat.name}</span>
                <span
                  className={`text-[10px] sm:text-xs mt-0.5 font-medium tabular-nums ${
                    isSelected ? 'text-botanical-200' : 'text-stone-500'
                  }`}
                >
                  {countLabel}
                </span>
              </button>
            );
          })}
        </div>
      )}
    </section>
  );
}
