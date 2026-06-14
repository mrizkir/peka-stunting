<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;

class UserProfileService
{
	public function updateProfilePhoto(User $user, UploadedFile $file): void
	{
		$user
			->addMedia($file)
			->toMediaCollection(User::MEDIA_COLLECTION_PROFILE_PHOTO);
	}

	public function deleteProfilePhoto(User $user): void
	{
		$user->clearMediaCollection(User::MEDIA_COLLECTION_PROFILE_PHOTO);
	}

	public function profilePhotoUrl(User $user): ?string
	{
		return $user->profilePhotoUrl();
	}
}
