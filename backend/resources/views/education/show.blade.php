<x-layouts.app
    title="Detail Konten"
    eyebrow="Template detail"
    :heading="$content['title']"
    description="Contoh halaman detail konten untuk mode baca dan edit admin pada modul edukasi."
>
    <x-slot:headerActions>
        <x-ui.button variant="secondary">Preview</x-ui.button>
        <x-ui.button>Simpan perubahan</x-ui.button>
    </x-slot:headerActions>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="space-y-6">
            <x-ui.card title="Mode Edit Admin" description="Field berikut disiapkan agar nanti bisa terhubung ke endpoint update konten.">
                <form class="space-y-5">
                    <div class="grid gap-5 md:grid-cols-2">
                        <x-ui.input label="Menu utama" value="{{ $content['menu'] }}" readonly />
                        <x-ui.input label="Section" value="{{ $content['section'] }}" readonly />
                    </div>

                    <div class="grid gap-5 md:grid-cols-[minmax(0,1fr)_200px]">
                        <x-ui.input label="Judul konten" value="{{ $content['title'] }}" />
                        <x-ui.input label="Status" value="{{ $content['status'] }}" />
                    </div>

                    <x-ui.input label="Ringkasan singkat" value="{{ $content['summary'] }}" hint="Ringkasan ditampilkan pada kartu daftar konten." />

                    <x-ui.textarea label="Isi konten" hint="Untuk kalkulator, area ini bisa dipakai sebagai deskripsi sebelum form hitung.">{{ $content['body'] }}</x-ui.textarea>
                </form>
            </x-ui.card>

            <x-ui.card title="Preview Konten" description="Area ini menampilkan gaya baca yang lebih dekat dengan tampilan pengguna akhir.">
                <div class="prose prose-slate max-w-none">
                    <p class="text-base leading-7 text-slate-600">{{ $content['summary'] }}</p>
                    @foreach (explode("\n", $content['body']) as $paragraph)
                        <p class="mt-4 text-sm leading-7 text-slate-600">{{ $paragraph }}</p>
                    @endforeach
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card title="Metadata">
                <div class="space-y-4 text-sm text-slate-600">
                    <div class="flex items-center justify-between gap-3">
                        <span>Menu</span>
                        <span class="font-medium text-slate-900">{{ $content['menu'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span>Section</span>
                        <span class="font-medium text-slate-900">{{ $content['section'] }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span>Status</span>
                        <x-ui.badge tone="warning">{{ $content['status'] }}</x-ui.badge>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Catatan Implementasi">
                <ul class="space-y-3 text-sm leading-6 text-slate-600">
                    @foreach ($content['notes'] as $note)
                        <li class="flex gap-3">
                            <span class="mt-2 h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span>{{ $note }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
