export function createRegisterForm(initialForm = {}, initialErrors = {}) {
  return {
    form: {
      name: '',
      email: '',
      phone: '',
      gender: '',
      birth_date: '',
      password: '',
      password_confirmation: '',
      ...initialForm,
    },
    errors: {
      name: '',
      email: '',
      phone: '',
      gender: '',
      birth_date: '',
      password: '',
      password_confirmation: '',
      ...initialErrors,
    },
    asString(value) {
      return typeof value === 'string' ? value : '';
    },
    validateField(field) {
      if (field === 'name') {
        this.errors.name = this.asString(this.form.name).trim() ? '' : 'Nama wajib diisi.';
      }

      if (field === 'email') {
        const email = this.asString(this.form.email).trim();
        if (!email) {
          this.errors.email = 'Email wajib diisi.';
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          this.errors.email = 'Format email tidak valid.';
        } else {
          this.errors.email = '';
        }
      }

      if (field === 'phone') {
        const phone = this.asString(this.form.phone).trim();
        if (!phone) {
          this.errors.phone = 'Nomor HP wajib diisi.';
        } else if (!/^\d{8,15}$/.test(phone)) {
          this.errors.phone = 'Nomor HP harus 8-15 digit angka.';
        } else {
          this.errors.phone = '';
        }
      }

      if (field === 'gender') {
        if (!this.form.gender) {
          this.errors.gender = 'Jenis kelamin wajib dipilih.';
        } else if (!['L', 'P'].includes(this.form.gender)) {
          this.errors.gender = 'Jenis kelamin harus Laki-laki atau Perempuan.';
        } else {
          this.errors.gender = '';
        }
      }

      if (field === 'birth_date') {
        if (!this.form.birth_date) {
          this.errors.birth_date = '';
        } else {
          const selectedDate = new Date(this.form.birth_date);
          const today = new Date();
          selectedDate.setHours(0, 0, 0, 0);
          today.setHours(0, 0, 0, 0);

          this.errors.birth_date =
            selectedDate <= today ? '' : 'Tanggal lahir tidak boleh lebih dari hari ini.';
        }
      }

      if (field === 'password') {
        if (!this.form.password) {
          this.errors.password = 'Password wajib diisi.';
        } else if (this.form.password.length < 8) {
          this.errors.password = 'Password minimal 8 karakter.';
        } else {
          this.errors.password = '';
        }

        if (this.form.password_confirmation) {
          this.errors.password_confirmation =
            this.form.password === this.form.password_confirmation
              ? ''
              : 'Konfirmasi password tidak cocok.';
        }
      }

      if (field === 'password_confirmation') {
        if (!this.form.password_confirmation) {
          this.errors.password_confirmation = 'Konfirmasi password wajib diisi.';
        } else if (this.form.password !== this.form.password_confirmation) {
          this.errors.password_confirmation = 'Konfirmasi password tidak cocok.';
        } else {
          this.errors.password_confirmation = '';
        }
      }
    },
    validateForm() {
      ['name', 'email', 'phone', 'gender', 'birth_date', 'password', 'password_confirmation']
        .forEach((field) => this.validateField(field));

      return !Object.values(this.errors).some((message) => message);
    },
    submitForm() {
      if (this.validateForm()) {
        this.$refs.registerForm.submit();
      }
    },
  };
}
