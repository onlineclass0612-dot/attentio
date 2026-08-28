<div class="space-y-3">
    @if(session('success'))
        <x-radix-alert type="success" title="Berhasil">
            {{ session('success') }}
        </x-radix-alert>
    @endif

    @if(session('error') || session('danger'))
        <x-radix-alert type="destructive" title="Terjadi Kesalahan">
            {{ session('error') ?? session('danger') }}
        </x-radix-alert>
    @endif

    @if(session('warning'))
        <x-radix-alert type="warning" title="Peringatan">
            {{ session('warning') }}
        </x-radix-alert>
    @endif

    @if(session('info') || session('status'))
        <x-radix-alert type="info" title="Informasi">
            {{ session('info') ?? session('status') }}
        </x-radix-alert>
    @endif

    @if($errors->any())
        <x-radix-alert type="destructive" title="Periksa Kembali Input Form Anda">
            <ul class="list-disc list-inside space-y-0.5 mt-1 text-[11px] opacity-95">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-radix-alert>
    @endif
</div>
