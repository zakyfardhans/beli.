@php($editing = isset($kupon))

<div class="space-y-4">
    <div>
        <label for="kode" class="mb-1 block text-sm font-semibold">Kode Kupon</label>
        <input id="kode" name="kode" type="text" maxlength="50" value="{{ old('kode', $editing ? $kupon->kode : '') }}"
            required autocomplete="off"
            class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm uppercase focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        <p class="mt-1 text-xs text-muted">Gunakan huruf, angka, tanda hubung, atau garis bawah.</p>
        @error('kode')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="persentase" class="mb-1 block text-sm font-semibold">Diskon (%)</label>
            <input id="persentase" name="persentase" type="number" min="1" max="100"
                value="{{ old('persentase', $editing ? $kupon->persentase : '') }}" required
                class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
            @error('persentase')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="kuota" class="mb-1 block text-sm font-semibold">Kuota penggunaan</label>
            <input id="kuota" name="kuota" type="number" min="{{ $editing ? max(1, $kupon->terpakai) : 1 }}"
                value="{{ old('kuota', $editing ? $kupon->kuota : '') }}" required
                class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
            @error('kuota')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label for="kedaluwarsa" class="mb-1 block text-sm font-semibold">Berlaku sampai</label>
        <input id="kedaluwarsa" name="kedaluwarsa" type="datetime-local"
            value="{{ old('kedaluwarsa', $editing ? $kupon->kedaluwarsa->format('Y-m-d\\TH:i') : now()->addMonth()->format('Y-m-d\\TH:i')) }}"
            @unless ($editing) min="{{ now()->format('Y-m-d\\TH:i') }}" @endunless required
            class="w-full rounded-lg border border-line px-3.5 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
        @error('kedaluwarsa')<p class="mt-1 text-sm text-rose-700">{{ $message }}</p>@enderror
    </div>

    <input type="hidden" name="aktif" value="0">
    <label class="flex min-h-11 items-center gap-2 text-sm font-semibold">
        <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $editing ? $kupon->aktif : true))
            class="rounded border-line text-brand focus:ring-brand">
        Kupon aktif
    </label>
</div>
