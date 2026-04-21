<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $campaign->name }} — ALG3PL</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 text-white min-h-screen">
    <div class="max-w-3xl mx-auto px-6 py-16">
        <a href="{{ route('campaigns.archive') }}" class="text-sm text-gray-500 hover:text-gray-300 mb-8 inline-block">&larr; Back to archive</a>

        <h1 class="text-3xl font-bold text-white mb-2">{{ $campaign->name }}</h1>
        <p class="text-sm text-gray-500 mb-8">
            {{ $campaign->end_date?->format('F d, Y') }}
            @if($campaign->country) &middot; {{ $campaign->country->name }} @endif
        </p>

        @if($emailCampaign)
            <div class="rounded-xl bg-gray-900/50 p-8 ring-1 ring-white/[0.06]">
                <h2 class="text-xl font-semibold text-white mb-4">{{ $emailCampaign->subject }}</h2>
                <div class="prose prose-invert max-w-none">
                    {!! $emailCampaign->body !!}
                </div>
            </div>
        @else
            <div class="rounded-xl bg-gray-900/50 p-8 ring-1 ring-white/[0.06]">
                @if($campaign->description)
                    <p class="text-gray-300">{{ $campaign->description }}</p>
                @else
                    <p class="text-gray-500">No content available for this campaign.</p>
                @endif
            </div>
        @endif
    </div>
</body>
</html>
