<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'HKBP Persiapan Resort Volker' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased selection:bg-hkbp-800 selection:text-white">

    <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-sm transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <a href="/" class="flex items-center gap-3 group">
                    <div class="w-12 h-12 bg-hkbp-800 rounded-xl flex items-center justify-center text-gold-400 shadow-md group-hover:bg-hkbp-900 transition-all border border-blue-400/30">
                        <i data-lucide="cross" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <div class="font-black text-xl text-hkbp-900 leading-tight tracking-tight">HKBP Volker</div>
                        <div class="text-[11px] font-bold text-hkbp-600 tracking-wider">PERSIAPAN RESORT VOLKER</div>
                    </div>
                </a>

                <div class="hidden md:flex items-center space-x-1 lg:space-x-2 text-sm font-semibold text-slate-700">
                    <a href="/" class="px-3 py-2 rounded-lg text-slate-600 hover:text-hkbp-900 font-semibold transition">Beranda</a>
                    <a href="/profil" class="px-3 py-2 rounded-lg text-slate-600 hover:text-hkbp-900 font-semibold transition">Profil</a>
                    <a href="/pelayan" class="px-3 py-2 rounded-lg text-slate-600 hover:text-hkbp-900 font-semibold transition">Pelayan</a>
                    <a href="/renungan" class="px-3 py-2 rounded-lg text-slate-600 hover:text-hkbp-900 font-semibold transition">Renungan</a>
                    <a href="/galeri" class="px-3 py-2 rounded-lg text-slate-600 hover:text-hkbp-900 font-semibold transition">Galeri</a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        {{ $slot }}
    </main>

    <footer id="kontak" class="bg-hkbp-950 text-blue-50 pt-16 pb-8 border-t border-hkbp-900 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-10 mb-12">
                
                <div class="space-y-4">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-hkbp-800 rounded-lg flex items-center justify-center text-gold-400">
                            <i data-lucide="cross" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white leading-tight">HKBP Volker</h3>
                    </div>
                    <p class="text-sm text-blue-200 leading-relaxed">Menjadi Gereja yang Inklusif, Dialogis, dan Menjadi Berkat bagi seluruh ciptaan.</p>
                    <div class="pt-4 space-y-3">
                        <div class="flex items-start gap-3 text-sm text-blue-200">
                            <i data-lucide="map-pin" class="w-5 h-5 text-gold-400 shrink-0"></i> 
                            <span>Jl. Volker Raya No. 1, RT 01/RW 02, Tanjung Priok, Jakarta Utara</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-blue-200">
                            <i data-lucide="phone" class="w-5 h-5 text-gold-400 shrink-0"></i> 
                            <span>(021) 12345678</span>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 rounded-2xl overflow-hidden h-64 border border-hkbp-800 shadow-inner">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3967.04278453448!2d106.87702831411586!3d-6.12493399556531!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6a1ff5a4321303%3A0xc48c1488c5750d5f!2sTanjung%20Priok%2C%20North%20Jakarta%20City%2C%20Jakarta!5e0!3m2!1sen!2sid!4v1620000000000!5m2!1sen!2sid" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>

            </div>
            
            <div class="text-center text-xs text-blue-400/60 border-t border-hkbp-900 pt-8">
                &copy; 2026 HKBP Persiapan Resort Volker. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>