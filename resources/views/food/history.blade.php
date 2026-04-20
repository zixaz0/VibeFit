@extends('layouts.app')
@section('title', 'Riwayat Makan')

@section('content')
    <div class="space-y-6 fade-in">

        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-display font-bold text-gray-900">📋 Riwayat Makan</h1>
                <p class="text-gray-500 text-sm mt-0.5">Log makanan berdasarkan tanggal</p>
            </div>

            {{-- Date Picker --}}
            <form method="GET" action="{{ route('food.history') }}" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" onchange="this.form.submit()"
                    class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            </form>
        </div>

        {{-- Date Navigation --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('food.history', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}"
                class="p-2 rounded-xl border border-gray-200 hover:bg-gray-50 transition">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <span class="text-sm font-semibold text-gray-700 px-2">
                {{ $date->translatedFormat('d F Y') }}
                @if ($date->isToday())
                    <span class="text-brand-500">(Hari ini)</span>
                @endif
            </span>
            @if (!$date->isToday())
                <a href="{{ route('food.history', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}"
                    class="p-2 rounded-xl border border-gray-200 hover:bg-gray-50 transition">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @endif
        </div>

        {{-- Summary Bar --}}
        @if ($logs->count() > 0)
            <div class="bg-brand-50 border border-brand-200 rounded-2xl p-4 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-brand-800">Total Kalori</p>
                    <p class="text-2xl font-display font-bold text-brand-700">{{ number_format($totalCalories) }} kkal</p>
                </div>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-xs text-brand-600 font-medium">Protein</p>
                        <p class="text-sm font-bold text-brand-800">{{ round($logs->sum('protein')) }}g</p>
                    </div>
                    <div>
                        <p class="text-xs text-brand-600 font-medium">Karbo</p>
                        <p class="text-sm font-bold text-brand-800">{{ round($logs->sum('carbs')) }}g</p>
                    </div>
                    <div>
                        <p class="text-xs text-brand-600 font-medium">Lemak</p>
                        <p class="text-sm font-bold text-brand-800">{{ round($logs->sum('fat')) }}g</p>
                    </div>
                </div>
            </div>

            {{-- Logs grouped by meal type --}}
            @foreach (['breakfast' => ['🌅', 'Sarapan'], 'lunch' => ['☀️', 'Makan Siang'], 'dinner' => ['🌙', 'Makan Malam'], 'snack' => ['🍎', 'Snack']] as $type => [$icon, $label])
                @php $mealLogs = $logs->where('meal_type', $type); @endphp
                @if ($mealLogs->count() > 0)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                            <span class="font-semibold text-gray-700 text-sm">{{ $icon }}
                                {{ $label }}</span>
                            <span class="text-xs text-gray-400 font-medium">{{ number_format($mealLogs->sum('calories')) }}
                                kkal</span>
                        </div>
                        <div class="divide-y divide-gray-50">
                            @foreach ($mealLogs as $log)
                                <div class="px-5 py-4 flex items-center gap-4">
                                    @if ($log->image_path)
                                        <img src="{{ $log->image_path }}"
                                            class="w-14 h-14 rounded-xl object-cover flex-shrink-0">
                                    @else
                                        <div
                                            class="w-14 h-14 rounded-xl bg-brand-50 flex items-center justify-center text-2xl flex-shrink-0">
                                            🍴</div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-800 truncate">{{ $log->food_name }}</p>
                                        @if ($log->food_description)
                                            <p class="text-xs text-gray-400 mt-0.5 line-clamp-1">
                                                {{ $log->food_description }}</p>
                                        @endif
                                        <div class="flex gap-3 mt-1">
                                            @if ($log->protein)
                                                <span class="text-xs text-gray-400">P: {{ round($log->protein) }}g</span>
                                            @endif
                                            @if ($log->carbs)
                                                <span class="text-xs text-gray-400">K: {{ round($log->carbs) }}g</span>
                                            @endif
                                            @if ($log->fat)
                                                <span class="text-xs text-gray-400">L: {{ round($log->fat) }}g</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="font-bold text-brand-600">{{ number_format($log->calories) }}</p>
                                        <p class="text-xs text-gray-400">kkal</p>
                                        <p class="text-xs text-gray-300 mt-1">{{ $log->created_at->format('H:i') }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('food.destroy', $log) }}"
                                        onsubmit="return confirm('Hapus log ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="p-2 text-gray-300 hover:text-red-400 transition rounded-lg hover:bg-red-50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                <div class="text-5xl mb-4">🗓️</div>
                <p class="font-semibold text-gray-600">Tidak ada log untuk tanggal ini</p>
                <p class="text-sm text-gray-400 mt-1">Coba pilih tanggal lain atau mulai catat makanan kamu</p>
                <a href="{{ route('dashboard') }}"
                    class="inline-block mt-4 bg-brand-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-brand-600 transition">
                    Catat Sekarang
                </a>
            </div>
        @endif

    </div>
@endsection
