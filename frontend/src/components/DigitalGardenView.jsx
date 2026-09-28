import React, { useState, useEffect } from 'react';
import api from '../api/client';
import { useStore } from '../context/StoreContext';
import {
  Leaf,
  Droplets,
  Plus,
  Calendar,
  AlertTriangle,
  CheckCircle2,
  Sparkles,
  Heart,
  Clock,
  X,
} from 'lucide-react';

export default function DigitalGardenView() {
  const { addToast, navigateTo } = useStore();
  const [plants, setPlants] = useState([]);
  const [loading, setLoading] = useState(true);
  const [adoptModalOpen, setAdoptModalOpen] = useState(false);
  const [wateringId, setWateringId] = useState(null);

  const [newPlant, setNewPlant] = useState({
    nickname: 'Monty',
    product_name: 'Monstera Deliciosa (Swiss Cheese)',
    reminder_frequency_days: 7,
    notes: 'Likes misting twice a week and bright indirect sunlight.',
  });

  const loadPlants = async () => {
    try {
      setLoading(true);
      const res = await api.getMyPlants();
      if (res.success && Array.isArray(res.data)) {
        setPlants(res.data);
      }
    } catch (err) {
      console.error('Failed to load my-plants:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadPlants();
  }, []);

  const handleWater = async (id, name) => {
    try {
      setWateringId(id);
      const res = await api.waterMyPlant(id);
      if (res.success) {
        addToast(`Watered "${name}"! Next hydration schedule logged.`, 'success');
        loadPlants();
      }
    } catch (err) {
      console.error('Watering error:', err);
      addToast('Failed to log watering', 'error');
    } finally {
      setWateringId(null);
    }
  };

  const handleAdoptSubmit = async (e) => {
    e.preventDefault();
    try {
      const res = await api.addMyPlant(newPlant);
      if (res.success) {
        addToast(`Adopted "${newPlant.nickname}" into your digital garden!`, 'success');
        setAdoptModalOpen(false);
        setNewPlant({
          nickname: '',
          product_name: '',
          reminder_frequency_days: 7,
          notes: '',
        });
        loadPlants();
      }
    } catch (err) {
      console.error('Adopt plant error:', err);
      addToast(err.message || 'Failed to adopt plant companion', 'error');
    }
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10">
        <div>
          <div className="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-botanical-100 text-botanical-800 text-[11px] font-bold uppercase tracking-wider mb-2 border border-botanical-300/40 shadow-2xs">
            <Leaf className="w-3.5 h-3.5 text-botanical-600" />
            <span>Botanical Care Companion</span>
          </div>
          <h1 className="font-serif text-3xl sm:text-4xl font-bold text-stone-900 tracking-tight">
            My Adopted Garden Companions
          </h1>
          <p className="text-xs sm:text-sm text-stone-500 mt-1 max-w-xl leading-relaxed">
            Track hydration cycles, soil moisture logs, and care schedules for your home houseplants.
          </p>
        </div>

        <button
          onClick={() => setAdoptModalOpen(true)}
          className="relative group inline-flex items-center justify-between p-1.5 pl-5 pr-1.5 rounded-full bg-botanical-800 hover:bg-botanical-900 active:scale-95 text-white font-bold text-xs shadow-md shadow-botanical-900/10 transition w-fit"
        >
          <span className="mr-3">Adopt New Companion</span>
          <span className="w-8 h-8 rounded-full bg-white/10 group-hover:bg-white/20 flex items-center justify-center transition">
            <Plus className="w-4 h-4 text-white" />
          </span>
        </button>
      </div>

      {/* Plants Grid */}
      {loading ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-7">
          {[...Array(3)].map((_, i) => (
            <div key={i} className="p-2 rounded-[2rem] bg-sand-100/60 border border-stone-200/80 shadow-subtle animate-pulse">
              <div className="bg-white rounded-[1.75rem] p-6 h-64 space-y-4">
                <div className="h-4 bg-sand-200/80 rounded w-1/3" />
                <div className="h-6 bg-sand-200/80 rounded w-2/3" />
                <div className="h-16 bg-sand-200/50 rounded-xl" />
              </div>
            </div>
          ))}
        </div>
      ) : plants.length === 0 ? (
        <div className="p-2 rounded-[2.25rem] bg-sand-100/70 border border-stone-200/80 shadow-subtle max-w-lg mx-auto">
          <div className="text-center py-16 bg-white rounded-[1.85rem] border border-stone-100 p-8 shadow-card">
            <div className="w-16 h-16 rounded-full bg-sand-100 text-botanical-700 flex items-center justify-center mx-auto mb-4">
              <Heart className="w-8 h-8 text-botanical-600" />
            </div>
            <h3 className="font-serif text-xl font-bold text-stone-800">No Plant Companions Adopted Yet</h3>
            <p className="text-xs text-stone-500 mt-2 mb-6 leading-relaxed max-w-sm mx-auto">
              Adopt an indoor botanical specimen to receive automated watering countdowns and specialized foliage care reminders.
            </p>
            <button
              onClick={() => setAdoptModalOpen(true)}
              className="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-botanical-800 hover:bg-botanical-900 text-white font-bold text-xs shadow-md shadow-botanical-900/10 transition active:scale-95"
            >
              <span>Adopt Your First Plant</span>
            </button>
          </div>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-7">
          {plants.map((plant) => {
            const isOverdue = plant.is_overdue || false;

            return (
              <div
                key={plant.id}
                className="p-2 rounded-[2rem] bg-sand-100/60 border border-stone-200/80 shadow-subtle hover:shadow-card transition flex flex-col justify-between"
              >
                <div className="bg-white rounded-[1.75rem] p-5 sm:p-6 space-y-4 border border-stone-100">
                  <div className="flex items-start justify-between gap-3">
                    <div>
                      <span className="text-[10px] uppercase font-bold text-botanical-700 tracking-wider">
                        Adopted Companion
                      </span>
                      <h3 className="font-serif text-xl font-bold text-stone-900 mt-0.5">
                        "{plant.nickname}"
                      </h3>
                      <p className="text-xs text-stone-500 font-medium">
                        {plant.product?.name || plant.product_name || 'Living Botanical Specimen'}
                      </p>
                    </div>

                    {isOverdue ? (
                      <span className="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1 flex-shrink-0 animate-pulse">
                        <AlertTriangle className="w-3 h-3 text-amber-600" />
                        <span>Thirsty!</span>
                      </span>
                    ) : (
                      <span className="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1 flex-shrink-0">
                        <CheckCircle2 className="w-3 h-3 text-emerald-600" />
                        <span>Hydrated</span>
                      </span>
                    )}
                  </div>

                  {/* Watering Metrics */}
                  <div className="grid grid-cols-2 gap-2 text-xs pt-2 border-t border-stone-100">
                    <div className="p-3 rounded-2xl bg-sand-50/70 border border-stone-100">
                      <div className="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Frequency</div>
                      <div className="font-bold text-stone-800 mt-0.5 font-mono tabular-nums">Every {plant.reminder_frequency_days} Days</div>
                    </div>
                    <div className="p-3 rounded-2xl bg-sand-50/70 border border-stone-100">
                      <div className="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Next Water</div>
                      <div className={`font-bold mt-0.5 font-mono tabular-nums ${isOverdue ? 'text-amber-800' : 'text-stone-800'}`}>
                        {plant.next_watering_due ? new Date(plant.next_watering_due).toLocaleDateString() : 'Due Today'}
                      </div>
                    </div>
                  </div>

                  {plant.notes && (
                    <p className="text-xs text-stone-500 italic bg-sand-50/50 p-3 rounded-2xl border border-stone-100">
                      "{plant.notes}"
                    </p>
                  )}

                  {/* 1-Tap Water Button */}
                  <div className="pt-2">
                    <button
                      onClick={() => handleWater(plant.id, plant.nickname)}
                      disabled={wateringId === plant.id}
                      className="w-full py-2.5 px-4 rounded-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2 active:scale-98"
                    >
                      <Droplets className="w-4 h-4 text-blue-200" />
                      <span>{wateringId === plant.id ? 'Logging Hydration...' : 'Log Plant Watered Today'}</span>
                    </button>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}

      {/* Adopt Modal */}
      {adoptModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div onClick={() => setAdoptModalOpen(false)} className="fixed inset-0 bg-stone-900/60 backdrop-blur-sm" />
          <div className="p-2 rounded-[2.25rem] bg-stone-800/20 backdrop-blur-md border border-white/20 shadow-2xl relative w-full max-w-md z-10 animate-in zoom-in-95">
            <div className="relative bg-white rounded-[1.85rem] p-6 sm:p-7 border border-stone-100">
              <div className="flex items-center justify-between mb-5">
                <h3 className="font-serif text-xl font-bold text-stone-900 flex items-center gap-2">
                  <Leaf className="w-5 h-5 text-botanical-700" />
                  <span>Adopt Plant Companion</span>
                </h3>
                <button
                  onClick={() => setAdoptModalOpen(false)}
                  className="p-1.5 rounded-full text-stone-400 hover:text-stone-700 hover:bg-sand-100 transition"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>

              <form onSubmit={handleAdoptSubmit} className="space-y-4 text-xs">
                <div>
                  <label className="block font-semibold text-stone-700 mb-1">Companion Nickname *</label>
                  <input
                    type="text"
                    required
                    value={newPlant.nickname}
                    onChange={(e) => setNewPlant({ ...newPlant, nickname: e.target.value })}
                    placeholder="e.g. Monty, Penelope, Fernie"
                    className="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-none focus:ring-2 focus:ring-botanical-500/20 focus:border-botanical-600 transition"
                  />
                </div>

                <div>
                  <label className="block font-semibold text-stone-700 mb-1">Botanical Variety / Specimen Name *</label>
                  <input
                    type="text"
                    required
                    value={newPlant.product_name}
                    onChange={(e) => setNewPlant({ ...newPlant, product_name: e.target.value })}
                    placeholder="e.g. Fiddle Leaf Fig, Pothos"
                    className="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-none focus:ring-2 focus:ring-botanical-500/20 focus:border-botanical-600 transition"
                  />
                </div>

                <div>
                  <label className="block font-semibold text-stone-700 mb-1">Watering Rhythm (Days) *</label>
                  <input
                    type="number"
                    min="1"
                    max="60"
                    required
                    value={newPlant.reminder_frequency_days}
                    onChange={(e) => setNewPlant({ ...newPlant, reminder_frequency_days: parseInt(e.target.value) || 7 })}
                    className="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-none focus:ring-2 focus:ring-botanical-500/20 focus:border-botanical-600 transition font-mono tabular-nums"
                  />
                </div>

                <div>
                  <label className="block font-semibold text-stone-700 mb-1">Care Notes / Light Location</label>
                  <textarea
                    rows="2"
                    value={newPlant.notes}
                    onChange={(e) => setNewPlant({ ...newPlant, notes: e.target.value })}
                    placeholder="e.g. South window shelf, likes morning mist"
                    className="w-full px-4 py-2.5 rounded-xl border border-stone-200 focus:outline-none focus:ring-2 focus:ring-botanical-500/20 focus:border-botanical-600 transition"
                  />
                </div>

                <div className="pt-2">
                  <button
                    type="submit"
                    className="w-full py-3 rounded-full bg-botanical-800 hover:bg-botanical-900 text-white font-bold transition shadow-md shadow-botanical-900/10 active:scale-98"
                  >
                    Save to My Digital Garden
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
