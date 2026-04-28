export function createUsersIndex(config) {
  return {
    endpoint: config.endpoint,
    csrfToken: config.csrfToken,
    users: config.initialUsers ?? [],
    q: config.initialQ ?? '',
    perPage: Number(config.initialPerPage ?? 10),
    page: Number(config.initialPage ?? 1),
    lastPage: Number(config.initialLastPage ?? 1),
    total: Number(config.initialTotal ?? 0),
    from: Number(config.initialFrom ?? 0),
    to: Number(config.initialTo ?? 0),
    loading: false,
    hydrated: true,
    confirmDeleteOpen: false,
    deleting: false,
    selectedUser: null,
    toast: {
      show: false,
      type: 'success',
      message: '',
    },
    async init() {
      const params = new URLSearchParams(window.location.search);
      const urlQ = params.get('q');
      const urlPerPage = Number(params.get('per_page'));
      const urlPage = Number(params.get('page'));

      if (urlQ !== null) this.q = urlQ;
      if ([10, 25, 50].includes(urlPerPage)) this.perPage = urlPerPage;
      if (Number.isInteger(urlPage) && urlPage > 0) this.page = urlPage;

      await this.loadUsers(this.page, { silent: true });
    },
    notify(type, message) {
      this.toast = { show: true, type, message };
      window.setTimeout(() => {
        this.toast.show = false;
      }, 2500);
    },
    syncUrl() {
      const params = new URLSearchParams();
      if (this.q) params.set('q', this.q);
      if (this.perPage !== 10) params.set('per_page', String(this.perPage));
      if (this.page > 1) params.set('page', String(this.page));

      const nextUrl = params.toString()
        ? `${window.location.pathname}?${params.toString()}`
        : window.location.pathname;
      window.history.replaceState({}, '', nextUrl);
    },
    pageItems() {
      const items = [];
      const total = this.lastPage;
      const current = this.page;

      if (total <= 7) {
        for (let i = 1; i <= total; i += 1) items.push(i);
        return items;
      }

      items.push(1);
      if (current > 3) items.push('...');
      for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i += 1) {
        items.push(i);
      }
      if (current < total - 2) items.push('...');
      items.push(total);
      return items;
    },
    async loadUsers(targetPage = 1, options = {}) {
      const { silent = false } = options;
      this.loading = !silent;
      this.page = targetPage;

      const params = new URLSearchParams({
        ajax: '1',
        page: String(this.page),
        per_page: String(this.perPage),
        q: this.q,
      });

      try {
        const response = await fetch(`${this.endpoint}?${params.toString()}`, {
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
        });
        if (!response.ok) throw new Error('Gagal memuat data user.');
        const payload = await response.json();
        const nextLastPage = Number(payload.last_page ?? 1) || 1;
        const nextData = Array.isArray(payload.data) ? payload.data : [];

        if ((payload.total ?? 0) > 0 && nextData.length === 0 && this.page > nextLastPage) {
          await this.loadUsers(nextLastPage);
          return;
        }

        if (Array.isArray(nextData)) {
          this.users = nextData;
          this.page = Number(payload.current_page ?? 1);
          this.lastPage = nextLastPage;
          this.total = Number(payload.total ?? 0);
          this.from = Number(payload.from ?? 0);
          this.to = Number(payload.to ?? 0);
        }
        this.hydrated = true;
        this.syncUrl();
      } catch (error) {
        this.notify('error', error.message || 'Terjadi kesalahan saat memuat data.');
      } finally {
        this.loading = false;
      }
    },
    async changePerPage() {
      await this.loadUsers(1);
    },
    async search() {
      await this.loadUsers(1);
    },
    askDeleteUser(user) {
      if (user.is_current_auth) {
        this.notify('error', 'Akun yang sedang digunakan tidak bisa dihapus.');
        return;
      }

      this.selectedUser = user;
      this.confirmDeleteOpen = true;
    },
    closeDeleteModal() {
      if (this.deleting) {
        return;
      }

      this.confirmDeleteOpen = false;
      this.selectedUser = null;
    },
    async confirmDeleteUser() {
      if (!this.selectedUser || this.deleting) {
        return;
      }

      this.deleting = true;

      try {
        const response = await fetch(this.selectedUser.destroy_url, {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.csrfToken,
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            _method: 'DELETE',
          }),
        });

        if (!response.ok) {
          const payload = await response.json().catch(() => ({}));
          this.notify('error', payload.message || 'Gagal menghapus user.');
          return;
        }

        const payload = await response.json().catch(() => ({}));
        this.notify('success', payload.message || 'User berhasil dihapus.');

        this.confirmDeleteOpen = false;

        if (this.users.length === 1 && this.page > 1) {
          await this.loadUsers(this.page - 1);
          return;
        }

        await this.loadUsers(this.page);
      } finally {
        this.deleting = false;
        this.selectedUser = null;
      }
    },
  };
}
