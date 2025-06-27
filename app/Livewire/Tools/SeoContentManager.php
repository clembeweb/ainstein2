<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Models\SeoContent;
use App\Jobs\GenerateSeoContent;

class SeoContentManager extends Component
{
    use WithFileUploads;

    public $file;
    public $contents;

    public function mount(): void
    {
        $this->loadContents();
    }

    public function loadContents(): void
    {
        $this->contents = SeoContent::all();
    }

    public function uploadCsv(): void
    {
        $this->validate(['file' => 'required|file']);
        $path = $this->file->store('temp');
        $rows = array_map('str_getcsv', file(Storage::path($path)));
        foreach ($rows as $row) {
            if (!isset($row[0], $row[1])) {
                continue;
            }
            SeoContent::create([
                'name' => $row[0],
                'url'  => $row[1],
            ]);
        }
        Storage::delete($path);
        $this->loadContents();
    }

    public function generate(int $id): void
    {
        $content = SeoContent::findOrFail($id);
        GenerateSeoContent::dispatchSync($content);
        $this->loadContents();
    }

    public function updateField(int $id, string $field, string $value): void
    {
        SeoContent::findOrFail($id)->update([$field => $value]);
        $this->loadContents();
    }

    public function render()
    {
        return view('tools.seo-content');
    }
}
