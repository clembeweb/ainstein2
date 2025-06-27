<div>
    <form wire:submit.prevent="uploadCsv" class="space-y-4">
        <input type="file" wire:model="file" class="border" />
        <x-primary-button type="submit">Upload</x-primary-button>
    </form>

    <table class="mt-6 w-full text-sm">
        <thead>
            <tr>
                <th>Name</th><th>URL</th><th>Title</th><th>Description</th><th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($contents as $row)
            <tr class="border-t">
                <td>{{ $row->name }}</td>
                <td>{{ $row->url }}</td>
                <td>{{ $row->seo_title }}</td>
                <td>{{ $row->seo_description }}</td>
                <td>
                    <x-primary-button wire:click="generate({{ $row->id }})" class="px-2 py-1">Generate</x-primary-button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
