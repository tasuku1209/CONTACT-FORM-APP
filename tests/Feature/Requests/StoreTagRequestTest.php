<?php

namespace Tests\Feature\Requests;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_タグ名は必須である(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->post(
            route('tags.store'),
            [
                'name' => '',
            ]
        );

        // Assert
        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_タグ名は50文字以下である(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->post(
            route('tags.store'),
            [
                'name' => str_repeat('あ', 51),
            ]
        );

        // Assert
        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_タグ名は重複できない(): void
    {
        // Arrange
        $user = User::factory()->create();

        Tag::factory()->create([
            'name' => 'テスト',
        ]);

        // Act
        $response = $this->actingAs($user)->post(
            route('tags.store'),
            [
                'name' => 'テスト',
            ]
        );

        // Assert
        $response->assertSessionHasErrors([
            'name',
        ]);
    }
}
