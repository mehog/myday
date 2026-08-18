<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegacyDomainRedirectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'https://nasdan.app');
        config()->set('app.legacy_redirect_hosts', [
            'nuptoria.com',
            'www.nuptoria.com',
            'www.nasdan.app',
            'nasdan.ba',
            'www.nasdan.ba',
        ]);
    }

    public function test_nuptoria_invitation_path_redirects_to_nasdan_app_with_query(): void
    {
        $this->get('http://nuptoria.com/e/demo-islamsko?locale=bs')
            ->assertRedirect('https://nasdan.app/e/demo-islamsko?locale=bs')
            ->assertStatus(301);
    }

    public function test_www_nasdan_ba_admin_path_redirects_to_nasdan_app(): void
    {
        $this->get('http://www.nasdan.ba/admin')
            ->assertRedirect('https://nasdan.app/admin')
            ->assertStatus(301);
    }

    public function test_canonical_nasdan_app_host_is_not_redirected(): void
    {
        $this->get('http://nasdan.app/')
            ->assertOk();
    }
}
