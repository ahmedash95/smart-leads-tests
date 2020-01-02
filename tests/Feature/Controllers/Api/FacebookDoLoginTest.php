<?php

namespace Tests\Feature;

use App\User;
use Facebook\Facebook;
use Facebook\Helpers\FacebookRedirectLoginHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacebookDoLoginTest extends TestCase
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

    public function testItSetsTokenAfterFacebookRedirectsBack()
    {
        $loginHelperMock = $this->createMock(FacebookRedirectLoginHelper::class);
        $loginHelperMock->method('getAccessToken')
            ->willReturn('TOKEN');

        $fbMock = $this->createMock(Facebook::class);
        $fbMock->method('getRedirectLoginHelper')
            ->willReturn($loginHelperMock);

        $this->instance(Facebook::class, $fbMock);

        $this->user->revokeFBToken();

        $response = $this->actingAs($this->user)
            ->get('/facebook/login');


        $response->assertStatus(302);
        $this->assertEquals($this->user->fresh()->getFBToken(), 'TOKEN');
    }

}
