import React from 'react';
import { useStore } from '../context/StoreContext';
import { AlertTriangle } from 'lucide-react';

export default function MaintenanceBanner() {
  const { maintenanceMode, maintenanceMessage } = useStore();

  if (!maintenanceMode) return null;

  return (
    <div className="bg-amber-500 text-stone-950 px-4 py-2 text-xs font-bold shadow-xs border-b border-amber-600 flex items-center justify-center">
      <div className="flex items-center gap-2 max-w-4xl truncate text-center">
        <AlertTriangle className="w-4 h-4 text-stone-950 flex-shrink-0" />
        <span className="truncate">
          {maintenanceMessage || 'Greenhouses are currently undergoing seasonal care updates.'}
        </span>
      </div>
    </div>
  );
}
