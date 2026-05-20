<x-layouts.app
  title="Detail Konten"
  eyebrow="CMS Edukasi"
  :heading="$content['title']"
  description="Kelola judul, ringkasan, isi, status, dan gambar unggulan konten edukasi."
>
  <x-slot:headerActions>
    <a href="{{ route('education.menus.show', $content['menu_slug']) }}" class="inline-flex items-center justify-center rounded-md bg-base-100 px-4 py-2.5 text-sm font-semibold text-base-content ring-1 ring-inset ring-base-300 hover:bg-base-200">Kembali</a>
    @if ($canEdit)
      <button type="submit" form="education-content-form" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2.5 text-sm font-semibold text-primary-content hover:bg-primary/90 shadow-sm">Simpan perubahan</button>
    @endif
  </x-slot:headerActions>

  @if (session('success'))
    <div class="alert alert-success mb-6 text-sm">{{ session('success') }}</div>
  @endif

  @if (session('error'))
    <div class="alert alert-error mb-6 text-sm">{{ session('error') }}</div>
  @endif

  @unless ($canEdit)
    <div class="alert alert-warning mb-6 text-sm">
      Hanya admin yang dapat mengubah konten. Anda sedang dalam mode baca.
    </div>
  @endunless

  <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    <div class="space-y-6">
      <x-ui.card title="{{ $canEdit ? 'Edit Konten' : 'Detail Konten' }}" description="Perubahan disimpan ke database dan dapat dipublikasikan ke aplikasi mobile.">
        <form
          id="education-content-form"
          action="{{ route('education.contents.update', ['menu' => $content['menu_slug'], 'item' => $content['item_slug']]) }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-5"
        >
          @csrf
          @method('PUT')

          <div class="grid gap-5 md:grid-cols-2">
            <x-ui.input label="Menu utama" value="{{ $content['menu'] }}" readonly />
            <x-ui.input label="Section" value="{{ $content['section'] }}" readonly />
          </div>

          <div class="grid gap-5 md:grid-cols-[minmax(0,1fr)_200px]">
            <div>
              <x-ui.input
                label="Judul konten"
                name="title"
                type="text"
                :value="old('title', $content['title'])"
                :readonly="! $canEdit"
                required
              />
              @error('title')
                <p class="text-error mt-2 text-sm">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <x-ui.select label="Status" name="status" :disabled="! $canEdit" required>
                <option value="draft" @selected(old('status', $content['status_raw']) === 'draft')>Draft</option>
                <option value="published" @selected(old('status', $content['status_raw']) === 'published')>Published</option>
              </x-ui.select>
              @error('status')
                <p class="text-error mt-2 text-sm">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div>
            <span class="mb-2 block text-sm font-medium text-base-content/80">Tipe konten</span>
            <div class="rounded-md border border-base-300 bg-base-200/40 px-4 py-3">
              <x-ui.badge :tone="$content['type'] === 'Kalkulator' ? 'info' : 'slate'">
                {{ $content['type'] }}
              </x-ui.badge>
              <p class="text-base-content/55 mt-2 text-xs leading-relaxed">
                Tipe <strong>Konten</strong> atau <strong>Kalkulator</strong> ditetapkan saat item dibuat di struktur menu edukasi
                (bukan diubah dari form ini). Mengubah tipe memengaruhi tampilan di aplikasi mobile
                (artikel baca vs form hitung). Untuk mengedit judul, ringkasan, isi, status, dan gambar, gunakan field di bawah.
              </p>
            </div>
          </div>

          <div>
            <x-ui.input
              label="Ringkasan singkat"
              name="excerpt"
              type="text"
              :value="old('excerpt', $content['summary'])"
              :readonly="! $canEdit"
              hint="Ringkasan ditampilkan pada kartu daftar konten."
            />
            @error('excerpt')
              <p class="text-error mt-2 text-sm">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <x-ui.textarea
              label="Isi konten"
              name="body"
              :readonly="! $canEdit"
              hint="Untuk kalkulator, area ini bisa dipakai sebagai deskripsi sebelum form hitung."
            >{{ old('body', $content['body']) }}</x-ui.textarea>
            @error('body')
              <p class="text-error mt-2 text-sm">{{ $message }}</p>
            @enderror
          </div>

          @if ($canEdit)
            <div>
              <label class="block">
                <span class="mb-2 block text-sm font-medium text-base-content/80">Gambar unggulan</span>
                <input
                  type="file"
                  name="featured_image"
                  accept="image/jpeg,image/png,image/webp"
                  class="bg-base-100 border-base-300 text-base-content focus:border-primary focus:ring-primary/15 block w-full rounded-md border px-4 py-3 text-sm shadow-sm outline-none transition focus:ring-4"
                >
                <span class="text-base-content/55 mt-2 block text-xs">JPEG, PNG, atau WebP. Maks. 2 MB.</span>
              </label>
              @error('featured_image')
                <p class="text-error mt-2 text-sm">{{ $message }}</p>
              @enderror

              @if (! empty($content['featured_image_url']))
                <div class="mt-4">
                  <img src="{{ $content['featured_image_url'] }}" alt="{{ $content['title'] }}" class="max-h-48 rounded-xl border border-slate-200 object-cover">
                  <label class="mt-3 flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remove_featured_image" value="1" class="rounded border-slate-300">
                    Hapus gambar saat ini
                  </label>
                </div>
              @endif
            </div>
          @elseif (! empty($content['featured_image_url']))
            <div>
              <p class="mb-2 text-sm font-medium text-slate-700">Gambar unggulan</p>
              <img src="{{ $content['featured_image_url'] }}" alt="{{ $content['title'] }}" class="max-h-48 rounded-xl border border-slate-200 object-cover">
            </div>
          @endif
        </form>
      </x-ui.card>

      <x-ui.card title="Preview Konten" description="Area ini menampilkan gaya baca yang lebih dekat dengan tampilan pengguna akhir.">
        <div class="prose prose-slate max-w-none">
          @if (! empty($content['featured_image_url']))
            <img src="{{ $content['featured_image_url'] }}" alt="" class="mb-4 max-h-56 rounded-xl object-cover">
          @endif
          <p class="text-base leading-7 text-slate-600">{{ old('excerpt', $content['summary']) ?: '—' }}</p>
          @php $previewBody = old('body', $content['body']); @endphp
          @if (filled($previewBody))
            @foreach (explode("\n", $previewBody) as $paragraph)
              @if (filled($paragraph))
                <p class="mt-4 text-sm leading-7 text-slate-600">{{ $paragraph }}</p>
              @endif
            @endforeach
          @else
            <p class="mt-4 text-sm italic text-slate-400">Belum ada isi konten.</p>
          @endif
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
            <span>Tipe</span>
            <span class="font-medium text-slate-900">{{ $content['type'] }}</span>
          </div>
          <div class="flex items-center justify-between gap-3">
            <span>Status</span>
            <x-ui.badge :tone="$content['status_raw'] === 'published' ? 'success' : 'warning'">
              {{ $content['status'] }}
            </x-ui.badge>
          </div>
          @if ($educationContent->updated_at)
            <div class="flex items-center justify-between gap-3">
              <span>Diperbarui</span>
              <span class="font-medium text-slate-900">{{ $educationContent->updated_at->format('d M Y H:i') }}</span>
            </div>
          @endif
          @if ($educationContent->updatedBy)
            <div class="flex items-center justify-between gap-3">
              <span>Oleh</span>
              <span class="font-medium text-slate-900">{{ $educationContent->updatedBy->name }}</span>
            </div>
          @endif
        </div>
      </x-ui.card>

      <x-ui.card title="Navigasi">
        <div class="space-y-3 text-sm">
          <a href="{{ route('education.menus.show', $content['menu_slug']) }}" class="font-medium text-emerald-600 hover:text-emerald-700">
            ← Kembali ke {{ $content['menu'] }}
          </a>
          <a href="{{ route('education.index') }}" class="block font-medium text-slate-600 hover:text-slate-900">
            Semua menu edukasi
          </a>
        </div>
      </x-ui.card>
    </div>
  </div>
</x-layouts.app>
