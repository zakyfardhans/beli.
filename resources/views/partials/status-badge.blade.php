@php
    /** @var \App\Enums\StatusPesanan $status */
    $kelas = match ($status) {
        \App\Enums\StatusPesanan::MenungguPembayaran => 'bg-amber-100 text-amber-700',
        \App\Enums\StatusPesanan::MenungguVerifikasi => 'bg-violet-100 text-violet-700',
        \App\Enums\StatusPesanan::Diproses => 'bg-sky-100 text-sky-700',
        \App\Enums\StatusPesanan::Dikirim => 'bg-indigo-100 text-indigo-700',
        \App\Enums\StatusPesanan::Selesai => 'bg-emerald-100 text-emerald-800',
        \App\Enums\StatusPesanan::Dibatalkan => 'bg-rose-100 text-rose-800',
    };
@endphp
<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $kelas }}">
    {{ $status->label() }}
</span>
