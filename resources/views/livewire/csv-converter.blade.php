<div class="max-w-xl space-y-4">

    <div class="w-full">
        <input id="csv" type="file" wire:model="file" accept=".csv" class="hidden" />

        <label for="csv"
               class="inline-flex items-center justify-center px-4 py-2 rounded-md
           border border-gray-300 bg-white text-sm font-medium text-gray-700
           hover:bg-gray-50 cursor-pointer
           focus-within:ring-2 focus-within:ring-gray-300">
            Choose CSV
        </label>

        <span class="ml-3 text-sm text-gray-600">
    @if($file) {{ $file->getClientOriginalName() }} @else No file selected @endif
  </span>

        @error('file')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div wire:loading wire:target="file" class="mt-2 text-sm text-gray-500">
            Uploading…
        </div>
    </div>

    @error('file')
    <div class="text-red-600 text-sm">{{ $message }}</div>
    @enderror

    <button
        wire:click="convert"
        class="relative border border-dashed rounded-lg p-4 text-sm text-center w-64 py-2 mt-3 bg-black text-white rounded"
        wire:loading.attr="disabled"
    >
        Convert & Download
    </button>

    <div wire:loading wire:target="convert">Processing…</div>
</div>
