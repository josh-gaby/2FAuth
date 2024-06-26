<?php

namespace Tests\Feature\Http;

use App\Http\Controllers\SystemController;
use App\Models\User;
use App\Notifications\TestEmailSettingNotification;
use App\Services\ReleaseRadarService;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\FeatureTestCase;

/**
 * SystemControllerTest test class
 */
#[CoversClass(SystemController::class)]
#[CoversClass(TestEmailSettingNotification::class)]

class SystemControllerTest extends FeatureTestCase
{
    /**
     * @var \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable
     */
    protected $user;

    protected $admin;

    /**
     * @test
     */
    public function setUp() : void
    {
        parent::setUp();

        $this->user  = User::factory()->create();
        $this->admin = User::factory()->administrator()->create();
    }

    /**
     * @test
     */
    public function test_infos_returns_unauthorized()
    {
        $response = $this->json('GET', '/system/infos')
            ->assertUnauthorized();
    }

    /**
     * @test
     */
    public function test_infos_returns_forbidden()
    {
        $response = $this->actingAs($this->user, 'api-guard')
            ->json('GET', '/system/infos')
            ->assertForbidden();
    }

    /**
     * @test
     */
    public function test_infos_returns_only_base_collection()
    {
        $response = $this->actingAs($this->admin, 'api-guard')
            ->json('GET', '/system/infos')
            ->assertOk()
            ->assertJsonStructure([
                'common' => [
                    'Date',
                    'userAgent',
                    'Version',
                    'Environment',
                    'Install path',
                    'Debug',
                    'Cache driver',
                    'Log channel',
                    'Log level',
                    'DB driver',
                    'PHP version',
                    'Operating system',
                    'interface',
                    'Auth guard',
                    'webauthn user verification',
                    'Trusted proxies',
                    'lastRadarScan',
                ],
            ]);
    }

    /**
     * @test
     */
    public function test_infos_returns_proxy_collection_when_signed_in_behind_proxy()
    {
        $response = $this->actingAs($this->admin, 'reverse-proxy-guard')
            ->json('GET', '/system/infos')
            ->assertOk()
            ->assertJsonStructure([
                'common' => [
                    'Auth proxy logout url',
                    'Auth proxy header for user',
                    'Auth proxy header for email',
                ],
            ]);
    }

    /**
     * @test
     */
    public function test_latestrelease_runs_manual_scan()
    {
        $releaseRadarService = $this->mock(ReleaseRadarService::class)->makePartial();
        $releaseRadarService->shouldReceive('manualScan')
            ->once()
            ->andReturn('new_release');

        $response = $this->json('GET', '/system/latestRelease')
            ->assertOk()
            ->assertJson([
                'newRelease' => 'new_release',
            ]);
    }

    /**
     * @test
     */
    public function test_testEmail_sends_a_notification()
    {
        Notification::fake();

        $response = $this->actingAs($this->admin, 'web-guard')
            ->json('POST', '/system/test-email', []);

        $response->assertStatus(200);

        Notification::assertSentTo($this->admin, TestEmailSettingNotification::class);
    }

    /**
     * @test
     */
    public function test_testEmail_renders_to_email()
    {
        $mail = (new TestEmailSettingNotification('test_token'))->toMail($this->user)->render();

        $this->assertStringContainsString(
            Lang::get('notifications.test_email_settings.reason'),
            $mail
        );
    }

    /**
     * @test
     */
    public function test_testEmail_returns_unauthorized()
    {
        $response = $this->json('GET', '/system/infos')
            ->assertUnauthorized();
    }

    /**
     * @test
     */
    public function test_testEmail_returns_forbidden()
    {
        $response = $this->actingAs($this->user, 'api-guard')
            ->json('GET', '/system/infos')
            ->assertForbidden();
    }

    /**
     * @test
     */
    public function test_clearCache_returns_success()
    {
        $response = $this->json('GET', '/system/clear-cache');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_optimize_returns_success()
    {
        $response = $this->json('GET', '/system/optimize');

        $response->assertStatus(200);
    }
}
