<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Under Maintenance &middot; Smarter Groups</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; height: 100%; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            color: #111827;
            -webkit-font-smoothing: antialiased;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
                color: #f9fafb;
            }
            .card { background: rgba(31, 41, 55, 0.6); border-color: rgba(75, 85, 99, 0.4); }
            .subtitle { color: #9ca3af; }
            .hint { color: #6b7280; }
        }

        .card {
            max-width: 32rem;
            width: 100%;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(229, 231, 235, 0.8);
            border-radius: 1rem;
            padding: 3rem 2.5rem;
            text-align: center;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.1);
        }

        .icon {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px -8px rgba(99, 102, 241, 0.5);
        }

        .icon svg { width: 2rem; height: 2rem; color: white; }

        h1 {
            font-size: 1.875rem;
            font-weight: 700;
            margin: 0 0 0.75rem;
            letter-spacing: -0.025em;
        }

        .subtitle {
            font-size: 1rem;
            color: #4b5563;
            line-height: 1.6;
            margin: 0 0 2rem;
        }

        .hint {
            font-size: 0.875rem;
            color: #9ca3af;
            margin: 0;
        }

        .dot {
            display: inline-block;
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 50%;
            background: #a855f7;
            margin-right: 0.5rem;
            animation: pulse 1.6s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.4; transform: scale(0.9); }
            50% { opacity: 1; transform: scale(1.1); }
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>

        <h1>We&rsquo;ll be right back</h1>

        <p class="subtitle">
            Smarter Groups is undergoing scheduled maintenance.
            We&rsquo;re making things better and should be back online shortly.
        </p>

        <p class="hint">
            <span class="dot"></span>This page will refresh automatically.
        </p>
    </main>
</body>
</html>
