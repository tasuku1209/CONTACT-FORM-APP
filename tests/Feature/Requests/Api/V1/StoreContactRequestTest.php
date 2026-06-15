<?php

namespace Tests\Feature\Requests\Api\V1;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_正しい入力でお問い合わせを作成できる(): void
    {
        // Arrange
        $category = Category::factory()->create();

        $tags = Tag::factory()->count(2)->create();

        // Act
        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => [
                $tags[0]->id,
                $tags[1]->id,
            ],
        ]);

        // Assert
        $response->assertStatus(201);
    }

    public function test_姓が未入力の場合422エラーになる(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name',
            ]);
    }

    public function test_不正な性別は422エラーになる(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 999,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'gender',
            ]);
    }

    public function test_不正な電話番号は422エラーになる(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '090-1234-5678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'tel',
            ]);
    }

    public function test_存在しないカテゴリ_i_dは422エラーになる(): void
    {
        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => 999999,
            'detail' => 'お問い合わせ内容です',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'category_id',
            ]);
    }

    public function test_存在しないタグ_i_dは422エラーになる(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => [999999],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'tag_ids.0',
            ]);
    }
}
