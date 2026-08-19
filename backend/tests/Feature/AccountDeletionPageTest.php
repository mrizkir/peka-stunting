<?php

namespace Tests\Feature;

use Tests\TestCase;

class AccountDeletionPageTest extends TestCase
{
	public function test_guest_can_view_account_deletion_page(): void
	{
		$this->get(route('account-deletion'))
			->assertOk()
			->assertSee('Permintaan penghapusan akun PEKA Stunting')
			->assertSee('Zona berbahaya')
			->assertSee('nining@anugerahbintan.ac.id');
	}
}
