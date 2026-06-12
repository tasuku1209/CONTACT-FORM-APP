<?php

namespace Tests\Feature\Requests;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_自分のタグ名はそのまま更新できる(): void
    {
        // Arrange
        $user = User::factory()->create();

        $tag = Tag::factory()->create([
            'name' => 'テスト',
        ]);

        // Act
        $response = $this->actingAs($user)->put(
            route('tags.update', $tag),
            [
                'name' => 'テスト',
            ]
        );

        // Assert
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'テスト',
        ]);
    }

    public function test_他で使用されているタグ名への変更は拒否される(): void
    {
        // Arrange
        $user = User::factory()->create();

        Tag::factory()->create([
            'name' => '既存タグ',
        ]);

        $tag = Tag::factory()->create([
            'name' => '元の名前',
        ]);

        // Act
        $response = $this->actingAs($user)->put(
            route('tags.update', $tag),
            [
                'name' => '既存タグ',
            ]
        );

        // Assert
        $response->assertSessionHasErrors([
            'name',
        ]);
    }
}
