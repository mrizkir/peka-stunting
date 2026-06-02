<x-layouts.app
  title="Info Aplikasi - PEKA Stunting"
  eyebrow="Pengaturan aplikasi"
  heading="Info Aplikasi"
  description="Kelola poster, teks, dan video untuk halaman Info Aplikasi di mobile. Konten harus dipublikasikan agar tampil di aplikasi."
>
  <x-slot:headerActions>
    @if ($canEdit)
      <button type="submit" form="app-info-content-form" class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2.5 text-sm font-semibold text-primary-content hover:bg-primary/90 shadow-sm">
        Simpan perubahan
      </button>
    @endif
  </x-slot:headerActions>

  @if (session('success'))
    <div class="alert alert-success mb-6 text-sm">{{ session('success') }}</div>
  @endif

  @unless ($canEdit)
    <div class="alert alert-warning mb-6 text-sm">
      Hanya admin yang dapat mengubah konten. Anda sedang dalam mode baca.
    </div>
  @endunless

  <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    <div class="space-y-6">
      <x-ui.card title="{{ $canEdit ? 'Edit Konten' : 'Detail Konten' }}" description="Poster ditampilkan full-screen dengan zoom dan tab Hal 1 / Hal 2 di aplikasi mobile.">
        <form
          id="app-info-content-form"
          action="{{ route('settings.app-info.update') }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-5"
        >
          @csrf
          @method('PUT')

          <div class="grid gap-5 md:grid-cols-[minmax(0,1fr)_200px]">
            <div>
              <x-ui.input
                label="Judul"
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
            <x-ui.input
              label="Link video (opsional)"
              name="video_url"
              type="url"
              :value="old('video_url', $content['video_url'] ?? '')"
              :readonly="! $canEdit"
              placeholder="https://www.youtube.com/watch?v=..."
              hint="Ditampilkan di bawah poster pada halaman Info Aplikasi di mobile."
            />
            @error('video_url')
              <p class="text-error mt-2 text-sm">{{ $message }}</p>
            @enderror
            @php
              $previewVideoUrl = old('video_url', $content['video_url'] ?? '');
              $previewVideoEmbed = \App\Support\EducationVideoUrl::youtubeEmbedUrl($previewVideoUrl);
            @endphp
            @if (filled($previewVideoUrl))
              <div class="mt-4 rounded-xl border border-base-300 bg-base-200/30 p-4">
                <p class="text-base-content/70 mb-3 text-xs font-medium uppercase tracking-wide">Pratinjau video</p>
                @if ($previewVideoEmbed)
                  <div class="aspect-video overflow-hidden rounded-lg bg-black">
                    <iframe
                      src="{{ $previewVideoEmbed }}"
                      title="Pratinjau video"
                      class="h-full w-full"
                      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                      allowfullscreen
                    ></iframe>
                  </div>
                @else
                  <a href="{{ $previewVideoUrl }}" target="_blank" rel="noopener noreferrer" class="text-primary text-sm font-medium hover:underline">
                    {{ $previewVideoUrl }}
                  </a>
                @endif
              </div>
            @endif
          </div>

          <div>
            <x-ui.input
              label="Ringkasan singkat"
              name="excerpt"
              type="text"
              :value="old('excerpt', $content['summary'])"
              :readonly="! $canEdit"
              hint="Ditampilkan di bawah poster jika ada."
            />
            @error('excerpt')
              <p class="text-error mt-2 text-sm">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <x-ui.rich-text-editor
              label="Isi konten"
              name="body"
              :value="old('body', $content['body'])"
              :readonly="! $canEdit"
              hint="Teks tambahan di bawah poster dan ringkasan."
            />
            @error('body')
              <p class="text-error mt-2 text-sm">{{ $message }}</p>
            @enderror
          </div>

          @if ($canEdit)
            <div>
              <label class="block">
                <span class="mb-2 block text-sm font-medium text-base-content/80">Galeri poster</span>
                <input
                  type="file"
                  name="poster_images[]"
                  multiple
                  accept="image/jpeg,image/png,image/webp"
                  class="bg-base-100 border-base-300 text-base-content focus:border-primary focus:ring-primary/15 block w-full rounded-md border px-4 py-3 text-sm shadow-sm outline-none transition focus:ring-4"
                >
                <span class="text-base-content/55 mt-2 block text-xs">
                  Upload beberapa poster sekaligus untuk swipe di aplikasi mobile (Hal 1, Hal 2, …).
                  JPEG, PNG, atau WebP. {{ \App\Support\UploadSizeLimit::posterImageUploadHint() }}
                </span>
              </label>
              @error('poster_images')
                <p class="text-error mt-2 text-sm">{{ $message }}</p>
              @enderror
              @error('poster_images.*')
                <p class="text-error mt-2 text-sm">{{ $message }}</p>
              @enderror

              @if (! empty($content['gallery_images']))
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                  @foreach ($content['gallery_images'] as $galleryImage)
                    <div class="space-y-2">
                      <img src="{{ $galleryImage['url'] }}" alt="" class="max-h-48 w-full rounded-xl border border-slate-200 object-cover">
                      <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remove_gallery_image_ids[]" value="{{ $galleryImage['id'] }}" class="rounded border-slate-300">
                        Hapus gambar ini
                      </label>
                    </div>
                  @endforeach
                </div>
                <label class="mt-3 flex items-center gap-2 text-sm text-slate-600">
                  <input type="checkbox" name="remove_poster_images" value="1" class="rounded border-slate-300">
                  Hapus semua galeri poster saat ini
                </label>
              @endif
            </div>
          @elseif (! empty($content['gallery_images']))
            <div>
              <p class="mb-2 text-sm font-medium text-slate-700">Galeri poster</p>
              <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($content['gallery_images'] as $galleryImage)
                  <img src="{{ $galleryImage['url'] }}" alt="" class="max-h-48 w-full rounded-xl border border-slate-200 object-cover">
                @endforeach
              </div>
            </div>
          @endif
        </form>
      </x-ui.card>

      <x-ui.card title="Pratinjau" description="Perkiraan tampilan konten di aplikasi mobile.">
        <div class="prose prose-slate max-w-none">
          @if (! empty($content['gallery_images']))
            @foreach ($content['gallery_images'] as $galleryImage)
              <img src="{{ $galleryImage['url'] }}" alt="" class="mb-4 max-h-56 rounded-xl object-cover">
            @endforeach
          @else
            <p class="text-sm italic text-slate-400">Belum ada poster. Unggah galeri poster agar tampil di mobile.</p>
          @endif
          <p class="text-base leading-7 text-slate-600">{{ old('excerpt', $content['summary']) ?: '—' }}</p>
          @php
            $previewBody = app(\App\Support\EducationBodySanitizer::class)->sanitize(
              old('body', $content['body']),
            );
          @endphp
          @if (filled($previewBody))
            <div class="education-body-html mt-4 text-sm leading-7 text-slate-600">
              {!! $previewBody !!}
            </div>
          @endif
        </div>
      </x-ui.card>
    </div>

    <div class="space-y-6">
      <x-ui.card title="Metadata">
        <div class="space-y-4 text-sm text-slate-600">
          <div class="flex items-center justify-between gap-3">
            <span>Status</span>
            <x-ui.badge :tone="$content['status_raw'] === 'published' ? 'success' : 'warning'">
              {{ $content['status'] }}
            </x-ui.badge>
          </div>
          <div class="flex items-center justify-between gap-3">
            <span>Jumlah poster</span>
            <span class="font-medium text-slate-900">{{ count($content['gallery_images']) }}</span>
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

      <x-ui.card title="Petunjuk">
        <ul class="list-disc space-y-2 pl-5 text-sm leading-6 text-slate-600">
          <li>Poster ditampilkan full-screen dengan fitur zoom di aplikasi mobile.</li>
          <li>Beberapa poster akan muncul sebagai tab Hal 1, Hal 2, dan seterusnya.</li>
          <li>Set status ke <strong>Published</strong> agar konten tampil di aplikasi.</li>
        </ul>
      </x-ui.card>
    </div>
  </div>
</x-layouts.app>
