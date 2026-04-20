<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — VibeFitt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .bg-pattern { background-image: radial-gradient(circle at 1px 1px, #d1fae5 1px, transparent 0); background-size: 28px 28px; }</style>
</head>
<body class="h-full bg-gray-50 bg-pattern flex items-center justify-center p-4 py-10">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-green-500 rounded-2xl mb-4 shadow-lg shadow-green-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <h1 style="font-family:'Sora',sans-serif" class="text-3xl font-extrabold text-gray-900">VibeFit</h1>
            <p class="text-gray-500 mt-1 text-sm">Mulai perjalanan diet sehat kamu</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl shadow-gray-100 p-8 border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Buat akun baru</h2>

            @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $e)
                    <p>• {{ $e }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-green-400 text-sm transition"
                           placeholder="Nama kamu">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-green-400 text-sm transition"
                           placeholder="kamu@email.com">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-green-400 text-sm transition"
                           placeholder="Minimal 8 karakter">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-green-400 text-sm transition"
                           placeholder="Ulangi password">
                </div>
                <button type="submit"
                        class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-3 rounded-xl transition text-sm shadow-lg shadow-green-100">
                    Daftar Sekarang
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-green-600 font-semibold hover:underline">Masuk di sini</a>
            </p>
        </div>
    </div>
</body>
</html>
