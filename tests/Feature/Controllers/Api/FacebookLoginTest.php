<?php

namespace Tests\Feature;

use App\User;
use Facebook\Facebook;
use Facebook\Helpers\FacebookRedirectLoginHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacebookLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    public function testFacebookLoginUrlReturns()
    {
        $loginHelperMock = $this->createMock(FacebookRedirectLoginHelper::class);
        $loginHelperMock->method('getLoginUrl')
            ->willReturn('https://facebook.com/test/fake/login');

        $fbMock = $this->createMock(Facebook::class);
        $fbMock->method('getRedirectLoginHelper')
            ->willReturn($loginHelperMock);

        $this->instance(Facebook::class,$fbMock);

        $response = $this->actingAs($this->user)
                ->post('/api/facebook/login');

        $response->assertStatus(200);
        $response->assertJson([
            'redirect_url' => 'https://facebook.com/test/fake/login'
        ]);
    }

    public function testEmptyResponseWhenTokenExists()
    {
        $this->user->setFBToken('TOKEN');

        $response = $this->actingAs($this->user)
            ->post('/api/facebook/login');

        $response->assertStatus(200);
        $response->assertJson([]);
    }

}
