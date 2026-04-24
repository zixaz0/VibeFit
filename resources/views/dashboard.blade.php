@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6 fade-in">

    {{-- HEADER ROW --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-display font-bold text-gray-900">
                Halo, {{ Str::words($user->name, 1, '') }}! 👋
            </h1>
            <p class="text-gray-500 text-sm mt-0.5">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        @if($user->diet_mode)
        <span class="inline-flex items-center gap-1.5 bg-orange-100 text-orange-700 text-xs font-semibold px-3 py-1.5 rounded-full">
            <span class="w-2 h-2 bg-orange-500 rounded-full pulse-green"></span>
            Mode Diet Aktif
        </span>
        @endif
    </div>

    {{-- CALORIE SUMMARY CARD --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="text-sm font-medium text-gray-500">Kalori Hari Ini</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-4xl font-display font-bold {{ $todayCalories > $target ? 'text-red-500' : 'text-gray-900' }}">
                        {{ number_format($todayCalories) }}
                    </span>
                    <span class="text-gray-400 text-lg">/ {{ number_format($target) }} kkal</span>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm font-medium text-gray-500">Sisa</p>
                <p class="text-2xl font-display font-bold {{ $remaining === 0 ? 'text-red-500' : 'text-brand-600' }}">
                    {{ number_format($remaining) }}
                    <span class="text-sm font-sans font-normal text-gray-400">kkal</span>
                </p>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="h-4 bg-gray-100 rounded-full overflow-hidden">
            <div class="progress-bar h-full rounded-full {{ $percentage >= 100 ? 'bg-red-400' : ($percentage >= 80 ? 'bg-orange-400' : 'bg-brand-500') }}"
                 style="width: {{ $percentage }}%"></div>
        </div>
        <div class="flex justify-between mt-1.5">
            <span class="text-xs text-gray-400">0 kkal</span>
            <span class="text-xs font-medium {{ $percentage >= 100 ? 'text-red-500' : 'text-gray-500' }}">
                {{ $percentage }}% dari target
                @if($user->diet_mode) (diet -{{ number_format($user->diet_calorie_cut) }} kkal)@endif
            </span>
            <span class="text-xs text-gray-400">{{ number_format($target) }} kkal</span>
        </div>

        {{-- Meal Breakdown --}}
        @if($todayLogs->count() > 0)
        <div class="mt-5 pt-5 border-t border-gray-100 grid grid-cols-4 gap-3">
            @foreach(['breakfast' => ['🌅','Sarapan'], 'lunch' => ['☀️','Siang'], 'dinner' => ['🌙','Malam'], 'snack' => ['🍎','Snack']] as $type => $info)
            <div class="text-center">
                <div class="text-lg mb-0.5">{{ $info[0] }}</div>
                <p class="text-xs text-gray-400">{{ $info[1] }}</p>
                <p class="text-sm font-semibold text-gray-700">{{ number_format($breakdown[$type] ?? 0) }}</p>
                <p class="text-xs text-gray-400">kkal</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- MAIN GRID: Upload + Weekly Chart --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- UPLOAD PHOTO CARD --}}
        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">📸 Tambah Makanan</h2>

            {{-- STEP 1: Upload Zone --}}
            <div id="step-upload">
                <div id="upload-zone"
                     class="upload-zone border-2 border-dashed border-gray-200 rounded-xl p-8 text-center cursor-pointer hover:border-brand-400 hover:bg-brand-50 transition"
                     onclick="openPhotoSheet()">
                    <div id="preview-wrap" class="hidden">
                        <img id="preview-img" src="" alt="preview" class="max-h-48 mx-auto rounded-xl object-cover mb-3">
                    </div>
                    <div id="upload-placeholder">
                        <div class="w-14 h-14 bg-brand-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <p class="font-semibold text-gray-700 text-sm">Klik atau drag foto makanan</p>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP — maks 10MB</p>
                    </div>
                </div>

                <input type="file" id="food-image-camera" accept="image/*" capture="environment" class="hidden">
                <input type="file" id="food-image-gallery" accept="image/*" class="hidden">

                {{-- Food Hint --}}
                <div id="hint-wrap" class="hidden mt-4">
                    <label class="text-xs font-semibold text-gray-600 block mb-1.5">
                        💡 Kasih tau AI ini makanan apa
                        <span class="font-normal text-gray-400">(opsional, tapi bikin hasil lebih akurat)</span>
                    </label>
                    <div class="relative">
                        <input type="text" id="food-hint"
                               placeholder="contoh: kentang goreng, nasi padang, mie ayam..."
                               maxlength="100"
                               class="w-full pl-3 pr-10 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 placeholder-gray-300">
                        <button type="button" id="hint-clear" onclick="clearHint()"
                                class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1.5">
                        ✏️ Kalau AI salah deteksi (misal ubi padahal kentang), tulis nama makanannya di sini sebelum analisis.
                    </p>
                </div>

                <button id="btn-analyze" onclick="analyzeImage()"
                        class="hidden mt-4 w-full bg-brand-500 hover:bg-brand-600 text-white font-semibold py-3 rounded-xl text-sm transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347A3.52 3.52 0 0014 15.119V16a2 2 0 01-2 2 2 2 0 01-2-2v-.881a3.52 3.52 0 00-1.017-2.472l-.346-.346z"/>
                    </svg>
                    <span id="btn-analyze-label">Analisis dengan AI</span>
                </button>

                <div id="loading" class="hidden mt-4 flex flex-col items-center gap-3 py-4">
                    <div class="w-10 h-10 border-4 border-brand-200 border-t-brand-500 rounded-full spin"></div>
                    <p class="text-sm text-gray-500 font-medium" id="loading-text">AI sedang menganalisis foto...</p>
                </div>
            </div>

            {{-- STEP 2: Confirm & Save --}}
            <div id="step-confirm" class="hidden">
                <div id="result-card" class="bg-brand-50 border border-brand-200 rounded-xl p-4 mb-4">
                    <div class="flex items-start gap-3">
                        <img id="result-img" src="" class="w-16 h-16 rounded-xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900" id="result-name">-</p>
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2" id="result-desc">-</p>
                            <div class="flex gap-3 mt-2 flex-wrap">
                                <span class="text-brand-700 font-bold text-sm" id="result-cal">0 kkal</span>
                                <span class="text-xs text-gray-500">P: <span id="result-protein">0</span>g</span>
                                <span class="text-xs text-gray-500">K: <span id="result-carbs">0</span>g</span>
                                <span class="text-xs text-gray-500">L: <span id="result-fat">0</span>g</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 italic" id="result-notes"></p>
                </div>

                <form method="POST" action="{{ route('food.store') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="temp_path" id="f-temp-path">
                    <input type="hidden" name="ai_analysis" id="f-ai-analysis">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">Nama Makanan</label>
                            <input type="text" name="food_name" id="f-name" required
                                   class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">Kalori (kkal)</label>
                            <input type="number" name="calories" id="f-calories" required min="0"
                                   class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">Protein (g)</label>
                            <input type="number" name="protein" id="f-protein" min="0" step="0.1"
                                   class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">Karbo (g)</label>
                            <input type="number" name="carbs" id="f-carbs" min="0" step="0.1"
                                   class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">Lemak (g)</label>
                            <input type="number" name="fat" id="f-fat" min="0" step="0.1"
                                   class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">Waktu Makan</label>
                            <select name="meal_type"
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                                <option value="breakfast">🌅 Sarapan</option>
                                <option value="lunch">☀️ Makan Siang</option>
                                <option value="dinner">🌙 Makan Malam</option>
                                <option value="snack" selected>🍎 Snack</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 block mb-1">Tanggal</label>
                            <input type="date" name="logged_date" value="{{ today()->format('Y-m-d') }}"
                                   class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        </div>
                    </div>
                    <textarea name="food_description" id="f-desc" rows="2" placeholder="Deskripsi (opsional)"
                              class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 resize-none"></textarea>
                    <div class="flex gap-3">
                        <button type="button" onclick="resetUpload()"
                                class="flex-1 border border-gray-200 text-gray-600 font-semibold py-3 rounded-xl text-sm hover:bg-gray-50 transition">
                            ← Ulang
                        </button>
                        <button type="submit"
                                class="flex-1 bg-brand-500 hover:bg-brand-600 text-white font-semibold py-3 rounded-xl text-sm transition">
                            Simpan Log ✓
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- WEEKLY BAR CHART --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col">
            <div class="flex items-start justify-between mb-1">
                <h2 class="text-lg font-bold text-gray-900">📊 7 Hari Terakhir</h2>
            </div>
            <p class="text-xs text-gray-400 mb-5">Target: <strong class="text-gray-600">{{ number_format($target) }} kkal</strong>/hari</p>
            <div class="relative flex-1" style="min-height: 180px;">
                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none pb-7">
                    @php $guideTarget = $target > 0 ? $target : 2000; @endphp
                    @foreach([100, 75, 50, 25, 0] as $pctLine)
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] text-gray-300 w-8 text-right flex-shrink-0">
                            {{ $pctLine > 0 ? number_format(round($guideTarget * $pctLine / 100)) : '0' }}
                        </span>
                        <div class="flex-1 border-t border-dashed border-gray-100"></div>
                    </div>
                    @endforeach
                </div>
                <div class="absolute inset-0 flex items-end gap-1.5 pl-10 pb-7">
                    @php $maxCal = max(collect($weekly)->pluck('calories')->max(), $target * 1.1, 100); @endphp
                    @foreach($weekly as $day)
                    @php
                        $pct = $maxCal > 0 ? min(100, ($day['calories'] / $maxCal) * 100) : 0;
                        $isToday = $day['full_date'] === today()->format('Y-m-d');
                        $isOver  = $day['calories'] >= $target && $target > 0;
                        $isEmpty = $day['calories'] === 0;
                        if ($isOver)       { $barColor = 'bg-red-400';    $barColorHover = 'hover:bg-red-500'; }
                        elseif ($isToday)  { $barColor = 'bg-brand-500';  $barColorHover = 'hover:bg-brand-600'; }
                        else               { $barColor = $isEmpty ? 'bg-gray-100' : 'bg-brand-300'; $barColorHover = $isEmpty ? '' : 'hover:bg-brand-400'; }
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1 h-full justify-end group relative"
                         title="{{ $day['date'] }}: {{ number_format($day['calories']) }} kkal">
                        <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-150 z-10 pointer-events-none">
                            <div class="bg-gray-800 text-white text-[10px] font-medium px-2 py-1 rounded-lg whitespace-nowrap shadow-lg">
                                {{ number_format($day['calories']) }} kkal
                                @if($isToday)<br><span class="text-brand-300">Hari ini</span>@endif
                            </div>
                            <div class="w-2 h-2 bg-gray-800 rotate-45 mx-auto -mt-1"></div>
                        </div>
                        <div class="w-full rounded-t-lg transition-all duration-700 ease-out {{ $barColor }} {{ $barColorHover }} cursor-default"
                             style="height: {{ max($pct, $isEmpty ? 2 : 4) }}%; min-height: {{ $isEmpty ? '2px' : '4px' }};"></div>
                    </div>
                    @endforeach
                </div>
                @php $targetLinePct = isset($maxCal) && $maxCal > 0 ? min(100, ($target / $maxCal) * 100) : 0; @endphp
                <div class="absolute left-10 right-0 pointer-events-none"
                     style="bottom: calc({{ $targetLinePct }}% + 1.75rem);">
                    <div class="border-t-2 border-dashed border-orange-400 w-full relative">
                        <span class="absolute -top-4 right-0 text-[9px] font-semibold text-orange-500 bg-orange-50 px-1.5 py-0.5 rounded">Target</span>
                    </div>
                </div>
                <div class="absolute bottom-0 left-10 right-0 flex gap-1.5">
                    @foreach($weekly as $day)
                    @php $isToday = $day['full_date'] === today()->format('Y-m-d'); @endphp
                    <div class="flex-1 text-center">
                        <span class="text-[10px] font-medium {{ $isToday ? 'text-brand-600' : 'text-gray-400' }} block leading-tight">
                            {{ $day['short_day'] ?? \Carbon\Carbon::parse($day['full_date'])->locale('id')->isoFormat('dd') }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-4 flex-wrap">
                <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-sm bg-brand-500"></div><span class="text-[11px] text-gray-500">Hari ini</span></div>
                <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-sm bg-brand-300"></div><span class="text-[11px] text-gray-500">Normal</span></div>
                <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded-sm bg-red-400"></div><span class="text-[11px] text-gray-500">Melebihi target</span></div>
                <div class="flex items-center gap-1.5"><div class="w-4 h-0 border-t-2 border-dashed border-orange-400"></div><span class="text-[11px] text-gray-500">Target</span></div>
            </div>
        </div>
    </div>

    {{-- TODAY'S LOG LIST --}}
    @if($todayLogs->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">🍽️ Log Hari Ini</h2>
            <a href="{{ route('food.history') }}" class="text-sm text-brand-600 font-semibold hover:underline">Lihat semua →</a>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($todayLogs as $log)
            <div class="py-3 flex items-center gap-4">
                @if($log->image_path)
                <img src="{{ $log->image_path }}"
                     class="w-12 h-12 rounded-xl object-cover flex-shrink-0 cursor-pointer hover:opacity-80 transition"
                     onclick="openDetail({{ $log->id }})">
                @else
                <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center text-xl flex-shrink-0 cursor-pointer hover:bg-brand-100 transition"
                     onclick="openDetail({{ $log->id }})">🍴</div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 text-sm truncate">{{ $log->food_name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $log->meal_type_label }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="font-bold text-brand-600 text-sm">{{ number_format($log->calories) }} kkal</p>
                    @if($log->protein)
                    <p class="text-xs text-gray-400">P{{ round($log->protein) }}g K{{ round($log->carbs) }}g L{{ round($log->fat) }}g</p>
                    @endif
                </div>
                {{-- Tombol Detail --}}
                <button onclick="openDetail({{ $log->id }})"
                        class="p-2 text-gray-300 hover:text-brand-500 transition rounded-lg hover:bg-brand-50 flex-shrink-0"
                        title="Lihat detail">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </button>
                <form method="POST" action="{{ route('food.destroy', $log) }}" class="flex-shrink-0"
                      onsubmit="return confirm('Hapus log ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-2 text-gray-300 hover:text-red-400 transition rounded-lg hover:bg-red-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center text-gray-400">
        <div class="text-4xl mb-3">🍽️</div>
        <p class="font-medium text-gray-500">Belum ada log makanan hari ini</p>
        <p class="text-sm mt-1">Upload foto makanan kamu di atas!</p>
    </div>
    @endif

</div>

{{-- ============================================================ --}}
{{-- DETAIL POPUP                                                 --}}
{{-- ============================================================ --}}
<div id="detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeDetail()"></div>
    <div id="detail-panel"
         class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-auto overflow-hidden
                transform transition-all duration-200 scale-95 opacity-0">

        {{-- Foto --}}
        <div id="detail-img-wrap" class="hidden">
            <img id="detail-img" src="" alt="" class="w-full h-52 object-cover">
        </div>
        <div id="detail-no-img" class="w-full h-32 bg-brand-50 flex items-center justify-center text-5xl hidden">🍴</div>

        {{-- Close --}}
        <button onclick="closeDetail()"
                class="absolute top-3 right-3 w-8 h-8 bg-black/30 hover:bg-black/50 text-white rounded-full flex items-center justify-center transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Content --}}
        <div class="p-5">
            <div class="flex items-start justify-between gap-2 mb-1">
                <h3 class="font-bold text-gray-900 text-lg leading-tight" id="detail-name">-</h3>
                <span class="text-xs text-gray-400 flex-shrink-0 mt-1" id="detail-time">-</span>
            </div>
            <p class="text-xs text-gray-400 mb-4" id="detail-meal-type">-</p>

            {{-- Kalori --}}
            <div class="bg-brand-50 rounded-xl p-4 mb-4 text-center">
                <p class="text-3xl font-display font-bold text-brand-600" id="detail-cal">0</p>
                <p class="text-xs text-brand-400 mt-0.5">kkal</p>
            </div>

            {{-- Makro --}}
            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="text-center bg-blue-50 rounded-xl p-3">
                    <p class="text-lg font-bold text-blue-600" id="detail-protein">0</p>
                    <p class="text-[11px] text-blue-400 mt-0.5">Protein (g)</p>
                </div>
                <div class="text-center bg-amber-50 rounded-xl p-3">
                    <p class="text-lg font-bold text-amber-600" id="detail-carbs">0</p>
                    <p class="text-[11px] text-amber-400 mt-0.5">Karbo (g)</p>
                </div>
                <div class="text-center bg-rose-50 rounded-xl p-3">
                    <p class="text-lg font-bold text-rose-600" id="detail-fat">0</p>
                    <p class="text-[11px] text-rose-400 mt-0.5">Lemak (g)</p>
                </div>
            </div>

            {{-- Deskripsi --}}
            <div id="detail-desc-wrap" class="hidden mb-3">
                <p class="text-xs font-semibold text-gray-500 mb-1">Deskripsi</p>
                <p class="text-sm text-gray-600" id="detail-desc"></p>
            </div>

            {{-- Notes AI --}}
            <div id="detail-notes-wrap" class="hidden">
                <p class="text-xs font-semibold text-gray-500 mb-1">📝 Catatan AI</p>
                <p class="text-xs text-gray-400 italic" id="detail-notes"></p>
            </div>
        </div>
    </div>
</div>

{{-- Data logs untuk JS --}}
@if($todayLogs->count() > 0)
<script>
    const logsData = {
        @foreach($todayLogs as $log)
        {{ $log->id }}: {
            food_name:        @json($log->food_name),
            food_description: @json($log->food_description),
            calories:         {{ $log->calories }},
            protein:          {{ $log->protein ?? 0 }},
            carbs:            {{ $log->carbs ?? 0 }},
            fat:              {{ $log->fat ?? 0 }},
            meal_type_label:  @json($log->meal_type_label),
            image_path:       @json($log->image_path),
            created_at:       @json($log->created_at->format('H:i')),
            ai_notes:         @json(optional(json_decode($log->ai_analysis))->notes ?? ''),
        },
        @endforeach
    };
</script>
@endif

{{-- MOBILE PHOTO PICKER — BOTTOM SHEET --}}
<div id="photo-sheet" class="fixed inset-0 z-50 hidden" style="touch-action: none;">
    <div id="sheet-backdrop"
         class="absolute inset-0 bg-black/50 opacity-0 transition-opacity duration-300"
         onclick="closePhotoSheet()"></div>
    <div id="sheet-panel"
         class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl shadow-2xl"
         style="transform: translateY(100%); transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1); padding-bottom: env(safe-area-inset-bottom, 0px);">
        <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
        </div>
        <div class="px-5 pb-5 pt-3">
            <p class="text-sm font-semibold text-gray-700 mb-4 text-center">Pilih sumber foto</p>
            <div class="grid grid-cols-2 gap-3 mb-3">
                <button onclick="pickSource('camera')"
                        class="flex flex-col items-center gap-3 p-5 rounded-2xl border-2 border-gray-100 hover:border-brand-300 hover:bg-brand-50 active:scale-95 transition-all duration-150">
                    <div class="w-14 h-14 bg-brand-50 rounded-2xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div><p class="text-sm font-bold text-gray-800">Kamera</p><p class="text-xs text-gray-400 mt-0.5">Foto langsung</p></div>
                </button>
                <button onclick="pickSource('gallery')"
                        class="flex flex-col items-center gap-3 p-5 rounded-2xl border-2 border-gray-100 hover:border-brand-300 hover:bg-brand-50 active:scale-95 transition-all duration-150">
                    <div class="w-14 h-14 bg-brand-50 rounded-2xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div><p class="text-sm font-bold text-gray-800">Galeri</p><p class="text-xs text-gray-400 mt-0.5">Dari foto tersimpan</p></div>
                </button>
            </div>
            <button onclick="closePhotoSheet()"
                    class="w-full py-3.5 text-sm font-semibold text-gray-500 hover:text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-xl transition active:scale-95">
                Batal
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let selectedFile = null;
    let previewSrc   = null;
    const isMobile   = (window.matchMedia('(pointer: coarse)').matches || /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent));

    // ─── Detail Popup ─────────────────────────────────────────────
    function openDetail(id) {
        const log = (typeof logsData !== 'undefined') ? logsData[id] : null;
        if (!log) return;

        const modal = document.getElementById('detail-modal');
        const panel = document.getElementById('detail-panel');

        document.getElementById('detail-name').textContent      = log.food_name || '-';
        document.getElementById('detail-time').textContent      = log.created_at || '';
        document.getElementById('detail-meal-type').textContent = log.meal_type_label || '';
        document.getElementById('detail-cal').textContent       = log.calories || 0;
        document.getElementById('detail-protein').textContent   = log.protein || 0;
        document.getElementById('detail-carbs').textContent     = log.carbs || 0;
        document.getElementById('detail-fat').textContent       = log.fat || 0;

        if (log.image_path) {
            document.getElementById('detail-img').src = log.image_path;
            document.getElementById('detail-img-wrap').classList.remove('hidden');
            document.getElementById('detail-no-img').classList.add('hidden');
        } else {
            document.getElementById('detail-img-wrap').classList.add('hidden');
            document.getElementById('detail-no-img').classList.remove('hidden');
        }

        if (log.food_description) {
            document.getElementById('detail-desc').textContent = log.food_description;
            document.getElementById('detail-desc-wrap').classList.remove('hidden');
        } else {
            document.getElementById('detail-desc-wrap').classList.add('hidden');
        }

        if (log.ai_notes) {
            document.getElementById('detail-notes').textContent = log.ai_notes;
            document.getElementById('detail-notes-wrap').classList.remove('hidden');
        } else {
            document.getElementById('detail-notes-wrap').classList.add('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => {
            panel.classList.remove('scale-95', 'opacity-0');
            panel.classList.add('scale-100', 'opacity-100');
        });
    }

    function closeDetail() {
        const modal = document.getElementById('detail-modal');
        const panel = document.getElementById('detail-panel');
        panel.classList.remove('scale-100', 'opacity-100');
        panel.classList.add('scale-95', 'opacity-0');
        document.body.style.overflow = '';
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDetail(); });

    // ─── Bottom Sheet ─────────────────────────────────────────────
    function openPhotoSheet() {
        if (!isMobile) { document.getElementById('food-image-gallery').click(); return; }
        const sheet    = document.getElementById('photo-sheet');
        const panel    = document.getElementById('sheet-panel');
        const backdrop = document.getElementById('sheet-backdrop');
        sheet.classList.remove('hidden');
        sheet.offsetHeight;
        panel.style.transform  = 'translateY(0)';
        backdrop.style.opacity = '1';
        document.body.style.overflow = 'hidden';
    }

    function closePhotoSheet() {
        const sheet    = document.getElementById('photo-sheet');
        const panel    = document.getElementById('sheet-panel');
        const backdrop = document.getElementById('sheet-backdrop');
        panel.style.transform  = 'translateY(100%)';
        backdrop.style.opacity = '0';
        document.body.style.overflow = '';
        setTimeout(() => sheet.classList.add('hidden'), 350);
    }

    function pickSource(source) {
        closePhotoSheet();
        setTimeout(() => {
            document.getElementById(source === 'camera' ? 'food-image-camera' : 'food-image-gallery').click();
        }, 380);
    }

    (function() {
        const panel = document.getElementById('sheet-panel');
        let startY = 0, currentY = 0, dragging = false;
        panel.addEventListener('touchstart', e => { startY = e.touches[0].clientY; dragging = true; }, { passive: true });
        panel.addEventListener('touchmove', e => {
            if (!dragging) return;
            currentY = e.touches[0].clientY;
            const delta = Math.max(0, currentY - startY);
            panel.style.transition = 'none';
            panel.style.transform  = `translateY(${delta}px)`;
        }, { passive: true });
        panel.addEventListener('touchend', () => {
            dragging = false;
            const delta = currentY - startY;
            panel.style.transition = 'transform 0.35s cubic-bezier(0.32, 0.72, 0, 1)';
            if (delta > 80) closePhotoSheet(); else panel.style.transform = 'translateY(0)';
        });
    })();

    // ─── File Handling ────────────────────────────────────────────
    function handleFileSelected(file) {
        if (!file) return;
        selectedFile = file;
        previewSrc   = URL.createObjectURL(file);
        document.getElementById('preview-img').src = previewSrc;
        document.getElementById('preview-wrap').classList.remove('hidden');
        document.getElementById('upload-placeholder').classList.add('hidden');
        document.getElementById('hint-wrap').classList.remove('hidden');
        document.getElementById('btn-analyze').classList.remove('hidden');
        document.getElementById('btn-analyze').classList.add('flex');
    }

    document.getElementById('food-image-camera').addEventListener('change', function(e) { handleFileSelected(e.target.files[0]); });
    document.getElementById('food-image-gallery').addEventListener('change', function(e) { handleFileSelected(e.target.files[0]); });

    document.getElementById('food-hint').addEventListener('input', function() {
        const clearBtn = document.getElementById('hint-clear');
        const label    = document.getElementById('btn-analyze-label');
        if (this.value.trim()) {
            clearBtn.classList.remove('hidden');
            label.textContent = 'Analisis "' + this.value.trim().substring(0, 20) + (this.value.trim().length > 20 ? '…' : '') + '"';
        } else {
            clearBtn.classList.add('hidden');
            label.textContent = 'Analisis dengan AI';
        }
    });

    function clearHint() {
        document.getElementById('food-hint').value = '';
        document.getElementById('hint-clear').classList.add('hidden');
        document.getElementById('btn-analyze-label').textContent = 'Analisis dengan AI';
        document.getElementById('food-hint').focus();
    }

    const zone = document.getElementById('upload-zone');
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) handleFileSelected(file);
    });

    async function analyzeImage() {
        if (!selectedFile) return;
        const foodHint = document.getElementById('food-hint').value.trim();
        document.getElementById('btn-analyze').classList.add('hidden');
        document.getElementById('btn-analyze').classList.remove('flex');
        document.getElementById('hint-wrap').classList.add('hidden');
        document.getElementById('loading-text').textContent = foodHint
            ? `AI sedang menganalisis "${foodHint}"...`
            : 'AI sedang menganalisis foto...';
        document.getElementById('loading').classList.remove('hidden');
        document.getElementById('loading').classList.add('flex');

        const formData = new FormData();
        formData.append('image', selectedFile);
        formData.append('_token', csrfToken);
        formData.append('food_hint', foodHint);

        try {
            const res  = await fetch('{{ route("food.analyze") }}', { method: 'POST', body: formData });
            const data = await res.json();
            if (!data.success) throw new Error('Analisis gagal');

            const a = data.analysis;
            document.getElementById('result-name').textContent    = a.food_name   || '-';
            document.getElementById('result-desc').textContent    = a.description || '';
            document.getElementById('result-cal').textContent     = (a.calories || 0) + ' kkal';
            document.getElementById('result-protein').textContent = a.protein || 0;
            document.getElementById('result-carbs').textContent   = a.carbs   || 0;
            document.getElementById('result-fat').textContent     = a.fat     || 0;
            document.getElementById('result-notes').textContent   = a.notes   || '';
            document.getElementById('result-img').src             = previewSrc;
            document.getElementById('f-name').value        = a.food_name   || '';
            document.getElementById('f-calories').value    = a.calories    || 0;
            document.getElementById('f-protein').value     = a.protein     || 0;
            document.getElementById('f-carbs').value       = a.carbs       || 0;
            document.getElementById('f-fat').value         = a.fat         || 0;
            document.getElementById('f-desc').value        = a.description || '';
            document.getElementById('f-temp-path').value   = data.temp_path || '';
            document.getElementById('f-ai-analysis').value = JSON.stringify(a);

            document.getElementById('loading').classList.add('hidden');
            document.getElementById('loading').classList.remove('flex');
            document.getElementById('step-confirm').classList.remove('hidden');
        } catch (err) {
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('loading').classList.remove('flex');
            document.getElementById('hint-wrap').classList.remove('hidden');
            document.getElementById('btn-analyze').classList.remove('hidden');
            document.getElementById('btn-analyze').classList.add('flex');
            alert('Gagal menganalisis gambar. Coba lagi.');
            console.error(err);
        }
    }

    function resetUpload() {
        selectedFile = null;
        previewSrc   = null;
        document.getElementById('food-image-camera').value  = '';
        document.getElementById('food-image-gallery').value = '';
        document.getElementById('preview-img').src          = '';
        document.getElementById('preview-wrap').classList.add('hidden');
        document.getElementById('upload-placeholder').classList.remove('hidden');
        document.getElementById('food-hint').value = '';
        document.getElementById('hint-clear').classList.add('hidden');
        document.getElementById('hint-wrap').classList.add('hidden');
        document.getElementById('btn-analyze-label').textContent = 'Analisis dengan AI';
        document.getElementById('btn-analyze').classList.add('hidden');
        document.getElementById('btn-analyze').classList.remove('flex');
        document.getElementById('step-confirm').classList.add('hidden');
    }
</script>
@endpush
@endsection