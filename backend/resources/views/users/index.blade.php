<x-layouts.app
  title="Kelola User"
  eyebrow="Manajemen User"
  heading="Daftar User"
  description="Kelola akun pengguna aplikasi: tambah, ubah, dan hapus user."
>
  @php
    $usersIndexConfig = [
      'endpoint' => route('users.index'),
      'csrfToken' => csrf_token(),
      'initialPerPage' => (int) request('per_page', 10),
      'initialQ' => (string) request('q', ''),
      'initialUsers' => $users->map(fn ($user) => [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'gender_label' => $user->gender === 'L' ? 'Laki-laki' : ($user->gender === 'P' ? 'Perempuan' : '-'),
        'birth_date_label' => $user->birth_date?->format('d M Y') ?? '-',
        'edit_url' => route('users.edit', $user),
        'destroy_url' => route('users.destroy', $user),
        'is_current_auth' => auth()->id() === $user->id,
      ])->values(),
      'initialPage' => $users->currentPage(),
      'initialLastPage' => $users->lastPage(),
      'initialTotal' => $users->total(),
      'initialFrom' => $users->firstItem() ?? 0,
      'initialTo' => $users->lastItem() ?? 0,
    ];
  @endphp

  <x-slot:headerActions>
    <a href="{{ route('users.create') }}">
      <x-ui.button>Tambah user</x-ui.button>
    </a>
  </x-slot:headerActions>

  @if (session('success'))
    <div class="alert alert-success mb-6 text-sm">{{ session('success') }}</div>
  @endif

  @if (session('error'))
    <div class="alert alert-error mb-6 text-sm">{{ session('error') }}</div>
  @endif

  <script id="users-index-config" type="application/json">@json($usersIndexConfig)</script>

  <x-ui.card x-data="window.usersIndexFromScript('users-index-config')" x-init="init()">
    <div
      x-show="toast.show"
      x-transition
      class="toast toast-end toast-top z-50"
      style="display: none;"
    >
      <div class="alert" :class="toast.type === 'success' ? 'alert-success' : 'alert-error'">
        <span x-text="toast.message"></span>
      </div>
    </div>

    <div class="mb-4 flex items-center justify-between gap-3">
      <p class="text-sm text-base-content/70" x-text="`Menampilkan ${from || 0}-${to || 0} dari ${total} user`"></p>
      <div class="flex items-center gap-2">
        <input
          type="text"
          placeholder="Cari nama/email/no. HP"
          class="input input-bordered input-sm w-56"
          x-model.debounce.400ms="q"
          @input.debounce.400ms="search()"
        >
        <label for="per_page" class="text-sm text-base-content/70">Per halaman</label>
        <select id="per_page" class="select select-bordered select-sm" x-model.number="perPage" @change="changePerPage()">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
        </select>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="table-zebra table w-full">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>No. HP</th>
            <th>Jenis Kelamin</th>
            <th>Tgl Lahir</th>
            <th class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <template x-if="loading">
            <tr>
              <td colspan="6" class="py-8 text-center text-sm text-base-content/60">Memuat data...</td>
            </tr>
          </template>
          <template x-if="!loading && users.length === 0">
            <tr>
              <td colspan="6" class="py-8 text-center text-sm text-base-content/60">
                Tidak ada data user.
              </td>
            </tr>
          </template>
          <template x-for="user in users" :key="user.id">
            <tr>
              <td class="font-medium" x-text="user.name"></td>
              <td x-text="user.email"></td>
              <td x-text="user.phone"></td>
              <td x-text="user.gender_label"></td>
              <td x-text="user.birth_date_label"></td>
              <td>
                <div class="flex items-center justify-end gap-2">
                  <a :href="user.edit_url" class="btn btn-sm btn-ghost">Edit</a>
                  <button type="button" class="btn btn-sm btn-error btn-outline" @click="askDeleteUser(user)">Hapus</button>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div class="mt-5 flex items-center justify-end gap-2">
      <button type="button" class="btn btn-sm" :disabled="page <= 1 || loading" @click="loadUsers(page - 1)">Sebelumnya</button>
      <template x-for="(item, idx) in pageItems()" :key="`page-${idx}-${item}`">
        <span>
          <span x-show="item === '...'" class="px-1 text-sm text-base-content/60">...</span>
          <button
            x-show="item !== '...'"
            type="button"
            class="btn btn-sm"
            :class="Number(item) === page ? 'btn-primary' : 'btn-ghost'"
            :disabled="loading"
            @click="loadUsers(Number(item))"
            x-text="item"
          ></button>
        </span>
      </template>
      <button type="button" class="btn btn-sm" :disabled="page >= lastPage || loading" @click="loadUsers(page + 1)">Berikutnya</button>
    </div>

    <x-ui.modal
      title="Konfirmasi Hapus User"
      on-close="@click='closeDeleteModal()'"
      x-bind:class="{ 'modal-open': confirmDeleteOpen }"
    >
      User <span class="font-medium text-base-content" x-text="selectedUser?.name ?? '-'"></span> akan dihapus permanen.
      Tindakan ini tidak bisa dibatalkan.

      <x-slot:actions>
        <button type="button" class="btn btn-ghost" :disabled="deleting" @click="closeDeleteModal()">Batal</button>
        <button type="button" class="btn btn-error" :disabled="deleting" @click="confirmDeleteUser()">
          <span x-show="!deleting">Ya, hapus</span>
          <span x-show="deleting">Menghapus...</span>
        </button>
      </x-slot:actions>
    </x-ui.modal>
  </x-ui.card>
</x-layouts.app>
