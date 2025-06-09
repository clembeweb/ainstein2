<div class="ml-56 p-8"> {{-- lascia spazio alla sidebar --}}
    <h1 class="text-3xl font-semibold mb-6">Benvenuto, {{ auth()->user()->name }} 👋</h1>

    <section class="grid md:grid-cols-3 gap-6">
        <div class="p-6 bg-white rounded-xl shadow">
            <h2 class="text-lg font-medium mb-2">Crea un nuovo articolo AI</h2>
            <p class="text-sm text-gray-600 mb-4">Avvia subito la pipeline…</p>
            <x-primary-button wire:click="$emit('openNewJob')">Nuovo job</x-primary-button>
        </div>

        <div class="p-6 bg-white rounded-xl shadow">
            <h2 class="text-lg font-medium mb-2">Articoli recenti</h2>
            <livewire:articles-table limit="5"/>
        </div>

        <div class="p-6 bg-white rounded-xl shadow">
            <h2 class="text-lg font-medium mb-2">Statistiche</h2>
            <livewire:stats-widget/>
        </div>
    </section>
</div>
