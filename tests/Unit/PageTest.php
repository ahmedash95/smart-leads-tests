<?php

namespace Tests\Unit;

use App\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    public function testPageConnectionStatusCanBeUpdated()
    {
        /**
         * @var Page $page1
         * @var Page $page2
         * @var Page $page3
         */
        $page1 = factory(Page::class)->create();
        $page2 = factory(Page::class)->create();
        $page3 = factory(Page::class)->create();

        $this->assertNull($page1->connection_status);

        $page1->updateConnectionStatus(Page::CONNECTION_STATUS_CONNECTED);
        $page2->updateConnectionStatus(Page::CONNECTION_STATUS_DISCONNECTED);
        $page3->updateConnectionStatus(Page::CONNECTION_STATUS_ERROR);

        $this->assertEquals(Page::CONNECTION_STATUS_CONNECTED,$page1->fresh()->connection_status);
        $this->assertEquals(Page::CONNECTION_STATUS_DISCONNECTED,$page2->fresh()->connection_status);
        $this->assertEquals(Page::CONNECTION_STATUS_ERROR,$page3->fresh()->connection_status);
    }
    public function testPageConnectionStatusThrowsExceptionForInvalidStatus()
    {
        /**
         * @var Page $page1
         */
        $page1 = factory(Page::class)->create();
        $this->assertNull($page1->connection_status);

        $this->expectExceptionMessage('Page::updateConnectionStatus unknown status FAKE');
        $page1->updateConnectionStatus('FAKE');
    }
}
