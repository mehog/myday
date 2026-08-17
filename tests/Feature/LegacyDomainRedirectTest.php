<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegacyDomainRedirectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'https://nuptoria.com');
        config()->set('app.legacy_redirect_hosts', [
            'nasdan.app',
            'www.nasdan.app',
            'nasdan.ba',
            'www.nasdan.ba',
        ]);
    }

    public function test_nasdan_app_invitation_path_redirects_to_nuptoria_with_query(): void
    {
        $this->get('http://nasdan.app/e/demo-islamsko?locale=bs')
            ->assertRedirect('https://nuptoria.com/e/demo-islamsko?locale=bs')
            ->assertStatus(301);
    }

    public function test_www_nasdan_ba_admin_path_redirects_to_nuptoria(): void
    {
        $this->get('http://www.nasdan.ba/admin')
            ->assertRedirect('https://nuptoria.com/admin')
            ->assertStatus(301);
    }

    public function test_canonical_nuptoria_host_is_not_redirected(): void
    {
        $this->get('http://nuptoria.com/')
            ->assertOk();
    }
}
