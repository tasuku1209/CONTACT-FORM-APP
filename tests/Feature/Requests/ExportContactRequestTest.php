<?php

namespace Tests\Feature\Requests;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_有効な検索条件で_cs_vエクスポートできる(): void
    {
        // Arrange
        $user = User::factory()->create();

        $category = Category::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->get(route('contacts.export', [
                'keyword' => 'テスト',
                'gender' => 1,
                'category_id' => $category->id,
                'date' => now()->toDateString(),
            ]));

        // Assert
        $response->assertSessionHasNoErrors();
    }

    public function test_不正な性別値はバリデーションエラーになる(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->get(route('contacts.export', [
                'gender' => 999,
            ]));

        // Assert
        $response->assertSessionHasErrors([
            'gender',
        ]);
    }

    public function test_存在しないカテゴリ_i_dはバリデーションエラーになる(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->get(route('contacts.export', [
                'category_id' => 999,
            ]));

        // Assert
        $response->assertSessionHasErrors([
            'category_id',
        ]);
    }
}
