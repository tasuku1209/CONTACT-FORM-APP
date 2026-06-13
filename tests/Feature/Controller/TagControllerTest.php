<?php

namespace Tests\Feature\Controller;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証ユーザーはタグ編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('tags.edit', $tag));

        $response->assertStatus(200);
        $response->assertViewHas('tag');
    }

    public function test_認証ユーザーはタグを作成できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('tags.store'), [
                'name' => 'testタグ',
            ]);

        $response->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('tags', [
            'name' => 'testタグ',
        ]);
    }

    public function test_認証ユーザーはタグを更新できる(): void
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create([
            'name' => 'testタグ',
        ]);

        $response = $this->actingAs($user)
            ->put(route('tags.update', $tag), [
                'name' => '更新タグ',
            ]);

        $response->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '更新タグ',
        ]);
    }

    public function test_認証ユーザーはタグを削除できる(): void
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('tags.destroy', $tag));

        $response->assertRedirect(route('admin.index'));

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    public function test_未認証ユーザーはタグ作成できない(): void
    {
        $response = $this->post(route('tags.store'), [
            'name' => 'テストタグ',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_未認証ユーザーはタグ更新できない(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->put(route('tags.update', $tag), [
            'name' => '更新タグ',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_未認証ユーザーはタグ削除できない(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->delete(route('tags.destroy', $tag));

        $response->assertRedirect('/login');
    }
}
