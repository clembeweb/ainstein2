<div>
    <form wire:submit.prevent="uploadCsv">
        <input type="file" wire:model="file">
        <button type="submit">Upload</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Name</th><th>URL</th><th>Title</th><th>Description</th>
            </tr>
        </thead>
        <tbody>
        @foreach($contents as $row)
            <tr>
                <td>{{ $row->name }}</td>
                <td>{{ $row->url }}</td>
                <td>{{ $row->seo_title }}</td>
                <td>{{ $row->seo_description }}</td>
                <td>
                    <button wire:click="generate({{ $row->id }})">Generate</button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
