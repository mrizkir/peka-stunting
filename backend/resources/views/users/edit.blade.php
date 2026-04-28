<x-layouts.app
	title="Edit User"
	eyebrow="Manajemen User"
	heading="Edit User"
	:description="'Perbarui data untuk user: ' . $user->name"
>
	<x-ui.card>
		<form
			action="{{ route('users.update', $user) }}"
			method="POST"
			x-ref="userForm"
			x-data="window.userForm(
				{
					name: @js(old('name', $user->name)),
					email: @js(old('email', $user->email)),
					phone: @js(old('phone', $user->phone)),
					gender: @js(old('gender', $user->gender)),
					birth_date: @js(old('birth_date', optional($user->birth_date)->format('Y-m-d'))),
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
					isEdit: true,
				}
			)"
			novalidate
		>
			@csrf
			@method('PUT')
			@include('users._form', ['submitLabel' => 'Perbarui user', 'user' => $user])
		</form>
	</x-ui.card>
</x-layouts.app>
