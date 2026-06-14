<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
	/** @use HasFactory<UserFactory> */
	use HasApiTokens, HasFactory, HasRoles, InteractsWithMedia, Notifiable;

	public const MEDIA_COLLECTION_PROFILE_PHOTO = 'profile-photo';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var list<string>
	 */
	protected $fillable = [
		'name',
		'email',
		'password',
		'phone',
		'gender',
		'birth_date',
	];

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var list<string>
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

	/**
	 * Get the attributes that should be cast.
	 *
	 * @return array<string, string>
	 */
	protected function casts(): array
	{
		return [
			'email_verified_at' => 'datetime',
			'birth_date' => 'date',
			'password' => 'hashed',
		];
	}

	public function updatedEducationContents(): HasMany
	{
		return $this->hasMany(EducationContent::class, 'updated_by');
	}

	public function registerMediaCollections(): void
	{
		$this->addMediaCollection(self::MEDIA_COLLECTION_PROFILE_PHOTO)
			->singleFile()
			->useDisk('public')
			->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
	}

	public function profilePhotoMedia(): ?Media
	{
		return $this->getFirstMedia(self::MEDIA_COLLECTION_PROFILE_PHOTO);
	}

	public function profilePhotoUrl(): ?string
	{
		$media = $this->profilePhotoMedia();
		if ($media === null) {
			return null;
		}

		$url = $media->getFullUrl();

		if ($media->updated_at !== null) {
			$url .= '?v='.$media->updated_at->getTimestamp();
		}

		return $url;
	}
}
