<?php

namespace Tests\Feature;

use App\User;
use Facebook\Facebook;
use Facebook\Helpers\FacebookRedirectLoginHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacebookDisconnectTest extends TestCase
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

    public function testDeleteToken()
    {
        $this->user->setFBToken('BLABLA');
        $this->assertEquals('BLABLA', $this->user->fresh()->getFBToken());

        $response = $this->actingAs($this->user)
            ->post('/api/facebook/disconnect');

        $response->assertStatus(200);
        $response->assertJson([]);
        $this->assertNull($this->user->fresh()->getFBToken());
    }

}
