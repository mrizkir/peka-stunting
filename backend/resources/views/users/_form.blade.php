@php
  $isEdit = isset($user);
@endphp

<div class="grid gap-5 md:grid-cols-2">
  <div class="md:col-span-2">
    <x-ui.input
      label="Nama"
      name="name"
      type="text"
      :value="old('name', $user->name ?? '')"
      required
      x-model="form.name"
      @blur="validateField('name')"
      x-bind:aria-invalid="errors.name ? 'true' : 'false'"
      x-bind:class="errors.name ? 'border-error focus:border-error focus:ring-error/20' : ''"
    />
    <p x-show="errors.name" x-text="errors.name" class="text-error mt-2 text-sm"></p>
    @error('name')
      <p class="text-error mt-2 text-sm">{{ $message }}</p>
    @enderror
  </div>

  <div>
    <x-ui.input
      label="Email"
      name="email"
      type="email"
      :value="old('email', $user->email ?? '')"
      required
      x-model="form.email"
      @blur="validateField('email')"
      x-bind:aria-invalid="errors.email ? 'true' : 'false'"
      x-bind:class="errors.email ? 'border-error focus:border-error focus:ring-error/20' : ''"
    />
    <p x-show="errors.email" x-text="errors.email" class="text-error mt-2 text-sm"></p>
    @error('email')
      <p class="text-error mt-2 text-sm">{{ $message }}</p>
    @enderror
  </div>

  <div>
    <x-ui.input
      label="No. HP"
      name="phone"
      type="text"
      inputmode="numeric"
      maxlength="15"
      pattern="[0-9]*"
      :value="old('phone', $user->phone ?? '')"
      required
      x-model="form.phone"
      @blur="validateField('phone')"
      @input="form.phone = $event.target.value.replace(/\D/g, '').slice(0, 15); $event.target.value = form.phone; validateField('phone')"
      x-bind:aria-invalid="errors.phone ? 'true' : 'false'"
      x-bind:class="errors.phone ? 'border-error focus:border-error focus:ring-error/20' : ''"
    />
    <p x-show="errors.phone" x-text="errors.phone" class="text-error mt-2 text-sm"></p>
    @error('phone')
      <p class="text-error mt-2 text-sm">{{ $message }}</p>
    @enderror
  </div>

  <div>
    <x-ui.select
      label="Jenis Kelamin"
      name="gender"
      x-model="form.gender"
      @change="validateField('gender')"
      @blur="validateField('gender')"
      x-bind:aria-invalid="errors.gender ? 'true' : 'false'"
      x-bind:class="errors.gender ? 'border-error focus:border-error focus:ring-error/20' : ''"
    >
      <option value="">Pilih jenis kelamin</option>
      <option value="L" @selected(old('gender', $user->gender ?? '') === 'L')>Laki-laki</option>
      <option value="P" @selected(old('gender', $user->gender ?? '') === 'P')>Perempuan</option>
    </x-ui.select>
    <p x-show="errors.gender" x-text="errors.gender" class="text-error mt-2 text-sm"></p>
    @error('gender')
      <p class="text-error mt-2 text-sm">{{ $message }}</p>
    @enderror
  </div>

  <div>
    <x-ui.input
      label="Tanggal Lahir"
      name="birth_date"
      type="date"
      :value="old('birth_date', isset($user->birth_date) ? $user->birth_date->format('Y-m-d') : '')"
      x-model="form.birth_date"
      @blur="validateField('birth_date')"
      @change="validateField('birth_date')"
      x-bind:aria-invalid="errors.birth_date ? 'true' : 'false'"
      x-bind:class="errors.birth_date ? 'border-error focus:border-error focus:ring-error/20' : ''"
    />
    <p x-show="errors.birth_date" x-text="errors.birth_date" class="text-error mt-2 text-sm"></p>
    @error('birth_date')
      <p class="text-error mt-2 text-sm">{{ $message }}</p>
    @enderror
  </div>

  <div>
    <x-ui.input
      :label="$isEdit ? 'Password (opsional)' : 'Password'"
      name="password"
      type="password"
      :required="!$isEdit"
      x-model="form.password"
      @blur="validateField('password')"
      x-bind:aria-invalid="errors.password ? 'true' : 'false'"
      x-bind:class="errors.password ? 'border-error focus:border-error focus:ring-error/20' : ''"
    />
    <p x-show="errors.password" x-text="errors.password" class="text-error mt-2 text-sm"></p>
    @error('password')
      <p class="text-error mt-2 text-sm">{{ $message }}</p>
    @enderror
  </div>

  <div>
    <x-ui.input
      :label="$isEdit ? 'Konfirmasi Password (opsional)' : 'Konfirmasi Password'"
      name="password_confirmation"
      type="password"
      :required="!$isEdit"
      x-model="form.password_confirmation"
      @blur="validateField('password_confirmation')"
      x-bind:aria-invalid="errors.password_confirmation ? 'true' : 'false'"
      x-bind:class="errors.password_confirmation ? 'border-error focus:border-error focus:ring-error/20' : ''"
    />
    <p x-show="errors.password_confirmation" x-text="errors.password_confirmation" class="text-error mt-2 text-sm"></p>
  </div>
</div>

<div class="mt-6 flex items-center gap-3">
  <x-ui.button
    type="button"
    @click.prevent="submitForm()"
    x-bind:disabled="submitting"
  >
    <span x-show="!submitting">{{ $submitLabel }}</span>
    <span x-show="submitting">Menyimpan...</span>
  </x-ui.button>
  <a href="{{ route('users.index') }}" class="text-base-content/70 hover:text-base-content text-sm font-medium">Batal</a>
</div>
