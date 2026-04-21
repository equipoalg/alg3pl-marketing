<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Archive — ALG3PL</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 text-white min-h-screen">
    <div class="max-w-4xl mx-auto px-6 py-16">
        <div class="mb-12">
            <h1 class="text-3xl font-bold text-white">Newsletter Archive</h1>
            <p class="text-gray-400 mt-2">Past campaigns and newsletters from ALG3PL</p>
        </div>

        <div class="space-y-4">
            @forelse($campaigns as $campaign)
                <a href="{{ route('campaigns.show', $campaign) }}"
                   class="block rounded-xl bg-gray-900/50 p-5 ring-1 ring-white/[0.06] hover:ring-white/[0.12] transition group">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-white group-hover:text-blue-400 transition">{{ $campaign->name }}</h3>
                            @if($campaign->description)
                                <p class="text-sm text-gray-400 mt-1">{{ Str::limit($campaign->description, 120) }}</p>
                            @endif
                        </div>
                        <div class="text-right flex-shrink-0 ml-4">
                            @if($campaign->country)
                                <span class="text-xs text-gray-500">{{ $campaign->country->name }}</span>
                            @endif
                            <p class="text-xs text-gray-600 mt-1">{{ $campaign->end_date?->format('M d, Y') }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-16">
                    <p class="text-gray-500">No newsletters published yet.</p>
                </div>
            @endforelse
        </div>

        @if($campaigns->hasPages())
            <div class="mt-8">{{ $campaigns->links() }}</div>
        @endif
    </div>
</body>
</html>
