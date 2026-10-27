<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — SIM-MBG SPPG Sungai Selayur 1</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Urbanist', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #071739;
            color: #F8FAFC;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6 bg-gradient-to-br from-[#040C1E] via-[#071739] to-[#0A2556]">
    <div class="w-full max-w-lg text-center">
        <!-- Brand Badge -->
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-xs font-bold mb-6">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            SPPG Sungai Selayur 1 &bull; Badan Gizi Nasional (BGN)
        </div>

        <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8 sm:p-10 shadow-2xl">
            @yield('content')
        </div>

        <p class="text-xs text-slate-400/80 mt-6">
            &copy; {{ date('Y') }} SIM-MBG Satuan Pelayanan Pemenuhan Gizi (SPPG) Sungai Selayur 1.
        </p>
    </div>
</body>
</html>

