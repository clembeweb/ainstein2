<div>
    <form wire:submit.prevent="generate" class="space-y-4">
        <textarea wire:model="urls" class="w-full border" rows="4" placeholder="One URL per line"></textarea>
        <textarea wire:model="businessInfo" class="w-full border" rows="3" placeholder="Business info (optional)"></textarea>
        <x-primary-button type="submit">Generate</x-primary-button>
    </form>

    @if($results)
        <table class="mt-6 w-full text-sm">
            <thead>
                <tr><th class="text-left">URL</th><th class="text-left">Schema</th></tr>
            </thead>
            <tbody>
                @foreach($results as $row)
                    <tr class="border-t">
                        <td class="pr-4 align-top">{{ $row['url'] }}</td>
                        <td><pre class="whitespace-pre-wrap">{{ $row['schema'] }}</pre></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
