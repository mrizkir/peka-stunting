<x-layouts.app
	title="Tambah User"
	eyebrow="Manajemen User"
	heading="Tambah User Baru"
	description="Buat akun user baru untuk akses aplikasi."
>
	<x-ui.card>
		<form
			action="{{ route('users.store') }}"
			method="POST"
			x-ref="userForm"
			x-data="window.userForm(
				{
					name: @js(old('name', '')),
					email: @js(old('email', '')),
					phone: @js(old('phone', '')),
					gender: @js(old('gender', '')),
					birth_date: @js(old('birth_date', '')),
					password: '',
					password_confirmation: '',
				},
				{
					name: @js($errors->first('name')),
					email: @js($errors->first('email')),
					phone: @js($errors->first('phone')),
					gender: @js($errors->first('gender')),
					birth_date: @js($errors->first('birth_date')),
					password: @js($errors->first('password')),
					password_confirmation: @js($errors->first('password_confirmation')),
				},
				{
					isEdit: false,
				}
			)"
			novalidate
		>
			@csrf
			@include('users._form', ['submitLabel' => 'Simpan user'])
		</form>
	</x-ui.card>
</x-layouts.app>
