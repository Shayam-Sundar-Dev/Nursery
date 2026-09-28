import React, { useState } from 'react';
import api from '../api/client';
import { useStore } from '../context/StoreContext';
import ProductCard from './ProductCard';
import {
  Sparkles,
  Sun,
  ShieldCheck,
  RotateCcw,
  CheckCircle2,
  ArrowRight,
  Leaf,
  Droplets,
  Heart,
} from 'lucide-react';

const QUESTIONS = [
  {
    id: 'light_requirement',
    title: 'What lighting level does your living space receive?',
    subtitle: 'Evaluate the natural window sunlight in the room where your plant will thrive',
    options: [
      { value: 'low-light', label: 'Low / Ambient Light', desc: 'North-facing rooms, shaded corners, or offices with fluorescent lamps' },
      { value: 'bright-indirect', label: 'Bright Indirect Light', desc: 'Near sunny east/west windows with filtered sheer curtain sun' },
      { value: 'direct-sun', label: 'Direct Sunlight', desc: 'Sunny south-facing sills with 4+ hours of unfiltered warmth' },
    ],
  },
  {
    id: 'is_pet_friendly',
    title: 'Do you have furry companions (cats or dogs) at home?',
    subtitle: 'We filter out any botanical species with irritant saponins or insoluble calcium oxalates',
    options: [
      { value: 'yes', label: 'Yes, 100% Pet-Safe Only', desc: 'Non-toxic plants safe if nibbled by playful curious pets' },
      { value: 'no', label: 'No Pets / High Shelves Available', desc: 'Any botanical variety including exotic philodendrons & monsteras' },
    ],
  },
  {
    id: 'watering_frequency',
    title: 'How often would you like to water your plant companion?',
    subtitle: 'Match your personal watering rhythm so your botanicals never get overwatered',
    options: [
      { value: 'weekly', label: 'Once a Week (Regular Care)', desc: 'Enjoying a dedicated weekend watering ritual' },
      { value: 'biweekly', label: 'Every 2 Weeks (Low Maintenance)', desc: 'Hardy drought-tolerant varieties that forgive busy travel' },
      { value: 'monthly', label: 'Once a Month (Ultra Resilient)', desc: 'Succulents, cacti, and ZZ plants that store water in thick stems' },
    ],
  },
  {
    id: 'experience_level',
    title: 'What is your green-thumb gardening experience level?',
    subtitle: 'We will suggest species tuned to your plant care confidence',
    options: [
      { value: 'beginner', label: 'Beginner / First-Time Gardener', desc: 'Practically unkillable plants that boost your botanical confidence' },
      { value: 'moderate', label: 'Intermediate Houseplant Lover', desc: 'Ready for tropical foliage requiring balanced humidity' },
      { value: 'expert', label: 'Botanical Collector / Rare Enthusiast', desc: 'Exotic rare variegation, fine aroids, and rewarding challenges' },
    ],
  },
];

