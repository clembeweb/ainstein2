<div>
    <form wire:submit.prevent="addKeywords" class="space-y-4">
        <input type="text" wire:model="campaignStrategyId" class="border w-full" placeholder="Campaign Strategy ID" />
        <input type="text" wire:model="adgroupName" class="border w-full" placeholder="Adgroup Name" />
        <textarea wire:model="keywords" class="border w-full" rows="4" placeholder="Keywords (one per line)"></textarea>
        <x-primary-button type="submit">Add</x-primary-button>
    </form>

    <table class="mt-6 w-full text-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Keyword</th>
                <th>Adgroup</th>
            </tr>
        </thead>
        <tbody>
            @foreach($list as $row)
                <tr class="border-t">
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->keyword }}</td>
                    <td>{{ $row->adgroup_name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
