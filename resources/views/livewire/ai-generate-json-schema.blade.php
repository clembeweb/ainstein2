<div class="p-6">
    <form wire:submit.prevent="uploadSitemap" class="mb-4">
        <input type="file" wire:model="sitemap" accept=".xml">
        <button type="submit" class="ml-2 px-4 py-2 bg-blue-500 text-white">Upload Sitemap</button>
    </form>

    <div class="mb-4">
        <input type="text" wire:model="serialKey" placeholder="Serial key" class="border p-2 mr-2">
        <input type="text" wire:model="businessInfo" placeholder="Business info" class="border p-2">
        <button wire:click="generate" class="ml-2 px-4 py-2 bg-green-500 text-white">Generate Schema</button>
    </div>

    <table class="w-full text-left border">
        <thead>
            <tr>
                <th class="border px-2">URL</th>
                <th class="border px-2">Schema</th>
                <th class="border px-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $result)
            <tr>
                <td class="border px-2">{{ $result->url }}</td>
                <td class="border px-2">
                    <textarea class="w-full" rows="5" readonly>{{ $result->schema }}</textarea>
                </td>
                <td class="border px-2">
                    <button wire:click="delete({{ $result->id }})" class="px-2 py-1 bg-red-500 text-white">Delete</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
