import React, { useState, useEffect, useRef } from 'react';
import api from '../api/client';
import { useStore } from '../context/StoreContext';
import ProductCard from './ProductCard';
import {
  Search,
  Filter,
  SlidersHorizontal,
  X,
  Sun,
  SunMedium,
  CloudSun,
  Droplets,
  Sparkles,
  ArrowUpDown,
  Leaf,
  ShieldCheck,
  ChevronDown,
  Check,
} from 'lucide-react';

/**
 * Luxury Floating Botanical Dropdown
 * Replaces native browser OS selects with an editorial, double-bezel glassmorphic menu.
 */
function BotanicalDropdown({
  value,
  onChange,
  options,
  icon: Icon,
  label,
  buttonLabel,
  align = 'left',
}) {
  const [open, setOpen] = useState(false);
  const dropdownRef = useRef(null);

  useEffect(() => {
    function handleClickOutside(event) {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setOpen(false);
      }
    }
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const selectedOption = options.find((opt) => opt.value === value) || options[0];
  const DisplayIcon = selectedOption.icon || Icon;

  return (
    <div className={`relative inline-block ${open ? 'z-50' : 'z-20'} shrink-0`} ref={dropdownRef}>
      {/* Trigger Button with Concentric Double-Bezel and Smooth Hover */}
      <button
        type="button"
        onClick={() => setOpen(!open)}
        aria-haspopup="listbox"
        aria-expanded={open}
        className={`group inline-flex items-center gap-1.5 sm:gap-2 h-8 sm:h-9 px-3 sm:px-3.5 rounded-full border text-xs font-semibold transition-all duration-200 cursor-pointer shadow-2xs select-none shrink-0 ${
          open
            ? 'bg-white border-botanical-700 ring-2 ring-botanical-600/20 text-stone-900 shadow-xs'
            : value && value !== 'popular' && value !== ''
              ? 'bg-botanical-50 border-botanical-700 text-botanical-900 font-bold shadow-2xs'
              : 'bg-sand-50/80 hover:bg-white border-stone-200/90 text-stone-700 hover:border-stone-300'
        }`}
      >
        {DisplayIcon && (
          <DisplayIcon
            className={`w-3.5 h-3.5 transition-colors shrink-0 ${
              open || (value && value !== 'popular' && value !== '')
                ? 'text-botanical-700'
                : 'text-stone-500 group-hover:text-botanical-700'
            }`}
          />
        )}
        <span className="truncate max-w-[130px] sm:max-w-none">
          {buttonLabel && <span className="text-stone-400 font-medium mr-1">{buttonLabel}:</span>}
          <span className="text-stone-900 font-bold">{selectedOption.label}</span>
        </span>
        <ChevronDown
          className={`w-3.5 h-3.5 text-stone-400 group-hover:text-stone-700 transition-transform duration-200 shrink-0 ${
            open ? 'rotate-180 text-botanical-700' : ''
          }`}
        />
      </button>

      {/* Floating Popover with Double-Bezel Architecture */}
      {open && (
        <div
          className={`absolute top-full mt-2 z-50 animate-in fade-in zoom-in-95 duration-150 min-w-[220px] sm:min-w-[260px] max-w-[calc(100vw-2rem)] ${
            align === 'right' ? 'right-0' : 'left-0'
          }`}
        >
          {/* Outer double-bezel glassmorphic shell */}
          <div className="p-1.5 rounded-[1.5rem] bg-stone-900/10 backdrop-blur-xl border border-white/60 shadow-float">
            {/* Inner card container */}
            <div className="bg-white/95 rounded-[1.25rem] border border-stone-100 p-1.5 space-y-0.5 shadow-2xs">
              {label && (
                <div className="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-stone-400 border-b border-stone-100 mb-1 flex items-center justify-between">
                  <span>{label}</span>
                  <span className="text-[9px] font-normal text-stone-400">Select option</span>
                </div>
              )}
              {options.map((option) => {
                const isSelected = option.value === value;
                const OptionIcon = option.icon || Icon;

                return (
                  <button
                    key={String(option.value)}
                    type="button"
                    onClick={() => {
                      onChange(option.value);
                      setOpen(false);
                    }}
                    className={`w-full px-3 py-2 rounded-xl text-left text-xs transition-all flex items-center justify-between gap-3 group cursor-pointer ${
                      isSelected
                        ? 'bg-botanical-50/90 text-botanical-900 font-bold shadow-2xs'
                        : 'text-stone-700 hover:bg-sand-50/90 hover:text-stone-900 font-medium'
                    }`}
                  >
                    <div className="flex items-center gap-2.5 min-w-0">
                      {OptionIcon && (
                        <div
                          className={`w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors ${
                            isSelected
                              ? 'bg-botanical-600 text-white shadow-2xs'
                              : 'bg-sand-100/80 text-stone-500 group-hover:bg-sand-200 group-hover:text-stone-800'
                          }`}
                        >
                          <OptionIcon className="w-3.5 h-3.5" />
                        </div>
                      )}
                      <div className="truncate">
                        <div className={`leading-snug ${isSelected ? 'font-bold text-botanical-900' : 'text-stone-800'}`}>
                          {option.label}
                        </div>
                        {option.description && (
                          <div
                            className={`text-[10px] truncate ${
                              isSelected ? 'text-botanical-700/80 font-normal' : 'text-stone-400'
                            }`}
                          >
                            {option.description}
                          </div>
                        )}
                      </div>
                    </div>
                    {isSelected && (
                      <Check className="w-4 h-4 text-botanical-700 shrink-0 ml-1" />
                    )}
                  </button>
                );
              })}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default function CatalogView() {
  const { selectedCategory, setSelectedCategory, searchQuery, setSearchQuery } = useStore();
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);

  // Filters
  const [lightFilter, setLightFilter] = useState('');
  const [petFriendlyOnly, setPetFriendlyOnly] = useState(false);
  const [sortBy, setSortBy] = useState('popular'); // 'popular' | 'price_low' | 'price_high' | 'newest'

  useEffect(() => {
    async function loadCategories() {
      try {
        const res = await api.getCategories();
        if (res.success) setCategories(res.data);
      } catch (err) {
        console.error('Error loading categories:', err);
      }
    }
    loadCategories();
  }, []);

  // Reset local filters whenever "View All" is triggered (both category and search cleared)
  useEffect(() => {
    if (!selectedCategory && !searchQuery) {
      setLightFilter('');
      setPetFriendlyOnly(false);
      setSortBy('popular');
    }
  }, [selectedCategory, searchQuery]);

  useEffect(() => {
    async function loadProducts() {
      try {
        setLoading(true);
        const params = {
          category: selectedCategory || undefined,
          search: searchQuery?.trim() || undefined,
          light: lightFilter || undefined,
          pet_friendly: petFriendlyOnly ? 1 : undefined,
          sort: sortBy || 'popular',
        };

        const res = await api.getProducts(params);
        if (res.success && Array.isArray(res.data)) {
          setProducts(res.data);
        } else {
          setProducts([]);
        }
      } catch (err) {
        console.error('Error loading products:', err);
        setProducts([]);
      } finally {
        setLoading(false);
      }
    }

    loadProducts();
  }, [selectedCategory, searchQuery, lightFilter, petFriendlyOnly, sortBy]);

  const sortOptions = [
    {
      value: 'popular',
      label: 'Most Popular',
      description: 'Handpicked curator favorites',
      icon: Sparkles,
    },
    {
      value: 'price_low',
      label: 'Price: Low to High',
      description: 'Budget-friendly plant additions',
      icon: ArrowUpDown,
    },
    {
      value: 'price_high',
      label: 'Price: High to Low',
      description: 'Rare & mature botanical specimens',
      icon: ArrowUpDown,
    },
    {
      value: 'newest',
      label: 'New Greenhouse Arrivals',
      description: 'Fresh harvest & propagation batches',
      icon: Leaf,
    },
  ];

  const lightOptions = [
    {
      value: '',
      label: 'Any Light Level',
      description: 'Display all living specimens',
      icon: Sparkles,
    },
    {
      value: 'Low Light',
      label: 'Low / Ambient Light',
      description: 'Bedrooms, shaded corners & offices',
      icon: CloudSun,
    },
    {
      value: 'Bright Indirect',
      label: 'Bright Indirect Light',
      description: 'Filtered sunlight near windows',
      icon: SunMedium,
    },
    {
      value: 'Direct Sun',
      label: 'Direct Sunlight',
      description: 'Warm south-facing sills & balconies',
      icon: Sun,
    },
  ];

  const clearAllFilters = () => {
    setSelectedCategory(null);
    setSearchQuery('');
    setLightFilter('');
    setPetFriendlyOnly(false);
    setSortBy('popular');
  };

  const hasActiveFilters = Boolean(
    selectedCategory || searchQuery || lightFilter || petFriendlyOnly || sortBy !== 'popular'
  );

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12 space-y-6 sm:space-y-8">
      {/* Editorial Header Section */}
      <div className="text-center max-w-3xl mx-auto space-y-2 sm:space-y-3">
        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-botanical-100/80 border border-botanical-300/40 text-botanical-800 text-[10px] sm:text-[11px] font-bold uppercase tracking-widest shadow-2xs">
          <Leaf className="w-3 h-3 sm:w-3.5 sm:h-3.5 text-botanical-600" />
          <span>Living Botanical Collection</span>
        </div>
        <h1 className="font-serif text-2xl sm:text-4xl lg:text-5xl font-bold text-stone-900 tracking-tight leading-tight">
          Cultivated Houseplants & Tropical Foliage
        </h1>
        <p className="text-xs sm:text-sm lg:text-base text-stone-500 max-w-2xl mx-auto leading-relaxed text-pretty">
          Each plant is nourished in our climate-regulated nurseries, individually inspected by botanists, and prepared with thermal insulation packaging.
        </p>
      </div>

      {/* Sleek, Compact Filter & Discovery Deck */}
      <div className="relative z-30 rounded-2xl sm:rounded-3xl bg-white/95 backdrop-blur-md border border-stone-200/90 shadow-card p-3 sm:p-4 space-y-2.5 sm:space-y-3">
        {/* Row 1: Category Filter Strip with Momentum Horizontal Scroll */}
        <div className="flex items-center gap-1.5 sm:gap-2 overflow-x-auto no-scrollbar py-0.5 -mx-1 px-1">
          <button
            onClick={() => setSelectedCategory(null)}
            className={`h-8 sm:h-9 px-3.5 sm:px-4 rounded-full text-xs font-bold transition-all duration-200 shrink-0 cursor-pointer ${
              !selectedCategory
                ? 'bg-botanical-800 text-white shadow-xs ring-2 ring-botanical-800/20'
                : 'bg-sand-100/90 hover:bg-sand-200/80 text-stone-700'
            }`}
          >
            All Flora
          </button>
          {categories.map((c) => (
            <button
              key={c.id}
              onClick={() => setSelectedCategory(selectedCategory === c.slug ? null : c.slug)}
              className={`h-8 sm:h-9 px-3.5 sm:px-4 rounded-full text-xs font-bold transition-all duration-200 shrink-0 cursor-pointer ${
                selectedCategory === c.slug
                  ? 'bg-botanical-800 text-white shadow-xs ring-2 ring-botanical-800/20'
                  : 'bg-sand-100/90 hover:bg-sand-200/80 text-stone-700'
              }`}
            >
              {c.name}
            </button>
          ))}
        </div>

        {/* Row 2: Secondary Controls (Sort, Light, Pet-Friendly, Reset, Count) */}
        <div className="pt-2.5 border-t border-stone-100 flex items-center justify-between gap-2">
          {/* Filter Pills with Horizontal Scroll on Mobile */}
          <div className="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5 min-w-0 flex-1">
            {/* Sort Selector */}
            <BotanicalDropdown
              value={sortBy}
              onChange={setSortBy}
              options={sortOptions}
              icon={ArrowUpDown}
              label="Sort Living Botanicals"
              buttonLabel="Sort"
              align="left"
            />

            {/* Lighting Requirement */}
            <BotanicalDropdown
              value={lightFilter}
              onChange={setLightFilter}
              options={lightOptions}
              icon={Sun}
              label="Lighting Requirement"
              buttonLabel="Light"
              align="left"
            />

            {/* Pet Friendly Interactive Toggle Pill */}
            <button
              type="button"
              onClick={() => setPetFriendlyOnly(!petFriendlyOnly)}
              className={`h-8 sm:h-9 px-3 sm:px-3.5 rounded-full text-xs font-semibold transition-all flex items-center gap-1.5 shrink-0 border cursor-pointer select-none ${
                petFriendlyOnly
                  ? 'bg-botanical-800 text-white border-botanical-800 shadow-2xs'
                  : 'bg-sand-50/80 hover:bg-white text-stone-700 border-stone-200/90 hover:border-stone-300'
              }`}
            >
              <span>🐾 Pet Safe</span>
              {petFriendlyOnly && <Check className="w-3 h-3 stroke-[3] text-emerald-300" />}
            </button>

            {/* Reset Filters Action */}
            {hasActiveFilters && (
              <button
                onClick={clearAllFilters}
                className="h-8 sm:h-9 px-3 rounded-full text-xs font-semibold text-rose-600 hover:text-rose-700 bg-rose-50/80 hover:bg-rose-100 border border-rose-200/80 transition flex items-center gap-1 shrink-0 cursor-pointer"
                title="Reset all filters"
              >
                <X className="w-3 h-3" />
                <span>Reset</span>
              </button>
            )}
          </div>

          {/* Specimen Counter */}
          {!loading && (
            <div className="text-[11px] font-bold text-stone-400 uppercase tracking-wider tabular-nums shrink-0 hidden sm:block">
              {products.length} {products.length === 1 ? 'Specimen' : 'Specimens'}
            </div>
          )}
        </div>
      </div>

      {/* Active Filter Tags Bar (Shown when any granular filter is applied) */}
      {!loading && (selectedCategory || searchQuery || lightFilter || petFriendlyOnly) && (
        <div className="flex flex-wrap items-center gap-2 text-xs text-stone-500 px-1 -mt-2">
          <span className="text-[11px] uppercase tracking-wider font-bold text-stone-400">Active:</span>
          {selectedCategory && (
            <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-botanical-100 text-botanical-800 font-semibold text-xs border border-botanical-300/40">
              <span>Category: {categories.find((c) => c.slug === selectedCategory)?.name || selectedCategory}</span>
              <button
                type="button"
                onClick={() => setSelectedCategory(null)}
                className="hover:text-stone-900 font-bold ml-0.5"
                title="Remove category filter"
              >
                ×
              </button>
            </span>
          )}
          {lightFilter && (
            <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-sand-200/90 text-stone-800 font-semibold text-xs border border-sand-300/60">
              <span>Light: {lightFilter}</span>
              <button
                type="button"
                onClick={() => setLightFilter('')}
                className="hover:text-stone-900 font-bold ml-0.5"
                title="Clear lighting filter"
              >
                ×
              </button>
            </span>
          )}
          {petFriendlyOnly && (
            <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold text-xs border border-emerald-200">
              <span>🐾 Pet Friendly</span>
              <button
                type="button"
                onClick={() => setPetFriendlyOnly(false)}
                className="hover:text-stone-900 font-bold ml-0.5"
                title="Clear pet friendly filter"
              >
                ×
              </button>
            </span>
          )}
          {searchQuery && (
            <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-sand-200/90 text-stone-800 font-semibold text-xs border border-sand-300/60">
              <span>"{searchQuery}"</span>
              <button
                type="button"
                onClick={() => setSearchQuery('')}
                className="hover:text-stone-900 font-bold ml-0.5"
                title="Clear search"
              >
                ×
              </button>
            </span>
          )}
        </div>
      )}

      {/* Product Grid / Skeleton / Empty State */}
      {loading ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 sm:gap-7">
          {[...Array(8)].map((_, i) => (
            <div key={i} className="p-2 rounded-[2rem] bg-sand-100/60 border border-stone-200/80 shadow-subtle animate-pulse">
              <div className="bg-white rounded-[1.75rem] p-4 space-y-4">
                <div className="aspect-[4/3.8] bg-sand-200/70 rounded-2xl" />
                <div className="space-y-2">
                  <div className="h-5 bg-sand-200/80 rounded-md w-3/4" />
                  <div className="h-3 bg-sand-200/50 rounded-md w-1/2" />
                  <div className="flex gap-2 pt-2">
                    <div className="h-5 bg-sand-200/60 rounded-md w-16" />
                    <div className="h-5 bg-sand-200/60 rounded-md w-16" />
                  </div>
                </div>
                <div className="pt-3 border-t border-stone-100 flex justify-between items-center">
                  <div className="h-6 bg-sand-200/80 rounded-md w-20" />
                  <div className="h-9 w-9 bg-sand-200/80 rounded-full" />
                </div>
              </div>
            </div>
          ))}
        </div>
      ) : products.length === 0 ? (
        <div className="p-2 rounded-[2rem] bg-sand-100/70 border border-stone-200/80 shadow-subtle max-w-lg mx-auto">
          <div className="text-center py-16 bg-white rounded-[1.75rem] border border-stone-100 p-8 shadow-card">
            <div className="w-16 h-16 rounded-full bg-sand-100 flex items-center justify-center mx-auto mb-4 text-stone-400">
              <Sparkles className="w-8 h-8 text-botanical-600" />
            </div>
            <h3 className="font-serif text-2xl font-bold text-stone-800">No Botanicals Match Your Query</h3>
            <p className="text-xs sm:text-sm text-stone-500 mt-2 max-w-md mx-auto leading-relaxed">
              We couldn't find exact matches with the selected filters. Try loosening lighting criteria or resetting filters to browse our full living collection.
            </p>
            <button
              onClick={clearAllFilters}
              className="mt-6 inline-flex items-center gap-2 px-6 py-3 rounded-full bg-botanical-800 hover:bg-botanical-900 text-white font-bold text-xs shadow-md shadow-botanical-900/10 transition active:scale-95"
            >
              <span>Reset All Filters</span>
            </button>
          </div>
        </div>
      ) : (
        <div className="relative z-0 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 sm:gap-7">
          {products.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      )}
    </div>
  );
}
