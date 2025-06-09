<nav class="fixed inset-y-0 left-0 w-56 bg-gray-900 text-gray-100">
    <div class="p-6 text-xl font-bold tracking-wider">AINSTEIN</div>

    <ul class="space-y-1 mt-4">
        <li><a href="{{ route('dashboard') }}"
               class="flex items-center px-6 py-3 hover:bg-gray-800 rounded">
            <x-heroicon-o-home class="w-5 mr-3"/> Dashboard
        </a></li>

        <li><a href="#"
               class="flex items-center px-6 py-3 hover:bg-gray-800 rounded">
            <x-heroicon-o-document-text class="w-5 mr-3"/> AI Articles
        </a></li>

        <li><a href="#"
               class="flex items-center px-6 py-3 hover:bg-gray-800 rounded">
            <x-heroicon-o-cog class="w-5 mr-3"/> Settings
        </a></li>
    </ul>
</nav>
