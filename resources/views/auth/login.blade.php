<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SIMASDARSA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#f0fdf8', 100: '#ccfbef', 200: '#99f6df', 300: '#5eead4', 400: '#2dd4bf',
                            500: '#14b8a6', 600: '#0d9488', 700: '#0f766e', 800: '#115e59', 900: '#134e4a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .login-bg { background: linear-gradient(135deg, #134e4a 0%, #14b8a6 100%); }
        .form-container { animation: slideIn 0.6s ease-out; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="login-bg min-h-screen flex items-center justify-center p-4">
    <div class="form-container max-w-md w-full">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-white rounded-3xl flex items-center justify-center shadow-2xl mx-auto mb-4">
                <svg class="w-12 h-12 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
            </div>
            <h1 class="text-3xl font-black text-white tracking-tight">SIMASDARSA</h1>
            <p class="text-brand-100 font-medium">Sistem Manajemen Stok & Penjualan</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-3xl shadow-2xl p-8 border border-white/20">
            <h2 class="text-2xl font-bold text-gray-800 text-center mb-8">Selamat Datang</h2>

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl">
                    <ul class="text-red-700 text-sm font-medium">
                        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.authenticate') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all outline-none" placeholder="name@company.com">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all outline-none" placeholder="••••••••">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Pilih Peran Akses</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex flex-col p-3 border-2 border-gray-100 rounded-2xl cursor-pointer hover:bg-brand-50 transition-all has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50 group">
                            <input type="radio" name="sub_role" value="pimpinan" class="sr-only" required>
                            <span class="text-sm font-bold text-gray-700 group-has-[:checked]:text-brand-700">Pimpinan</span>
                            <span class="text-[10px] text-gray-400">Executive</span>
                        </label>
                        <label class="relative flex flex-col p-3 border-2 border-gray-100 rounded-2xl cursor-pointer hover:bg-brand-50 transition-all has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50 group">
                            <input type="radio" name="sub_role" value="manager" class="sr-only" required>
                            <span class="text-sm font-bold text-gray-700 group-has-[:checked]:text-brand-700">Manager</span>
                            <span class="text-[10px] text-gray-400">Operational</span>
                        </label>
                        <label class="relative flex flex-col p-3 border-2 border-gray-100 rounded-2xl cursor-pointer hover:bg-brand-50 transition-all has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50 group">
                            <input type="radio" name="sub_role" value="kasir" class="sr-only" required>
                            <span class="text-sm font-bold text-gray-700 group-has-[:checked]:text-brand-700">Kasir</span>
                            <span class="text-[10px] text-gray-400">Cashier/POS</span>
                        </label>
                        <label class="relative flex flex-col p-3 border-2 border-gray-100 rounded-2xl cursor-pointer hover:bg-brand-50 transition-all has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50 group">
                            <input type="radio" name="sub_role" value="tim_it" class="sr-only" required>
                            <span class="text-sm font-bold text-gray-700 group-has-[:checked]:text-brand-700">Tim IT</span>
                            <span class="text-[10px] text-gray-400">Sys Admin</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-4 rounded-2xl transition-all shadow-lg hover:shadow-brand-200 active:scale-[0.98]">
                    Masuk ke Dashboard
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-xs text-gray-400 font-medium tracking-wide uppercase">&copy; 2026 SIMASDARSA System</p>
            </div>
        </div>
    </div>
</body>
</html>
