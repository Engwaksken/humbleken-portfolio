{{-- Admin panel style overrides, loaded after Filament's own styles. --}}
<style>
    /* Centre dialogs vertically; Filament's default places them in the upper quarter on larger screens. */
    .fi-modal:not(.fi-modal-slide-over):not(.fi-width-screen) > .fi-modal-window-ctn {
        grid-template-rows: 1fr auto 1fr;
    }
</style>
