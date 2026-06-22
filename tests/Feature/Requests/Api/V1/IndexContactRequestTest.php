<?php

namespace Tests\Feature\Requests\Api\V1;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_api一覧取得において有効な検索条件で検索できる(): void
    {
        // Arrange
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        // 検索条件に一致するデータ
        Contact::factory()
            ->count(21)
            ->create([
                'first_name' => '太郎',
                'gender' => 1,
                'category_id' => $categoryA->id,
                'created_at' => '2026-06-15 10:00:00',
            ]);

        // 検索条件に一致しないデータ
        Contact::factory()->create([
            'first_name' => '花子',
            'gender' => 2,
            'category_id' => $categoryB->id,
            'created_at' => '2026-06-16 10:00:00',
        ]);

        // Act
        $response = $this->getJson('/api/v1/contacts?' . http_build_query([
            'keyword' => '太郎',
            'gender' => 1,
            'category_id' => $categoryA->id,
            'date' => '2026-06-15',
            'per_page' => 20,
        ]));

        // Assert
        $response->assertStatus(200);

        // 返却件数（1ページ20件）
        $this->assertCount(
            20,
            $response->json('data')
        );

        // 検索総件数
        $this->assertSame(
            21,
            $response->json('meta.total')
        );
    }

    // 以下、異常系テスト
    public function test_不正な性別は422エラーになる(): void
    {
        $response = $this->getJson('/api/v1/contacts?gender=999');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'gender',
            ]);
    }

    public function test_存在しないカテゴリ_i_dは422エラーになる(): void
    {
        $response = $this->getJson('/api/v1/contacts?category_id=999999');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'category_id',
            ]);
    }

    public function test_不正な日付形式は422エラーになる(): void
    {
        $response = $this->getJson('/api/v1/contacts?date=invalid-date');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'date',
            ]);
    }

    public function test_per_pageが0の場合422エラーになる(): void
    {
        $response = $this->getJson('/api/v1/contacts?per_page=0');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'per_page',
            ]);
    }

    public function test_per_pageが101の場合422エラーになる(): void
    {
        $response = $this->getJson('/api/v1/contacts?per_page=101');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'per_page',
            ]);
    }

    public function test_pageが0の場合422エラーになる(): void
    {
        $response = $this->getJson('/api/v1/contacts?page=0');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'page',
            ]);
    }

    public function test_keywordが256文字以上の場合422エラーになる(): void
    {
        $keyword = str_repeat('a', 256);

        $response = $this->getJson('/api/v1/contacts?keyword=' . $keyword);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'keyword',
            ]);
    }
}