export default function PlantFinderQuiz() {
  const { navigateTo } = useStore();
  const [step, setStep] = useState(0);
  const [answers, setAnswers] = useState({
    light_requirement: '',
    is_pet_friendly: '',
    watering_frequency: '',
    experience_level: '',
  });
  const [matches, setMatches] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const currentQ = QUESTIONS[step];

  const handleSelectOption = (field, val) => {
    const nextAnswers = { ...answers, [field]: val };
    setAnswers(nextAnswers);

    if (step < QUESTIONS.length - 1) {
      setStep(step + 1);
    } else {
      submitQuiz(nextAnswers);
    }
  };

  const submitQuiz = async (finalAnswers) => {
    try {
      setLoading(true);
      setError(null);

      // Map frontend answer values → backend enum values
      const lightMap = {
        'low-light': 'low',
        'bright-indirect': 'bright_indirect',
        'direct-sun': 'direct_sun',
      };
      const careMap = {
        'beginner': 'forgetful',
        'moderate': 'moderate',
        'expert': 'attentive',
      };

      const payload = {
        light_level: lightMap[finalAnswers.light_requirement] || 'bright_indirect',
        has_pets: finalAnswers.is_pet_friendly === 'yes',
        care_routine: careMap[finalAnswers.experience_level] || 'moderate',
      };

      const res = await api.plantFinderQuiz(payload);
      const results = res.recommendations || res.data || [];
      if (Array.isArray(results)) {
        setMatches(results);
        setTimeout(() => {
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }, 50);
      } else {
        setMatches([]);
      }
    } catch (err) {
      console.error('Quiz submission error:', err);
      setError(err.message || 'Could not calculate botanical matches. Please check your connection and retry.');
    } finally {
      setLoading(false);
    }
  };

  const resetQuiz = () => {
    setStep(0);
    setAnswers({
      light_requirement: '',
      is_pet_friendly: '',
      watering_frequency: '',
      experience_level: '',
    });
    setMatches(null);
    setError(null);
  };

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
      {/* Header */}
      <div className="text-center max-w-2xl mx-auto mb-10">
        <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-botanical-100 text-botanical-800 text-xs font-bold uppercase tracking-wider mb-3">
          <Sparkles className="w-3.5 h-3.5 text-amber-500" />
          <span>Botanical Matcher Algorithm</span>
        </div>
        <h1 className="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold text-stone-900 tracking-tight text-balance">
          Find your perfect plant companion
        </h1>
        <p className="text-sm text-stone-600 mt-3 leading-relaxed text-pretty">
          Answer 4 quick lifestyle questions to discover living indoor botanicals guaranteed to flourish in your specific space.
        </p>
      </div>

      {/* Error Banner */}
      {error && (
        <div className="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs flex items-center justify-between gap-3">
          <span>{error}</span>
          <button
            onClick={() => submitQuiz(answers)}
            className="px-3 py-1.5 rounded-lg bg-red-800 text-white font-semibold hover:bg-red-900 transition flex-shrink-0"
          >
            Retry
          </button>
        </div>
      )}

      {/* Quiz Card */}
      {!matches && (
        <div className="bg-white rounded-3xl p-6 sm:p-10 border border-stone-200/80 shadow-card relative overflow-hidden">
          {/* Progress Bar */}
          <div className="w-full bg-sand-100 h-2 rounded-full mb-8 overflow-hidden">
            <div
              className="bg-botanical-700 h-full rounded-full transition-all duration-500 ease-out"
              style={{ width: `${((step + 1) / QUESTIONS.length) * 100}%` }}
            />
          </div>

          <div className="flex items-center justify-between text-xs font-bold text-stone-400 uppercase tracking-wider mb-3 tabular-nums">
            <span>Question {step + 1} of {QUESTIONS.length}</span>
            <span>{Math.round(((step + 1) / QUESTIONS.length) * 100)}% complete</span>
          </div>

          <h2 className="font-serif text-2xl sm:text-3xl font-bold text-stone-900 mb-2 text-balance">
            {currentQ.title}
          </h2>
          <p className="text-xs sm:text-sm text-stone-500 mb-8 leading-relaxed text-pretty">
            {currentQ.subtitle}
          </p>

          {/* Options */}
          <div className="space-y-3.5">
            {currentQ.options.map((opt) => {
              const isSelected = answers[currentQ.id] === opt.value;
              return (
                <button
                  key={opt.value}
                  onClick={() => handleSelectOption(currentQ.id, opt.value)}
                  disabled={loading}
                  className={`w-full p-5 rounded-2xl border-2 text-left transition-all duration-200 group flex items-start justify-between cursor-pointer ${
                    isSelected
                      ? 'border-botanical-700 bg-botanical-50/70 shadow-xs'
                      : 'border-stone-200/90 hover:border-botanical-500/80 hover:bg-sand-50/60'
                  }`}
                >
                  <div className="pr-4">
                    <div className={`font-bold text-sm transition ${
                      isSelected ? 'text-botanical-900' : 'text-stone-900 group-hover:text-botanical-800'
                    }`}>
                      {opt.label}
                    </div>
                    <div className="text-xs text-stone-500 mt-1 leading-relaxed">
                      {opt.desc}
                    </div>
                  </div>
                  <div className={`w-6 h-6 rounded-full border-2 flex items-center justify-center transition flex-shrink-0 mt-0.5 ${
                    isSelected
                      ? 'border-botanical-700 bg-botanical-700 text-white'
                      : 'border-stone-300 group-hover:border-botanical-500 group-hover:bg-botanical-50'
                  }`}>
                    <CheckCircle2 className={`w-4 h-4 text-white transition ${
                      isSelected ? 'opacity-100' : 'opacity-0 group-hover:opacity-60 text-botanical-700'
                    }`} />
                  </div>
                </button>
              );
            })}
          </div>

          {step > 0 && (
            <button
              onClick={() => setStep(step - 1)}
              className="mt-6 text-xs font-semibold text-stone-500 hover:text-stone-800 transition inline-flex items-center gap-1"
            >
              <span>&larr; Back to previous question</span>
            </button>
          )}

          {loading && (
            <div className="absolute inset-0 bg-white/90 backdrop-blur-xs flex flex-col items-center justify-center z-20">
              <div className="w-10 h-10 border-3 border-botanical-200 border-t-botanical-700 rounded-full animate-spin mb-3" />
              <p className="text-xs font-bold text-botanical-800">Calculating greenhouse recommendations...</p>
            </div>
          )}
        </div>
      )}

      {/* Results View */}
      {matches && (
        <div className="space-y-8 animate-in fade-in duration-500">
          <div className="p-6 sm:p-8 rounded-3xl bg-botanical-900 text-white flex flex-col sm:flex-row items-center justify-between gap-5 shadow-card">
            <div className="flex items-center gap-4">
              <div className="w-14 h-14 rounded-2xl bg-botanical-800 text-emerald-300 flex items-center justify-center flex-shrink-0 shadow-inner">
                <Sparkles className="w-7 h-7" />
              </div>
              <div>
                <h3 className="font-serif text-xl sm:text-2xl font-bold">
                  We found {matches.length} recommended botanical match{matches.length === 1 ? '' : 'es'}
                </h3>
                <p className="text-xs sm:text-sm text-botanical-200 mt-1 max-w-xl">
                  Curated for your space's sunlight levels, routine watering rhythm, and pet safety criteria.
                </p>
              </div>
            </div>

            <button
              onClick={resetQuiz}
              className="px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-xs font-bold transition flex items-center gap-2 flex-shrink-0 cursor-pointer"
            >
              <RotateCcw className="w-3.5 h-3.5" />
              <span>Retake quiz</span>
            </button>
          </div>

          {matches.length === 0 ? (
            <div className="p-12 text-center bg-white rounded-3xl border border-stone-200/90 shadow-card">
              <Leaf className="w-12 h-12 text-botanical-600/50 mx-auto mb-3" />
              <h4 className="font-serif text-xl font-bold text-stone-800">No exact matches for this criteria</h4>
              <p className="text-xs sm:text-sm text-stone-500 mt-1 max-w-md mx-auto leading-relaxed">
                Consider loosening pet restrictions or checking our complete nursery collection for resilient, forgiving species.
              </p>
              <button
                onClick={() => navigateTo('catalog')}
                className="mt-6 px-6 py-3 rounded-2xl bg-botanical-800 hover:bg-botanical-900 text-white font-bold text-xs shadow-md transition cursor-pointer"
              >
                Browse all plants
              </button>
            </div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
              {matches.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>
          )}
        </div>
      )}
    </div>
  );
}
