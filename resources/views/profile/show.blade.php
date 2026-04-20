@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<div class="max-w-2xl mx-auto space-y-6 fade-in">

    <div>
        <h1 class="text-2xl font-display font-bold text-gray-900">Profil Saya</h1>
        <p class="text-gray-500 text-sm mt-0.5">Kelola data diri dan target kalori kamu</p>
    </div>

    {{-- BMR INFO CARD --}}
    @if($user->bmr)
    <div class="bg-gradient-to-br from-brand-500 to-brand-600 rounded-2xl p-5 text-white">
        <p class="text-brand-100 text-sm font-medium mb-1">Estimasi BMR Kamu</p>
        <p class="text-3xl font-display font-bold">{{ number_format($user->bmr) }} <span class="text-lg font-normal text-brand-100">kkal/hari</span></p>
        <p class="text-brand-200 text-xs mt-2">Dihitung menggunakan formula Mifflin-St Jeor berdasarkan data tubuh kamu</p>
    </div>
    @endif

    {{-- PROFILE FORM --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-5">Data Diri</h2>

        @if(session('success'))
        <div class="mb-4 bg-brand-50 border border-brand-200 rounded-xl p-3 text-sm text-brand-700 font-medium">
            ✓ {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-700">
            @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="birth_date"
                           value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Jenis Kelamin</label>
                    <select name="gender"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <option value="">-- Pilih --</option>
                        <option value="male"   {{ old('gender', $user->gender) === 'male'   ? 'selected' : '' }}>Laki-laki</option>
                        <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Berat Badan (kg)</label>
                    <input type="number" name="weight" step="0.1" min="20" max="300"
                           value="{{ old('weight', $user->weight) }}"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"
                           placeholder="Contoh: 65.5">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Tinggi Badan (cm)</label>
                    <input type="number" name="height" step="0.1" min="100" max="250"
                           value="{{ old('height', $user->height) }}"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"
                           placeholder="Contoh: 170">
                </div>
            </div>

            {{-- CALORIE SETTINGS --}}
            <div class="pt-4 border-t border-gray-100">
                <h3 class="text-sm font-bold text-gray-900 mb-4">🎯 Target Kalori</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-700 block mb-1.5">Kalori Harian (kkal)</label>
                        <input type="number" name="daily_calorie_target" min="1000" max="10000"
                               value="{{ old('daily_calorie_target', $user->daily_calorie_target) }}"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700 block mb-1.5">Pangkas saat Diet (kkal)</label>
                        <input type="number" name="diet_calorie_cut" min="100" max="2000"
                               value="{{ old('diet_calorie_cut', $user->diet_calorie_cut) }}"
                               class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <p class="text-xs text-gray-400 mt-1">Target diet = kalori harian - pangkasan ini</p>
                    </div>
                </div>

                {{-- Diet Mode Toggle --}}
                <div class="mt-4 flex items-center justify-between p-4 bg-orange-50 border border-orange-200 rounded-xl">
                    <div>
                        <p class="text-sm font-bold text-orange-800">Mode Diet</p>
                        <p class="text-xs text-orange-600 mt-0.5">
                            Target aktif:
                            <strong>
                                @if($user->diet_mode)
                                    {{ number_format($user->effective_calorie_target) }} kkal
                                @else
                                    {{ number_format($user->daily_calorie_target) }} kkal (normal)
                                @endif
                            </strong>
                        </p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="diet_mode" value="0">
                        <input type="checkbox" name="diet_mode" value="1" class="sr-only peer"
                               {{ $user->diet_mode ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-orange-300
                                    rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white
                                    after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white
                                    after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5
                                    after:transition-all peer-checked:bg-orange-500"></div>
                    </label>
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-brand-500 hover:bg-brand-600 text-white font-semibold py-3 rounded-xl text-sm transition">
                Simpan Perubahan
            </button>
        </form>
    </div>

    {{-- CHANGE PASSWORD --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-5">🔒 Ubah Password</h2>

        <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="text-sm font-semibold text-gray-700 block mb-1.5">Password Lama</label>
                <input type="password" name="current_password" required
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                @error('current_password')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Password Baru</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                </div>
            </div>
            <button type="submit"
                    class="w-full border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold py-3 rounded-xl text-sm transition">
                Ubah Password
            </button>
        </form>
    </div>

</div>
@endsection
