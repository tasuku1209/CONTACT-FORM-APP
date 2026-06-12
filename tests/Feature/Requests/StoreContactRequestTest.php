<?php

namespace Tests\Feature\Requests;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_有効な入力内容でバリデーション通過する(): void
    {
        $category = Category::factory()->create();

        $tags = Tag::factory()->count(2)->create();

        $response = $this->post(route('contacts.confirm'), [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-1-1',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tags' => $tags->pluck('id')->toArray(),
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_不正な電話番号形式はバリデーションエラーになる(): void
    {
        // Arrange
        $category = Category::factory()->create();

        // Act
        $response = $this->post(route('contacts.confirm'), [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => 'abc123',
            'address' => '東京都渋谷区1-1-1',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
        ]);

        // Assert
        $response->assertSessionHasErrors([
            'tel',
        ]);
    }
}
