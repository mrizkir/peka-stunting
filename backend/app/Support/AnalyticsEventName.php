<?php

namespace App\Support;

class AnalyticsEventName
{
	public const APP_OPEN = 'app_open';

	public const SESSION_START = 'session_start';

	public const SESSION_END = 'session_end';

	public const SCREEN_VIEW = 'screen_view';

	public const EDUCATION_CONTENT_VIEW = 'education_content_view';

	public const VIDEO_PLAY = 'video_play';

	public const CALCULATOR_STARTED = 'calculator_started';

	public const CALCULATOR_COMPLETED = 'calculator_completed';

	public const SCREENING_COMPLETED = 'screening_completed';

	public const LOGIN_SUCCESS = 'login_success';

	public const REGISTER_SUCCESS = 'register_success';

	public const LOGOUT = 'logout';

	public const CHILD_REGISTERED = 'child_registered';

	public const MEASUREMENT_SAVED = 'measurement_saved';

	/**
	 * @return list<string>
	 */
	public static function allowed(): array
	{
		return [
			self::APP_OPEN,
			self::SESSION_START,
			self::SESSION_END,
			self::SCREEN_VIEW,
			self::EDUCATION_CONTENT_VIEW,
			self::VIDEO_PLAY,
			self::CALCULATOR_STARTED,
			self::CALCULATOR_COMPLETED,
			self::SCREENING_COMPLETED,
			self::LOGIN_SUCCESS,
			self::REGISTER_SUCCESS,
			self::LOGOUT,
			self::CHILD_REGISTERED,
			self::MEASUREMENT_SAVED,
		];
	}
}
