import React, { useState, useEffect, useRef } from 'react';
import api from '../api/client';
import { useStore } from '../context/StoreContext';
import { ChevronLeft, ChevronRight, Sparkles, ArrowRight, ShieldCheck, ThermometerSnowflake, Sprout } from 'lucide-react';

const FALLBACK_SLIDERS = [
  {
    id: 'f1',
    badge_text: 'Living Botanical Guarantee',
    title: 'Exotic Living Botanicals, Directly From Greenhouse to Doorstep',
    subtitle: 'Nourished under precision climate conditions and dispatched in insulated 72-hour thermal pods with real-time transit telemetry.',
    button_text: 'Explore Spring Flora',
    link_url: '/catalog',
    image_url: 'https://images.unsplash.com/photo-1463936575829-25148e1db1b8?auto=format&fit=crop&w=1800&q=80',
    overlay_opacity: 0.45,
    text_color: '#ffffff',
  },
  {
    id: 'f2',
    badge_text: 'Intelligent Space Matching',
    title: 'Discover Plants Calibrated to Your Sunlight & Lifestyle',
    subtitle: 'Take our 60-second Botanical Matcher Quiz to uncover low-light, pet-friendly tropicals guaranteed to thrive in your exact space.',
    button_text: 'Take Plant Matcher',
    link_url: '/quiz',
    image_url: 'https://images.unsplash.com/photo-1545241047-6083a3684587?auto=format&fit=crop&w=1800&q=80',
    overlay_opacity: 0.50,
    text_color: '#ffffff',
  },
];

