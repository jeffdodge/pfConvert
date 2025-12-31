<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="relative h-full flex-1 p-6 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            Welcome to pfConvert!
            <br /><br />
            Please upload your employee export from Rippling to Convert it to Paychex Flex format.
            <br /><br />
            <livewire:csv-converter />
        </div>

    </div>
</x-layouts.app>
