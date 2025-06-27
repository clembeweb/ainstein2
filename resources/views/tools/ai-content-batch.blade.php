<div>
    <form wire:submit.prevent="generate" class="space-y-4">
        <input type="text" wire:model="siteUrl" class="w-full border rounded" placeholder="WordPress Site URL" />
        <textarea wire:model="payload" class="w-full border rounded" rows="4" placeholder="Content spec"></textarea>
        <x-primary-button type="submit">Generate</x-primary-button>
    </form>

    <table class="mt-6 w-full text-sm">
        <thead>
            <tr><th>Payload</th><th>Result</th></tr>
        </thead>
        <tbody>
            @foreach($list as $row)
                <tr class="border-t">
                    <td class="whitespace-pre-wrap">{{ $row->payload }}</td>
                    <td class="whitespace-pre-wrap">{{ $row->result }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
