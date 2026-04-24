@extends('layouts.app')
@section('title', 'Riwayat Makan')

@section('content')
    <div class="space-y-5 fade-in">

        {{-- ── HEADER ─────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-display font-bold text-gray-900">📋 Riwayat Makan</h1>
                <p class="text-gray-500 text-sm mt-0.5">Log makanan berdasarkan tanggal</p>
            </div>

            {{-- Date Picker --}}
            <form method="GET" action="{{ route('food.history') }}" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" onchange="this.form.submit()"
                    class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white">
            </form>
        </div>

        {{-- ── DATE NAVIGATION ────────────────────────────────────── --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('food.history', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}"
                class="p-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 hover:border-gray-300 transition shadow-sm">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <span class="flex-1 text-center text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl px-4 py-2.5 shadow-sm">
                {{ $date->translatedFormat('d F Y') }}
                @if ($date->isToday())
                    <span class="text-brand-500 font-normal">&nbsp;· Hari ini</span>
                @endif
            </span>
            @if (!$date->isToday())
                <a href="{{ route('food.history', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}"
                    class="p-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 hover:border-gray-300 transition shadow-sm">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <div class="p-2.5 rounded-xl border border-gray-100 bg-gray-50 opacity-40 cursor-not-allowed">
                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            @endif
        </div>

        {{-- ── CONTENT ─────────────────────────────────────────────── --}}
        @if ($logs->count() > 0)

            {{-- Summary Bar --}}
            <div class="bg-gradient-to-br from-brand-500 to-brand-600 rounded-2xl p-5 text-white shadow-md">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <p class="text-sm font-medium text-brand-100">Total Kalori</p>
                        <p class="text-3xl font-display font-bold mt-0.5">{{ number_format($totalCalories) }}
                            <span class="text-lg font-normal text-brand-200">kkal</span>
                        </p>
                    </div>
                    <div class="flex gap-4 sm:gap-6">
                        <div class="text-center">
                            <p class="text-[11px] text-brand-200 font-medium uppercase tracking-wide">Protein</p>
                            <p class="text-xl font-bold mt-0.5">{{ round($logs->sum('protein')) }}<span class="text-sm font-normal text-brand-200">g</span></p>
                        </div>
                        <div class="text-center">
                            <p class="text-[11px] text-brand-200 font-medium uppercase tracking-wide">Karbo</p>
                            <p class="text-xl font-bold mt-0.5">{{ round($logs->sum('carbs')) }}<span class="text-sm font-normal text-brand-200">g</span></p>
                        </div>
                        <div class="text-center">
                            <p class="text-[11px] text-brand-200 font-medium uppercase tracking-wide">Lemak</p>
                            <p class="text-xl font-bold mt-0.5">{{ round($logs->sum('fat')) }}<span class="text-sm font-normal text-brand-200">g</span></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Logs grouped by meal type --}}
            @foreach (['breakfast' => ['🌅', 'Sarapan'], 'lunch' => ['☀️', 'Makan Siang'], 'dinner' => ['🌙', 'Makan Malam'], 'snack' => ['🍎', 'Snack']] as $type => [$icon, $label])
                @php $mealLogs = $logs->where('meal_type', $type); @endphp
                @if ($mealLogs->count() > 0)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                        {{-- Meal Header --}}
                        <div class="px-5 py-3.5 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-base">{{ $icon }}</span>
                                <span class="font-bold text-gray-800 text-sm">{{ $label }}</span>
                                <span class="text-xs text-gray-400 font-medium bg-gray-100 px-2 py-0.5 rounded-full">
                                    {{ $mealLogs->count() }} item
                                </span>
                            </div>
                            <span class="text-sm font-bold text-brand-600">
                                {{ number_format($mealLogs->sum('calories')) }} kkal
                            </span>
                        </div>

                        {{-- Meal Items --}}
                        <div class="divide-y divide-gray-50">
                            @foreach ($mealLogs as $log)
                                <div class="px-4 sm:px-5 py-4">
                                    <div class="flex items-center gap-3 sm:gap-4">

                                        {{-- Thumbnail — clickable untuk detail --}}
                                        <button type="button" onclick="openHistoryDetail({{ $log->id }})"
                                            class="flex-shrink-0 focus:outline-none group">
                                            @if ($log->image_path)
                                                <img src="{{ $log->image_path }}"
                                                    class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl object-cover group-hover:opacity-80 transition ring-2 ring-transparent group-hover:ring-brand-300">
                                            @else
                                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-brand-50 flex items-center justify-center text-2xl group-hover:bg-brand-100 transition">
                                                    🍴
                                                </div>
                                            @endif
                                        </button>

                                        {{-- Info --}}
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-gray-800 truncate text-sm sm:text-base">
                                                {{ $log->food_name }}
                                            </p>
                                            @if ($log->food_description)
                                                <p class="text-xs text-gray-400 mt-0.5 line-clamp-1 hidden sm:block">
                                                    {{ $log->food_description }}
                                                </p>
                                            @endif
                                            <div class="flex flex-wrap gap-2 mt-1.5">
                                                @if ($log->protein)
                                                    <span class="inline-flex items-center text-[11px] text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full font-medium">P: {{ round($log->protein) }}g</span>
                                                @endif
                                                @if ($log->carbs)
                                                    <span class="inline-flex items-center text-[11px] text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full font-medium">K: {{ round($log->carbs) }}g</span>
                                                @endif
                                                @if ($log->fat)
                                                    <span class="inline-flex items-center text-[11px] text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full font-medium">L: {{ round($log->fat) }}g</span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Calories + time --}}
                                        <div class="text-right flex-shrink-0 hidden sm:block">
                                            <p class="font-bold text-brand-600 text-base">{{ number_format($log->calories) }}</p>
                                            <p class="text-xs text-gray-400">kkal</p>
                                            <p class="text-xs text-gray-300 mt-1">{{ $log->created_at->format('H:i') }}</p>
                                        </div>

                                        {{-- Mobile: calories only --}}
                                        <div class="text-right flex-shrink-0 sm:hidden">
                                            <p class="font-bold text-brand-600 text-sm">{{ number_format($log->calories) }}</p>
                                            <p class="text-[10px] text-gray-400">kkal</p>
                                        </div>

                                        {{-- Action Buttons --}}
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            {{-- Detail Button --}}
                                            <button type="button" onclick="openHistoryDetail({{ $log->id }})"
                                                class="p-2 text-gray-300 hover:text-brand-500 transition rounded-lg hover:bg-brand-50"
                                                title="Lihat detail">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>

                                            {{-- Delete Button --}}
                                            <form method="POST" action="{{ route('food.destroy', $log) }}"
                                                onsubmit="return confirm('Hapus log ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="p-2 text-gray-300 hover:text-red-400 transition rounded-lg hover:bg-red-50"
                                                    title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- Mobile time row --}}
                                    <p class="text-[10px] text-gray-300 mt-2 text-right sm:hidden">{{ $log->created_at->format('H:i') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

        @else
            {{-- Empty State --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                <div class="text-5xl mb-4">🗓️</div>
                <p class="font-semibold text-gray-600">Tidak ada log untuk tanggal ini</p>
                <p class="text-sm text-gray-400 mt-1">Coba pilih tanggal lain atau mulai catat makanan kamu</p>
                <a href="{{ route('dashboard') }}"
                    class="inline-block mt-5 bg-brand-500 text-white text-sm font-semibold px-6 py-3 rounded-xl hover:bg-brand-600 transition shadow-sm">
                    Catat Sekarang →
                </a>
            </div>
        @endif

    </div>


    {{-- ============================================================ --}}
    {{-- DETAIL MODAL                                                 --}}
    {{-- ============================================================ --}}
    <div id="history-detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeHistoryDetail()"></div>
        <div id="history-detail-panel"
            class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-auto overflow-hidden
                   transform transition-all duration-200 scale-95 opacity-0">

            {{-- Foto --}}
            <div id="hd-img-wrap" class="hidden">
                <img id="hd-img" src="" alt="" class="w-full h-52 object-cover">
            </div>
            <div id="hd-no-img" class="w-full h-32 bg-brand-50 flex items-center justify-center text-5xl">🍴</div>

            {{-- Close --}}
            <button onclick="closeHistoryDetail()"
                class="absolute top-3 right-3 w-8 h-8 bg-black/30 hover:bg-black/50 text-white rounded-full flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            {{-- Content --}}
            <div class="p-5">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <h3 class="font-bold text-gray-900 text-lg leading-tight" id="hd-name">-</h3>
                    <span class="text-xs text-gray-400 flex-shrink-0 mt-1" id="hd-time">-</span>
                </div>
                <p class="text-xs text-gray-400 mb-4" id="hd-meal-type">-</p>

                {{-- Kalori --}}
                <div class="bg-brand-50 rounded-xl p-4 mb-4 text-center">
                    <p class="text-3xl font-display font-bold text-brand-600" id="hd-cal">0</p>
                    <p class="text-xs text-brand-400 mt-0.5">kkal</p>
                </div>

                {{-- Makro --}}
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="text-center bg-blue-50 rounded-xl p-3">
                        <p class="text-xl font-bold text-blue-600" id="hd-protein">0</p>
                        <p class="text-[11px] text-blue-400 mt-0.5">Protein (g)</p>
                    </div>
                    <div class="text-center bg-amber-50 rounded-xl p-3">
                        <p class="text-xl font-bold text-amber-600" id="hd-carbs">0</p>
                        <p class="text-[11px] text-amber-400 mt-0.5">Karbo (g)</p>
                    </div>
                    <div class="text-center bg-rose-50 rounded-xl p-3">
                        <p class="text-xl font-bold text-rose-600" id="hd-fat">0</p>
                        <p class="text-[11px] text-rose-400 mt-0.5">Lemak (g)</p>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div id="hd-desc-wrap" class="hidden mb-3">
                    <p class="text-xs font-semibold text-gray-500 mb-1">Deskripsi</p>
                    <p class="text-sm text-gray-600" id="hd-desc"></p>
                </div>

                {{-- Notes AI --}}
                <div id="hd-notes-wrap" class="hidden">
                    <p class="text-xs font-semibold text-gray-500 mb-1">📝 Catatan AI</p>
                    <p class="text-xs text-gray-400 italic" id="hd-notes"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Data logs untuk JS --}}
    @if ($logs->count() > 0)
        <script>
            const historyLogsData = {
                @foreach ($logs as $log)
                    {{ $log->id }}: {
                        food_name:        @json($log->food_name),
                        food_description: @json($log->food_description),
                        calories:         {{ $log->calories }},
                        protein:          {{ $log->protein ?? 0 }},
                        carbs:            {{ $log->carbs ?? 0 }},
                        fat:              {{ $log->fat ?? 0 }},
                        meal_type_label:  @json($log->meal_type_label ?? ucfirst($log->meal_type)),
                        image_path:       @json($log->image_path),
                        created_at:       @json($log->created_at->format('H:i')),
                        ai_notes:         @json(optional(json_decode($log->ai_analysis))->notes ?? ''),
                    },
                @endforeach
            };
        </script>
    @endif

@endsection

@push('scripts')
<script>
    function openHistoryDetail(id) {
        const log = (typeof historyLogsData !== 'undefined') ? historyLogsData[id] : null;
        if (!log) return;

        const modal = document.getElementById('history-detail-modal');
        const panel = document.getElementById('history-detail-panel');

        document.getElementById('hd-name').textContent      = log.food_name || '-';
        document.getElementById('hd-time').textContent      = log.created_at || '';
        document.getElementById('hd-meal-type').textContent = log.meal_type_label || '';
        document.getElementById('hd-cal').textContent       = log.calories || 0;
        document.getElementById('hd-protein').textContent   = log.protein || 0;
        document.getElementById('hd-carbs').textContent     = log.carbs || 0;
        document.getElementById('hd-fat').textContent       = log.fat || 0;

        if (log.image_path) {
            document.getElementById('hd-img').src = log.image_path;
            document.getElementById('hd-img-wrap').classList.remove('hidden');
            document.getElementById('hd-no-img').classList.add('hidden');
        } else {
            document.getElementById('hd-img-wrap').classList.add('hidden');
            document.getElementById('hd-no-img').classList.remove('hidden');
        }

        if (log.food_description) {
            document.getElementById('hd-desc').textContent = log.food_description;
            document.getElementById('hd-desc-wrap').classList.remove('hidden');
        } else {
            document.getElementById('hd-desc-wrap').classList.add('hidden');
        }

        if (log.ai_notes) {
            document.getElementById('hd-notes').textContent = log.ai_notes;
            document.getElementById('hd-notes-wrap').classList.remove('hidden');
        } else {
            document.getElementById('hd-notes-wrap').classList.add('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => {
            panel.classList.remove('scale-95', 'opacity-0');
            panel.classList.add('scale-100', 'opacity-100');
        });
    }

    function closeHistoryDetail() {
        const modal = document.getElementById('history-detail-modal');
        const panel = document.getElementById('history-detail-panel');
        panel.classList.remove('scale-100', 'opacity-100');
        panel.classList.add('scale-95', 'opacity-0');
        document.body.style.overflow = '';
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeHistoryDetail();
    });
</script>
@endpush