export default function HeroSlider() {
  const { navigateTo } = useStore();
  const [sliders, setSliders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [isHovered, setIsHovered] = useState(false);
  const [progress, setProgress] = useState(0);
  const timerRef = useRef(null);
  const progressIntervalRef = useRef(null);
  const touchStartX = useRef(null);
  const touchEndX = useRef(null);

  const SLIDE_DURATION = 7000; // 7 seconds

  useEffect(() => {
    async function loadSliders() {
      try {
        setLoading(true);
        const res = await api.getSliders();
        if (res.success && Array.isArray(res.data) && res.data.length > 0) {
          setSliders(res.data);
        } else {
          setSliders(FALLBACK_SLIDERS);
        }
      } catch (err) {
        console.warn('Failed to load sliders, using fallbacks:', err);
        setSliders(FALLBACK_SLIDERS);
      } finally {
        setLoading(false);
      }
    }
    loadSliders();
  }, []);

  // Slide progression timer
  useEffect(() => {
    if (sliders.length <= 1 || isHovered) return;

    setProgress(0);
    const stepTime = 50;
    const increment = (stepTime / SLIDE_DURATION) * 100;

    progressIntervalRef.current = setInterval(() => {
      setProgress((prev) => {
        if (prev >= 100) {
          setCurrentIndex((curr) => (curr + 1) % sliders.length);
          return 0;
        }
        return prev + increment;
      });
    }, stepTime);

    return () => clearInterval(progressIntervalRef.current);
  }, [sliders, currentIndex, isHovered]);

  const goToSlide = (index) => {
    setCurrentIndex(index);
    setProgress(0);
  };

  const prevSlide = () => {
    setCurrentIndex((prev) => (prev - 1 + sliders.length) % sliders.length);
    setProgress(0);
  };

  const nextSlide = () => {
    setCurrentIndex((prev) => (prev + 1) % sliders.length);
    setProgress(0);
  };

  const handleTouchStart = (e) => {
    touchStartX.current = e.targetTouches[0].clientX;
  };

  const handleTouchMove = (e) => {
    touchEndX.current = e.targetTouches[0].clientX;
  };

  const handleTouchEnd = () => {
    if (!touchStartX.current || !touchEndX.current) return;
    const distance = touchStartX.current - touchEndX.current;
    if (distance > 50) {
      nextSlide();
    } else if (distance < -50) {
      prevSlide();
    }
    touchStartX.current = null;
    touchEndX.current = null;
  };

  if (loading && sliders.length === 0) {
    return (
      <div className="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 my-4 sm:my-6">
        <div className="w-full h-[460px] sm:h-[580px] bg-sand-200/60 rounded-[2.25rem] sm:rounded-[2.5rem] animate-pulse" />
      </div>
    );
  }

  const active = sliders[currentIndex] || FALLBACK_SLIDERS[0];

  const handleCtaClick = () => {
    const link = active.link_url || '/catalog';
    if (link.includes('quiz')) {
      navigateTo('quiz');
    } else {
      navigateTo('catalog');
    }
  };

  return (
    <div
      className="relative max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 my-3 sm:my-6"
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
      onTouchStart={handleTouchStart}
      onTouchMove={handleTouchMove}
      onTouchEnd={handleTouchEnd}
    >
      {/* Outer Shell (Double-Bezel Architecture) */}
      <div className="relative p-1.5 sm:p-2.5 rounded-[2.25rem] sm:rounded-[2.5rem] bg-stone-900/[0.04] border border-stone-200/80 shadow-float">
        {/* Inner Core */}
        <div className="relative min-h-[460px] sm:min-h-[540px] lg:min-h-[600px] rounded-[calc(2.25rem-0.375rem)] sm:rounded-[calc(2.5rem-0.625rem)] overflow-hidden bg-stone-950 flex flex-col justify-between">
          
          {/* Background Images with Crossfade */}
          {sliders.map((slide, idx) => (
            <div
              key={slide.id || idx}
              className={`absolute inset-0 bg-cover bg-center transition-all duration-1000 ease-[cubic-bezier(0.32,0.72,0,1)] ${
                idx === currentIndex
                  ? 'opacity-100 scale-100'
                  : 'opacity-0 scale-105 pointer-events-none'
              }`}
              style={{
                backgroundImage: `url(${slide.image_url})`,
              }}
            />
          ))}

          {/* Responsive Multi-Layer Botanical Scrim: translucent on mobile to showcase the plant, directional on desktop */}
          <div className="absolute inset-0 bg-gradient-to-t from-stone-950/95 via-stone-950/50 to-stone-950/20 sm:bg-gradient-to-r sm:from-stone-950/95 sm:via-stone-950/65 sm:to-stone-950/15 z-[1] pointer-events-none" />

          {/* Top Status Bar Inside Hero */}
          <div className="relative z-10 p-4 sm:p-8 lg:p-10 flex items-center justify-between">
            <div className="inline-flex items-center gap-1.5 px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-emerald-300 text-[10px] sm:text-[11px] font-bold uppercase tracking-widest shadow-2xs">
              <Sparkles className="w-3 h-3 sm:w-3.5 sm:h-3.5 text-amber-400" />
              <span>{active.badge_text || 'Greenhouse Verified'}</span>
            </div>

            {/* Slide Index Pill */}
            <div className="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full bg-stone-900/60 backdrop-blur-md border border-white/10 text-stone-300 text-[11px] sm:text-xs font-mono tabular-nums">
              <span className="text-white font-bold">{String(currentIndex + 1).padStart(2, '0')}</span>
              <span className="text-stone-500">/</span>
              <span>{String(sliders.length).padStart(2, '0')}</span>
            </div>
          </div>

          {/* Center Main Editorial Copy */}
          <div className="relative z-10 px-4 sm:px-10 lg:px-16 max-w-3xl my-auto py-4 sm:py-6">
            <h1
              className="font-serif text-2xl sm:text-4xl lg:text-6xl font-bold tracking-tight text-white mb-2.5 sm:mb-4 leading-[1.16] sm:leading-[1.12] text-balance drop-shadow-md"
              style={{ color: active.text_color || '#ffffff' }}
            >
              {active.title}
            </h1>

            <p className="text-xs sm:text-base lg:text-lg text-stone-200/90 leading-relaxed mb-5 sm:mb-8 max-w-2xl font-normal drop-shadow-xs text-pretty line-clamp-3 sm:line-clamp-none">
              {active.subtitle}
            </p>

            {/* Actions: Equal Sized Paired Buttons */}
            <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4 w-full sm:w-auto">
              <button
                onClick={handleCtaClick}
                className="group h-12 sm:h-13 px-6 rounded-full bg-botanical-700 hover:bg-botanical-800 text-white font-bold text-xs uppercase tracking-widest shadow-xl shadow-botanical-950/40 transition-all duration-300 active:scale-[0.98] inline-flex items-center justify-center gap-3 cursor-pointer"
              >
                <span>{active.button_text || 'Shop Catalog'}</span>
                <ArrowRight className="w-4 h-4 text-emerald-200 group-hover:translate-x-0.5 transition-transform" />
              </button>

              <button
                onClick={() => navigateTo('quiz')}
                className="h-12 sm:h-13 px-6 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white font-bold text-xs uppercase tracking-widest transition active:scale-[0.98] cursor-pointer inline-flex items-center justify-center text-center"
              >
                Take Matcher Quiz
              </button>
            </div>
          </div>

          {/* Bottom Control Bar & Live Progress Indicators */}
          <div className="relative z-10 p-3.5 sm:p-8 lg:p-10 flex items-center justify-between gap-3 sm:gap-6 border-t border-white/10 bg-stone-950/50 backdrop-blur-md">
            
            {/* Live Progress Bar for Each Slide */}
            <div className="flex items-center gap-2 sm:gap-3 flex-1 max-w-xs sm:max-w-md">
              {sliders.map((slide, idx) => (
                <button
                  key={idx}
                  onClick={() => goToSlide(idx)}
                  className="flex-1 group text-left cursor-pointer focus:outline-none"
                  aria-label={`Slide ${idx + 1}`}
                >
                  <div className="w-full bg-white/20 h-1 sm:h-1.5 rounded-full overflow-hidden mb-1 sm:mb-1.5 transition-all group-hover:bg-white/30">
                    <div
                      className={`h-full rounded-full transition-all duration-100 ${
                        idx === currentIndex
                          ? 'bg-emerald-400'
                          : idx < currentIndex
                            ? 'bg-white/80 w-full'
                            : 'w-0'
                      }`}
                      style={{
                        width: idx === currentIndex ? `${progress}%` : undefined,
                      }}
                    />
                  </div>
                  <span className={`hidden sm:block text-[10px] font-bold tracking-wider truncate uppercase transition-colors ${
                    idx === currentIndex ? 'text-white' : 'text-stone-400 group-hover:text-stone-300'
                  }`}>
                    0{idx + 1} &bull; {slide.badge_text || 'Featured'}
                  </span>
                </button>
              ))}
            </div>

            {/* Quick Micro-Trust Badges (Desktop) */}
            <div className="hidden lg:flex items-center gap-5 text-xs text-stone-300">
              <span className="flex items-center gap-1.5">
                <ThermometerSnowflake className="w-4 h-4 text-emerald-400" />
                <span>72h Thermal Insulation</span>
              </span>
              <span className="w-1 h-1 rounded-full bg-stone-600" />
              <span className="flex items-center gap-1.5">
                <ShieldCheck className="w-4 h-4 text-amber-400" />
                <span>30-Day Root Warranty</span>
              </span>
            </div>

            {/* Previous / Next Arrow Controls */}
            {sliders.length > 1 && (
              <div className="flex items-center gap-1.5 sm:gap-2 shrink-0">
                <button
                  onClick={prevSlide}
                  aria-label="Previous slide"
                  className="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/10 hover:bg-white/25 text-white backdrop-blur-md border border-white/20 flex items-center justify-center transition active:scale-95 cursor-pointer"
                >
                  <ChevronLeft className="w-4 h-4" />
                </button>
                <button
                  onClick={nextSlide}
                  aria-label="Next slide"
                  className="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white/10 hover:bg-white/25 text-white backdrop-blur-md border border-white/20 flex items-center justify-center transition active:scale-95 cursor-pointer"
                >
                  <ChevronRight className="w-4 h-4" />
                </button>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
