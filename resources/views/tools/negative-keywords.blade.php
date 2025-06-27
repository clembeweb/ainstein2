<div>
    <form wire:submit.prevent="generate" class="space-y-4">
        <textarea wire:model="payload" class="w-full border rounded" rows="4" placeholder="Keywords"></textarea>
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
