@if (session('success'))
    <div class="mb-6">
        <x-ui.alert tone="success" :message="session('success')" />
    </div>
@endif

@if (session('error'))
    <div class="mb-6">
        <x-ui.alert tone="error" :message="session('error')" />
    </div>
@endif
