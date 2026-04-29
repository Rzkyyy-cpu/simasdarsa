<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Splash Screen - SIMASDARSA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#f0fdf8',
                            100: '#ccfbef',
                            200: '#99f6df',
                            300: '#5eead4',
                            400: '#2dd4bf',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                            800: '#115e59',
                            900: '#134e4a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .splash-container {
            background: linear-gradient(135deg, #14b8a6 0%, #0f766e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-container {
            animation: fadeInUp 1.5s ease-out;
        }

        .logo-text {
            animation: fadeInUp 2s ease-out 0.5s both;
        }

        .loading-bar {
            animation: loading 2.5s ease-out 1s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes loading {
            from {
                width: 0%;
            }
            to {
                width: 100%;
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>
<body>
    <div class="splash-container">
        <div class="text-center">
            <!-- Logo SIMASDARSA -->
            <div class="logo-container mb-8">
                <div class="w-32 h-32 bg-white rounded-3xl flex items-center justify-center shadow-2xl mx-auto mb-6">
                    <svg class="w-16 h-16 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                    </svg>
                </div>
            </div>

            <!-- Text SIMASDARSA -->
            <div class="logo-text">
                <h1 class="text-4xl font-bold text-white mb-2">SIMASDARSA</h1>
                <p class="text-xl text-brand-100 mb-8">Sistem Manajemen Stok & Penjualan</p>
            </div>

            <!-- Loading Bar -->
            <div class="w-64 h-1 bg-white/20 rounded-full mx-auto mb-4">
                <div class="loading-bar h-full bg-white rounded-full"></div>
            </div>

            <!-- Loading Text -->
            <p class="text-brand-100 text-sm pulse">Memuat sistem...</p>
        </div>
    </div>

    <script>
        // Redirect to login after animation
        setTimeout(() => {
            window.location.href = '{{ route("login") }}';
        }, 3500);
    </script>
</body>
</html>