<x-filament-panels::page>
    <form wire:submit="registrar">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit" size="lg" icon="heroicon-o-check-circle">
                Registrar Recebimento
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>