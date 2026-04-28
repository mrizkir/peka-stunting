import './bootstrap';
import Alpine from 'alpinejs';
import { createRegisterForm } from './forms/register-form';
import { createUserForm } from './forms/user-form';
import { createUsersIndex } from './pages/users-index';

window.Alpine = Alpine;
window.registerForm = createRegisterForm;
window.userForm = createUserForm;
window.usersIndex = createUsersIndex;
window.usersIndexFallback = (config) => ({
  users: config.initialUsers ?? [],
  q: config.initialQ ?? '',
  perPage: Number(config.initialPerPage ?? 10),
  page: Number(config.initialPage ?? 1),
  lastPage: Number(config.initialLastPage ?? 1),
  total: Number(config.initialTotal ?? 0),
  from: Number(config.initialFrom ?? 0),
  to: Number(config.initialTo ?? 0),
  loading: false,
  toast: { show: false, type: 'success', message: '' },
  init() {},
  search() {},
  changePerPage() {},
  deleteUser() {},
  loadUsers() {},
  pageItems() { return []; },
});
window.usersIndexFromScript = (id) => {
  const configEl = document.getElementById(id);
  let config = {};

  if (configEl) {
    try {
      config = JSON.parse(configEl.textContent || '{}');
    } catch (_error) {
      config = {};
    }
  }

  return window.usersIndex ? window.usersIndex(config) : window.usersIndexFallback(config);
};

Alpine.start();
