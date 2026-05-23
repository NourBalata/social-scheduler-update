<?php

namespace Tests\Feature;

use App\Models\FacebookPage;
use App\Models\Plan;
use App\Models\ScheduledPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ScheduledPostsTest extends TestCase
{
    use RefreshDatabase;

    private function createUser()
    {
        $plan = Plan::create([
            'name'        => 'Test Plan',
            'slug'        => 'test-plan',
            'price'       => 0,
            'posts_limit' => 10,
            'pages_limit' => 1,
            'active'      => true,
        ]);

        return User::create([
            'name'     => 'Test User',
            'email'    => 'test' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'plan_id'  => $plan->id,
        ]);
    }

    private function createPage($userId)
    {
        return FacebookPage::create([
            'user_id'      => $userId,
            'page_id'      => 'test_page_123',
            'page_name'    => 'Test Page',
            'access_token' => 'fake_token_for_testing',
            'is_active'    => true,
        ]);
    }

    private function createPost($pageId, $userId, $scheduledAt)
    {
        return ScheduledPost::create([
            'user_id'          => $userId,
            'facebook_page_id' => $pageId,
            'status'           => 'pending',
            'scheduled_at'     => $scheduledAt,
            'content'          => 'تجربة',
        ]);
    }

    public function test_pending_published()
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['id' => '123456789'], 200),
        ]);

        $user = $this->createUser();
        $page = $this->createPage($user->id);
        $post = $this->createPost($page->id, $user->id, now()->subMinute());

        $this->artisan('posts:publish');

        $this->assertDatabaseHas('scheduled_posts', [
            'id'     => $post->id,
            'status' => 'published',
        ]);
    }

    public function test_post_pending()
    {
        $user = $this->createUser();
        $page = $this->createPage($user->id);
        $post = $this->createPost($page->id, $user->id, now()->addHour());

        $this->artisan('posts:publish');

        $this->assertDatabaseHas('scheduled_posts', [
            'id'     => $post->id,
            'status' => 'pending',
        ]);
    }

  public function test_post_fails_when_facebook_returns_error()
{
    Http::fake([
        'graph.facebook.com/*' => Http::response([
            'error' => ['message' => 'Invalid OAuth access token.']
        ], 400),
    ]);

    $user = $this->createUser();
    $page = $this->createPage($user->id);
    $post = $this->createPost($page->id, $user->id, now()->subMinute());

    $this->expectException(\Exception::class); // ← أضيفي هاد

    $this->artisan('posts:publish');

    $this->assertDatabaseHas('scheduled_posts', [
        'id'     => $post->id,
        'status' => 'failed',
    ]);

    $this->assertNotNull(
        ScheduledPost::find($post->id)->error_message
    );
}
}