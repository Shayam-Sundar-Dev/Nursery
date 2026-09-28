import React, { useState } from 'react';
import { useStore } from '../context/StoreContext';
import {
  Leaf,
  Mail,
  Phone,
  MapPin,
  Clock,
  Instagram,
  Facebook,
  ShieldCheck,
  Truck,
  Sparkles,
  ArrowRight,
  ThermometerSnowflake,
} from 'lucide-react';

export default function Footer() {
  const { siteSettings, siteName, navigateTo, addToast } = useStore();
  const [emailInput, setEmailInput] = useState('');

  const contact = siteSettings?.contact;
  const social = siteSettings?.social;
  const general = siteSettings?.general;
  const shipping = siteSettings?.shipping;

  const handleSubscribe = (e) => {
    e.preventDefault();
    if (emailInput.trim()) {
      addToast('Subscribed to greenhouse rare specimen drops and seasonal care guides.');
      setEmailInput('');
    }
  };

  return (
    <footer className="bg-botanical-950 text-stone-300 pt-16 sm:pt-20 pb-12 border-t border-botanical-900/80 mt-20 sm:mt-28">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {/* Value Proposition Badges */}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-6 pb-14 border-b border-botanical-900/60 mb-14">
          <div className="flex items-start gap-4 p-5 rounded-3xl bg-botanical-900/40 border border-botanical-800/60 shadow-inner">
            <div className="w-12 h-12 rounded-2xl bg-botanical-800 text-emerald-300 flex items-center justify-center flex-shrink-0 shadow-xs">
              <ShieldCheck className="w-6 h-6" />
            </div>
            <div>
              <div className="font-serif font-bold text-white text-base">
                {shipping?.guarantee_text || '30-Day Living Guarantee'}
              </div>
              <div className="text-xs text-stone-400 mt-1 leading-relaxed">
                Every specimen arrives vibrant and root-healthy or we replace it immediately.
              </div>
            </div>
          </div>

          <div className="flex items-start gap-4 p-5 rounded-3xl bg-botanical-900/40 border border-botanical-800/60 shadow-inner">
            <div className="w-12 h-12 rounded-2xl bg-botanical-800 text-amber-300 flex items-center justify-center flex-shrink-0 shadow-xs">
              <ThermometerSnowflake className="w-6 h-6" />
            </div>
            <div>
              <div className="font-serif font-bold text-white text-base">Thermal Climate Pods</div>
              <div className="text-xs text-stone-400 mt-1 leading-relaxed">
                72-hour moisture-sealed corrugated pods with insulation against transit temperature shifts.
              </div>
            </div>
          </div>

          <div className="flex items-start gap-4 p-5 rounded-3xl bg-botanical-900/40 border border-botanical-800/60 shadow-inner">
            <div className="w-12 h-12 rounded-2xl bg-botanical-800 text-emerald-300 flex items-center justify-center flex-shrink-0 shadow-xs">
              <Sparkles className="w-6 h-6" />
            </div>
            <div>
              <div className="font-serif font-bold text-white text-base">Direct Nursery Acclimation</div>
              <div className="text-xs text-stone-400 mt-1 leading-relaxed">
                Nurtured by botanists and acclimated for smooth indoor transition into your space.
              </div>
            </div>
          </div>
        </div>

        {/* Footer Navigation Columns */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-10 sm:gap-12 pb-14 border-b border-botanical-900/60">
          {/* Brand Info */}
          <div className="space-y-4 md:col-span-1">
            <div className="flex items-center gap-3">
              <div className="w-11 h-11 rounded-2xl bg-botanical-800 text-white flex items-center justify-center shadow-md">
                <Leaf className="w-5 h-5 text-emerald-200" />
              </div>
              <span className="font-serif text-2xl font-bold text-white tracking-tight">
                {siteName}
              </span>
            </div>
            <p className="text-xs text-stone-400 leading-relaxed font-normal">
              {general?.footer_text || 'Cultivating rare indoor species and delivering living houseplants directly from our greenhouse sanctuaries.'}
            </p>
            <div className="flex items-center gap-3 pt-2">
              {social?.social_instagram && (
                <a
                  href={social.social_instagram}
                  target="_blank"
                  rel="noreferrer"
                  className="w-9 h-9 rounded-full bg-botanical-900 hover:bg-botanical-800 text-stone-300 hover:text-white flex items-center justify-center transition border border-botanical-800"
                  aria-label="Instagram"
                >
                  <Instagram className="w-4 h-4" />
                </a>
              )}
              {social?.social_facebook && (
                <a
                  href={social.social_facebook}
                  target="_blank"
                  rel="noreferrer"
                  className="w-9 h-9 rounded-full bg-botanical-900 hover:bg-botanical-800 text-stone-300 hover:text-white flex items-center justify-center transition border border-botanical-800"
                  aria-label="Facebook"
                >
                  <Facebook className="w-4 h-4" />
                </a>
              )}
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h4 className="font-bold text-xs uppercase tracking-widest text-emerald-300/80 mb-4">
              Explore Sanctuary
            </h4>
            <ul className="space-y-3 text-xs">
              <li>
                <button
                  onClick={() => navigateTo('catalog')}
                  className="hover:text-white transition font-medium cursor-pointer"
                >
                  Houseplants & Tropicals
                </button>
              </li>
              <li>
                <button
                  onClick={() => navigateTo('quiz')}
                  className="hover:text-white transition font-medium cursor-pointer"
                >
                  Botanical Matcher Algorithm
                </button>
              </li>
              <li>
                <button
                  onClick={() => navigateTo('catalog', { category: 'rare-botanicals' })}
                  className="hover:text-white transition font-medium cursor-pointer"
                >
                  Rare Collectors Specimens
                </button>
              </li>
              <li>
                <button
                  onClick={() => navigateTo('catalog')}
                  className="hover:text-white transition font-medium cursor-pointer"
                >
                  Pet-Safe Houseplants
                </button>
              </li>
            </ul>
          </div>

          {/* Contact & Nursery Address */}
          <div>
            <h4 className="font-bold text-xs uppercase tracking-widest text-emerald-300/80 mb-4">
              Greenhouse Location
            </h4>
            <div className="space-y-3 text-xs text-stone-400">
              {contact?.nursery_address && (
                <div className="flex items-start gap-2.5">
                  <MapPin className="w-4 h-4 text-emerald-400 flex-shrink-0 mt-0.5" />
                  <span>{contact.nursery_address}</span>
                </div>
              )}
              {contact?.contact_email && (
                <div className="flex items-center gap-2.5">
                  <Mail className="w-4 h-4 text-emerald-400 flex-shrink-0" />
                  <a href={`mailto:${contact.contact_email}`} className="hover:text-white transition">
                    {contact.contact_email}
                  </a>
                </div>
              )}
              {contact?.contact_phone && (
                <div className="flex items-center gap-2.5">
                  <Phone className="w-4 h-4 text-emerald-400 flex-shrink-0" />
                  <span>{contact.contact_phone}</span>
                </div>
              )}
              {contact?.operating_hours && (
                <div className="flex items-start gap-2.5 pt-1 text-[11px] text-stone-500">
                  <Clock className="w-3.5 h-3.5 text-stone-400 flex-shrink-0 mt-0.5" />
                  <span>{contact.operating_hours}</span>
                </div>
              )}
            </div>
          </div>

          {/* Newsletter Box (Button-in-Button) */}
          <div>
            <h4 className="font-bold text-xs uppercase tracking-widest text-emerald-300/80 mb-4">
              Seasonal Botanical Journal
            </h4>
            <p className="text-xs text-stone-400 mb-4 leading-relaxed font-normal">
              Receive alerts for rare greenhouse harvests, repotting calendars, and seasonal care guides.
            </p>
            <form onSubmit={handleSubscribe} className="relative flex items-center">
              <input
                type="email"
                required
                value={emailInput}
                onChange={(e) => setEmailInput(e.target.value)}
                placeholder="Enter email address"
                className="w-full pl-5 pr-14 py-3 rounded-full bg-white/5 border border-white/10 text-white placeholder-stone-500 text-xs focus:outline-none focus:border-emerald-400/80 transition"
              />
              <button
                type="submit"
                className="absolute right-1.5 top-1.5 w-9 h-9 rounded-full bg-emerald-400 hover:bg-emerald-300 text-stone-950 flex items-center justify-center transition active:scale-95 cursor-pointer shadow-sm"
                aria-label="Subscribe to newsletter"
              >
                <ArrowRight className="w-4 h-4" />
              </button>
            </form>
          </div>
        </div>

        {/* Bottom Legal & Copyright Bar */}
        <div className="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-stone-500">
          <div>
            {general?.copyright_text || `Verdant Botanical Nursery & Garden © ${new Date().getFullYear()}. All rights reserved.`}
          </div>
          <div className="flex items-center gap-4 text-[11px]">
            <span className="text-emerald-400/90 font-medium">Currency: {general?.currency_code || 'INR'} ({general?.currency_symbol || '₹'})</span>
            <span>&bull;</span>
            <span className="hover:text-stone-400 cursor-pointer">Privacy Policy</span>
            <span>&bull;</span>
            <span className="hover:text-stone-400 cursor-pointer">Terms of Nursery</span>
            <span>&bull;</span>
            <span className="hover:text-stone-400 cursor-pointer">Transit Guarantee</span>
          </div>
        </div>
      </div>
    </footer>
  );
}
