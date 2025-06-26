<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use App\Models\NegativeKeyword;

class NegativeKeywordsManager extends Component
{
    public string $campaignStrategyId = '';
    public string $adgroupName = '';
    public string $keywords = '';
    public $list;

    public function mount(): void
    {
        $this->loadKeywords();
    }

    public function loadKeywords(): void
    {
        $this->list = NegativeKeyword::latest()->get();
    }

    public function addKeywords(): void
    {
        $this->validate(['keywords' => 'required|string']);
        $lines = preg_split("/\r?\n/", trim($this->keywords));
        foreach ($lines as $keyword) {
            $keyword = trim($keyword);
            if ($keyword === '') {
                continue;
            }
            NegativeKeyword::create([
                'user_id' => auth()->id(),
                'campaign_strategy_id' => $this->campaignStrategyId ?: null,
                'adgroup_name' => $this->adgroupName ?: null,
                'keyword' => $keyword,
            ]);
        }
        $this->keywords = '';
        $this->loadKeywords();
    }

    public function render()
    {
        return view('livewire.tools.negative-keywords-manager');
    }
}
