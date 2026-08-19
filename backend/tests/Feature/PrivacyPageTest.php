<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivacyPageTest extends TestCase
{
	public function test_guest_can_view_privacy_policy_page(): void
	{
		$this->get(route('privacy'))
			->assertOk()
			->assertSee('Kebijakan Privasi PEKA Stunting')
			->assertSee('id.ac.anugerahbintan.pekastunting')
			->assertSee('nining@anugerahbintan.ac.id');
	}
}
