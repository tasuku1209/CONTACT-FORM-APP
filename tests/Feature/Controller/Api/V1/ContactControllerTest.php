<?php

namespace Tests\Feature\Controller\Api\V1;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_お問い合わせ一覧_ap_iで一覧取得できる(): void
    {
        // Arrange
        $category = Category::factory()->create();

        Contact::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);

        // Act
        $response = $this->getJson('/api/v1/contacts');

        // Assert
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'category',
                        'first_name',
                        'last_name',
                        'gender',
                        'email',
                        'tel',
                        'address',
                        'building',
                        'detail',
                        'tags',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);

        $this->assertCount(
            3,
            $response->json('data')
        );
    }

    public function test_お問い合わせ一覧_ap_iで検索とページネーションができる(): void
    {
        // Arrange
        $category = Category::factory()->create();

        // 検索対象（21件）
        Contact::factory()
            ->count(21)
            ->create([
                'first_name' => '太郎',
                'category_id' => $category->id,
            ]);

        // 検索対象外
        Contact::factory()->create([
            'first_name' => '花子',
            'category_id' => $category->id,
        ]);

        // Act
        $response = $this->getJson(
            '/api/v1/contacts?'.http_build_query([
                'keyword' => '太郎',
                'per_page' => 20,
            ])
        );

        // Assert
        $response->assertStatus(200);

        // 太郎が含まれる
        $response->assertJsonFragment([
            'first_name' => '太郎',
        ]);

        // 1ページ20件
        $this->assertCount(
            20,
            $response->json('data')
        );

        // 検索総件数21件
        $this->assertSame(
            21,
            $response->json('meta.total')
        );
    }

    public function test_お問い合わせ一覧_ap_iでバリデーションエラー時422が返る(): void
    {
        // Act
        $response = $this->getJson('/api/v1/contacts?gender=999');

        // Assert
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'gender',
            ]);
    }

    public function test_お問い合わせ詳細_ap_iで詳細取得できる(): void
    {
        // Arrange
        $category = Category::factory()->create([
            'content' => 'カテゴリーA',
        ]);

        $tagA = Tag::factory()->create([
            'name' => 'タグA',
        ]);

        $tagB = Tag::factory()->create([
            'name' => 'タグB',
        ]);

        $contact = Contact::factory()->create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => 'テストビル',
            'detail' => 'お問い合わせ内容',
            'category_id' => $category->id,
        ]);

        $contact->tags()->attach([
            $tagA->id,
            $tagB->id,
        ]);

        // Act
        $response = $this->getJson(
            "/api/v1/contacts/{$contact->id}"
        );

        // Assert
        $response->assertStatus(200);

        // Contact本体確認
        $response->assertJsonFragment([
            'id' => $contact->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => 'テストビル',
            'detail' => 'お問い合わせ内容',
        ]);

        // category ネスト確認
        $response->assertJsonFragment([
            'id' => $category->id,
            'content' => 'カテゴリーA',
        ]);

        // tags ネスト確認
        $response->assertJsonFragment([
            'id' => $tagA->id,
            'name' => 'タグA',
        ]);

        $response->assertJsonFragment([
            'id' => $tagB->id,
            'name' => 'タグB',
        ]);

        // JSON構造確認
        $response->assertJsonStructure([
            'data' => [
                'id',

                'category' => [
                    'id',
                    'content',
                ],

                'first_name',
                'last_name',
                'gender',
                'email',
                'tel',
                'address',
                'building',
                'detail',

                'tags' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],

                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_お問い合わせ詳細_ap_iで存在しない_i_dの場合404が返る(): void
    {
        // Act
        $response = $this->getJson('/api/v1/contacts/999999');

        // Assert
        $response
            ->assertStatus(404)
            ->assertJson([
                'error' => 'お問い合わせが見つかりませんでした。',
            ]);
    }

    public function test_お問い合わせ作成_ap_iで作成できる(): void
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
            'tag_ids' => $tags->pluck('id')->toArray(),
        ]);

        // Assert
        $response->assertStatus(201);

        $this->assertDatabaseHas('contacts', [
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseCount('contact_tag', 2);
    }

    public function test_お問い合わせ作成_ap_iでバリデーションエラー時422が返る(): void
    {
        // Act
        $response = $this->postJson('/api/v1/contacts', []);

        // Assert
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'gender',
                'email',
                'tel',
                'address',
                'category_id',
                'detail',
            ]);
    }

    public function test_お問い合わせ更新_ap_iで更新できる(): void
    {
        // Arrange
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'first_name' => '更新前',
            'category_id' => $category->id,
        ]);

        // Act
        $response = $this->putJson("/api/v1/contacts/{$contact->id}", [
            'first_name' => '更新後',
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'building' => $contact->building,
            'category_id' => $category->id,
            'detail' => $contact->detail,
        ]);

        // Assert
        $response->assertStatus(200);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '更新後',
        ]);
    }

    public function test_お問い合わせ更新_ap_iで存在しない_i_dの場合404が返る(): void
    {
        // Arrange
        $category = Category::factory()->create();

        // Act
        $response = $this->putJson('/api/v1/contacts/999999', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
        ]);

        // Assert
        $response
            ->assertStatus(404)
            ->assertJson([
                'error' => 'お問い合わせが見つかりませんでした。',
            ]);
    }

    public function test_お問い合わせ更新_ap_iでバリデーションエラー時422が返る(): void
    {
        // Arrange
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        // Act
        $response = $this->putJson("/api/v1/contacts/{$contact->id}", []);

        // Assert
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'gender',
                'email',
                'tel',
                'address',
                'category_id',
                'detail',
            ]);
    }

    public function test_お問い合わせ削除_ap_iで削除できる(): void
    {
        // Arrange
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        // Act
        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

        // Assert
        $response->assertNoContent();

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_お問い合わせ削除_ap_iで存在しない_i_dの場合404が返る(): void
    {
        // Act
        $response = $this->deleteJson('/api/v1/contacts/999999');

        // Assert
        $response
            ->assertStatus(404)
            ->assertJson([
                'error' => 'お問い合わせが見つかりませんでした。',
            ]);
    }
}
