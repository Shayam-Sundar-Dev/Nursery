<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Address Labels - Processing & Packaging ({{ $orders->count() }})</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        botanical: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            600: '#16a34a',
                            800: '#166534',
                            900: '#14532d',
                            950: '#052e16',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .label-page {
                page-break-after: always;
                break-inside: avoid;
                margin: 0;
                padding: 8mm 6mm;
                border: 2px solid #000000 !important;
                box-shadow: none !important;
            }
            .label-page:last-child {
                page-break-after: avoid;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }

        @media screen {
            body {
                background-color: #f5f5f4;
            }
            .label-page {
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }
        }
    </style>
</head>
<body class="font-sans antialiased text-stone-900 min-h-screen py-6 print:py-0">

    <!-- Top Floating Toolbar (Hidden when printing) -->
    <div class="no-print max-w-4xl mx-auto mb-6 px-4">
        <div class="bg-white border border-stone-200 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-stone-200 bg-stone-50 hover:bg-stone-100 text-xs font-semibold text-stone-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Orders
                </a>
                <div>
                    <h1 class="text-sm font-bold text-stone-900">
                        Shipping Address Labels (From - To)
                    </h1>
                    <p class="text-xs text-stone-500">
                        Ready to print for <span class="font-semibold text-blue-700">{{ $orders->count() }} Processing & Packaging</span> order(s)
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <button onclick="window.print()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-botanical-800 hover:bg-botanical-900 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Labels Now (Ctrl/Cmd + P)
                </button>
            </div>
        </div>

        <div class="mt-2 text-[11px] text-stone-500 text-center">
            Tip: For best results, select <span class="font-semibold text-stone-700">"Default"</span> margins and enable <span class="font-semibold text-stone-700">"Background graphics"</span> in your browser's print options.
        </div>
    </div>

    <!-- Printable Container -->
    <div class="max-w-4xl mx-auto px-4 print:px-0 space-y-8 print:space-y-0">
        @foreach($orders as $index => $order)
            @php
                $ship = $order->shipping_address ?? [];
                $hasThermal = ($order->insulation_packaging_fee ?? 0) > 0;
            @endphp

            <div class="label-page bg-white rounded-2xl border-2 border-stone-900 p-6 sm:p-8 space-y-6">

                <!-- Header Bar -->
                <div class="border-b-2 border-stone-900 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-stone-900 text-white flex items-center justify-center font-bold text-lg shrink-0">
                            🌿
                        </div>
                        <div>
                            <div class="text-xs uppercase tracking-widest font-extrabold text-stone-900">
                                {{ $fromAddress['company_name'] }}
                            </div>
                            <div class="text-[11px] text-stone-600 font-medium">
                                {{ $fromAddress['tagline'] }}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:items-end">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-blue-100 text-blue-900 border border-blue-400">
                                Processing & Packaging
                            </span>
                            <span class="text-xs font-mono font-bold text-stone-900">
                                #{{ $order->order_number }}
                            </span>
                        </div>
                        <div class="text-[11px] text-stone-500 font-mono mt-0.5">
                            Order Date: {{ $order->created_at->format('M d, Y h:i A') }}
                        </div>
                    </div>
                </div>

                <!-- Priority Handling Strip -->
                <div class="bg-stone-100 border border-stone-300 rounded-xl p-3 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2 font-bold text-stone-900 uppercase tracking-wide text-[11px]">
                        <span class="text-base leading-none">🌱</span>
                        <span>Live Botanical Plant Transit &bull; Fragile &bull; Keep Upright</span>
                    </div>

                    @if($hasThermal)
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-teal-100 border border-teal-400 text-teal-950 font-extrabold text-[10px] uppercase tracking-wider">
                            <span>❄️ Thermal Pack Enclosed</span>
                        </div>
                    @endif

                    <div class="text-[11px] text-stone-700 font-mono">
                        Carrier: <span class="font-bold">{{ $order->carrier_name ?? 'Botanical Express Transit' }}</span>
                        @if($order->tracking_code)
                            &bull; Trk: <span class="font-bold">{{ $order->tracking_code }}</span>
                        @endif
                    </div>
                </div>

                <!-- FROM - TO SECTION (Side by Side Grid) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- FROM / SENDER BOX -->
                    <div class="border-2 border-stone-300 rounded-xl p-4 bg-stone-50/50 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between border-b border-stone-300 pb-2 mb-2.5">
                                <span class="text-[10px] font-extrabold uppercase tracking-widest text-stone-500">
                                    FROM / SENDER / RETURN ADDRESS
                                </span>
                                <span class="text-[10px] font-bold text-botanical-800 bg-botanical-100 px-2 py-0.5 rounded">
                                    Nursery Greenhouse
                                </span>
                            </div>

                            <div class="space-y-1 text-xs text-stone-800">
                                <div class="font-extrabold text-sm text-stone-900">
                                    {{ $fromAddress['company_name'] }}
                                </div>
                                <div class="text-stone-700 whitespace-pre-line leading-relaxed">
                                    {{ $fromAddress['address'] }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-stone-200 text-[11px] text-stone-600 space-y-0.5">
                            <div><span class="font-bold text-stone-700">Phone:</span> {{ $fromAddress['phone'] }}</div>
                            <div><span class="font-bold text-stone-700">Email:</span> {{ $fromAddress['email'] }}</div>
                        </div>
                    </div>

                    <!-- TO / CONSIGNEE (SHIP TO) BOX -->
                    <div class="border-2 border-stone-900 rounded-xl p-4 bg-white flex flex-col justify-between shadow-xs">
                        <div>
                            <div class="flex items-center justify-between border-b-2 border-stone-900 pb-2 mb-2.5">
                                <span class="text-[11px] font-black uppercase tracking-widest text-stone-900">
                                    SHIP TO / DELIVER TO (RECIPIENT)
                                </span>
                                <span class="text-[10px] font-bold text-blue-900 bg-blue-100 px-2 py-0.5 rounded">
                                    Customer Destination
                                </span>
                            </div>

                            <div class="space-y-1.5">
                                <div class="font-black text-base sm:text-lg text-stone-950 tracking-tight">
                                    {{ $ship['recipient'] ?? $order->customer_name }}
                                </div>
                                <div class="text-xs sm:text-sm font-semibold text-stone-800 leading-snug">
                                    {{ $ship['street'] ?? 'Address line not provided' }}
                                </div>
                                <div class="text-xs sm:text-sm font-bold text-stone-900">
                                    {{ $ship['city'] ?? '' }}{{ !empty($ship['state']) ? ', '.$ship['state'] : '' }}
                                    <span class="font-mono font-extrabold text-stone-950 text-sm">
                                        {{ $ship['postal_code'] ?? $order->postal_code }}
                                    </span>
                                </div>
                                <div class="text-xs font-semibold text-stone-700 uppercase">
                                    {{ $ship['country'] ?? 'India' }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-stone-300 text-xs text-stone-800 space-y-1">
                            <div>
                                <span class="font-bold text-stone-900">Contact Phone:</span>
                                <span class="font-mono font-bold">{{ $order->customer_phone ?? $ship['phone'] ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-stone-900">Customer Email:</span>
                                <span class="font-mono">{{ $order->customer_email }}</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Ordered Products & Packaging Checklist -->
                <div class="border border-stone-300 rounded-xl overflow-hidden">
                    <div class="bg-stone-100 px-4 py-2 border-b border-stone-300 flex items-center justify-between text-xs">
                        <span class="font-extrabold uppercase tracking-wider text-stone-800">
                            Package Contents & Verification Checklist ({{ $order->items->count() }} line item{{ $order->items->count() === 1 ? '' : 's' }})
                        </span>
                        <span class="text-[11px] text-stone-500 font-mono">
                            Total Units: {{ $order->items->sum('quantity') }}
                        </span>
                    </div>

                    <table class="w-full text-left text-xs">
                        <thead class="bg-stone-50 border-b border-stone-200 text-[10px] uppercase font-bold text-stone-600">
                            <tr>
                                <th class="py-2 px-3 w-8 text-center">Chk</th>
                                <th class="py-2 px-3">Botanical Specimen / Product Item</th>
                                <th class="py-2 px-3">Variant / Pot Type</th>
                                <th class="py-2 px-3 w-16 text-center">Quantity</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200">
                            @foreach($order->items as $item)
                                <tr class="hover:bg-stone-50/50">
                                    <td class="py-2.5 px-3 text-center">
                                        <div class="w-4 h-4 rounded border-2 border-stone-400 mx-auto"></div>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-stone-900 text-xs">{{ $item->product_name }}</div>
                                        @if($item->product?->botanical_name)
                                            <div class="text-[11px] italic text-botanical-800 font-serif">{{ $item->product->botanical_name }}</div>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-stone-600">
                                        {{ $item->variant_title ?? 'Standard Specimen' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-stone-900 text-xs">
                                        &times; {{ $item->quantity }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($order->gift_message)
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-300 text-xs text-amber-950">
                        <div class="font-bold uppercase tracking-wider text-[10px] text-amber-900 mb-0.5">
                            Customer Gift Message Note:
                        </div>
                        <p class="italic">"{{ $order->gift_message }}"</p>
                    </div>
                @endif

                <!-- Quality & Verification Footer -->
                <div class="pt-4 border-t-2 border-stone-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-stone-600">
                    <div class="flex items-center gap-4 text-[11px]">
                        <div>Packaged By: <span class="font-mono text-stone-800 underline decoration-dotted">____________________</span></div>
                        <div>Inspection Date: <span class="font-mono text-stone-800 underline decoration-dotted">____________________</span></div>
                    </div>

                    <div class="text-[10px] font-mono text-stone-400">
                        {{ $order->order_number }} &bull; Page {{ $index + 1 }} of {{ $orders->count() }}
                    </div>
                </div>

            </div>
        @endforeach
    </div>

</body>
</html>

