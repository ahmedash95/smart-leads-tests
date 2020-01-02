<?php


namespace Tests\Feature\Controllers\Api;

use App\Http\Resources\PageCollection;
use App\Jobs\SyncFBPages;
use App\Page;
use App\User;
use Facebook\Facebook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Mockery\Mock;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class PagesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testFetchPages() {
        $user = factory(User::class)->create();
        $pages = factory(Page::class,3)->create(['user_id' => $user->id]);
        $collection = PageCollection::collection($pages);
        $expectedResponse = ['pages' => $collection->jsonSerialize()];

        $response = $this->actingAs($user)->get('/api/pages');
        $response->assertJson($expectedResponse);
    }

    public function testSyncPagesDispatchesTheJob(){
        Bus::fake();

        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->post('/api/pages/sync');
        $response->assertStatus(200);

        Bus::assertDispatched(SyncFBPages::class, function ($job) use ($user) {
            return $job->user->id === $user->id;
        });
    }

    public function testPageConnect() {
        $mock = $this->createMock(Facebook::class);
        $mock->method('post')->willReturn(null);
        $this->instance(Facebook::class,$mock);


        $user = factory(User::class)->create();
        $page = factory(Page::class)->create(['user_id' => $user->id]);
        $this->assertNull($page->connection_status);
        $response = $this->actingAs($user)->post('/api/pages/'.$page->id.'/connection',[
            'status' => 'connected'
        ]);

        $response->assertStatus(201);
        $this->assertEquals(Page::CONNECTION_STATUS_CONNECTED,$page->fresh()->connection_status);
    }

    public function testPageDisconnect() {
        $mock = $this->createMock(Facebook::class);
        $mock->method('delete')->willReturn(null);
        $this->instance(Facebook::class,$mock);


        $user = factory(User::class)->create();
        $page = factory(Page::class)->create(['user_id' => $user->id]);
        $this->assertNull($page->connection_status);
        $response = $this->actingAs($user)->post('/api/pages/'.$page->id.'/connection',[
            'status' => 'disconnected'
        ]);

        $response->assertStatus(201);
        $this->assertEquals(Page::CONNECTION_STATUS_DISCONNECTED,$page->fresh()->connection_status);
    }

    public function testPageWrongStatusValidation() {
        $user = factory(User::class)->create();
        $page = factory(Page::class)->create(['user_id' => $user->id]);
        $response = $this->actingAs($user)->post('/api/pages/'.$page->id.'/connection',[
            'status' => 'fake_status'
        ]);

        $response->assertStatus(302);
    }

    public function testItCanNotAccessOtherUserPage() {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $page = factory(Page::class)->create(['user_id' => $user2->id]);
        $response = $this->actingAs($user)->post('/api/pages/'.$page->id.'/connection',[
            'status' => Page::CONNECTION_STATUS_DISCONNECTED
        ]);

        $response->assertStatus(404);
    }
}
