<?php


namespace Tests\Feature\Controllers\Api;


use App\User;
use App\WebhookReceiver\Receiver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacebookWebHookTest extends TestCase
{
    use RefreshDatabase;

    public function testWebHooksVerification()
    {
        config()->set('services.facebook.webhook_verify', 'TOKEN');

        $params = [
            'hub_challenge' => 'CHALLENGE_ACCEPTED',
            'hub_verify_token' => 'TOKEN',
        ];
        $response = $this->get('/facebook/webhook?'.http_build_query($params));
        $this->assertEquals('CHALLENGE_ACCEPTED', $response->content());
    }

    public function testWebHooksVerificationFailureReturns400()
    {
        config()->set('services.facebook.webhook_verify', 'TOKEN');

        $params = [
            'hub_challenge' => 'CHALLENGE_ACCEPTED',
            'hub_verify_token' => 'BAD_TOKEN',
        ];
        $response = $this->get('/facebook/webhook?'.http_build_query($params));

        $response->assertStatus(400);
    }

    public function testWebHookReceivesRequestsAndStoreThem()
    {
        $data = [
            'page_id' => 1,
            'form' => [
                'key' => 'value',
            ]
        ];

        $this->instance(Receiver::class,new TestPHPInput($data));

        $this->post('/facebook/webhook');

        $this->assertDatabaseHas('web_hooks',[
            'source' => 'facebook',
           'body' => json_encode($data),
        ]);
    }
}


class TestPHPInput implements Receiver {

    private $content;

    public function __construct(array $content)
    {
        $this->content = $content;
    }

    public function read(){}

    public function content(): string
    {
        return json_encode($this->content);
    }
}
