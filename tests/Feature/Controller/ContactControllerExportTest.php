<?php

namespace Tests\Feature\Controller;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証ユーザーは条件付きで_csvをダウンロードできる(): void
    {
        // Arrange
        $user = User::factory()->create();

        $categoryA = Category::factory()->create([
            'content' => 'カテゴリA',
        ]);

        $categoryB = Category::factory()->create([
            'content' => 'カテゴリB',
        ]);

        // CSVに含まれるデータ
        Contact::factory()->create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test1@example.com',
            'category_id' => $categoryA->id,
            'created_at' => now(),
        ]);

        // keywordで除外
        Contact::factory()->create([
            'first_name' => '次郎',
            'gender' => 1,
            'category_id' => $categoryA->id,
            'created_at' => now(),
        ]);

        // genderで除外
        Contact::factory()->create([
            'first_name' => '太郎',
            'gender' => 2,
            'category_id' => $categoryA->id,
            'created_at' => now(),
        ]);

        // categoryで除外
        Contact::factory()->create([
            'first_name' => '太郎',
            'gender' => 1,
            'category_id' => $categoryB->id,
            'created_at' => now(),
        ]);

        // dateで除外
        Contact::factory()->create([
            'first_name' => '太郎',
            'gender' => 1,
            'category_id' => $categoryA->id,
            'created_at' => now()->subDay(),
        ]);

        // Act
        $response = $this->actingAs($user)
            ->get(route('contacts.export', [
                'keyword' => '太郎',
                'gender' => 1,
                'category_id' => $categoryA->id,
                'date' => now()->toDateString(),
            ]));

        // Assert
        $response->assertStatus(200);

        // CSVダウンロード確認
        $response->assertHeader(
            'content-type',
            'text/csv; charset=UTF-8'
        );

        // CSV内容取得
        $content = $response->streamedContent();

        // ヘッダー確認
        $this->assertStringContainsString(
            'ID,氏名,性別,メール,電話,住所,建物,カテゴリ,内容,作成日時',
            $content
        );

        // 含まれるべきデータ
        $this->assertStringContainsString('山田 太郎', $content);
        $this->assertStringContainsString('男性', $content);
        $this->assertStringContainsString('カテゴリA', $content);

        // 除外されるべきデータ
        $this->assertStringNotContainsString('次郎', $content);
        $this->assertStringNotContainsString('カテゴリB', $content);
    }

    public function test_フィルタ未指定時は新着順で_csv出力される(): void
    {
        // Arrange
        $user = User::factory()->create();

        $category = Category::factory()->create();

        $oldContact = Contact::factory()->create([
            'first_name' => '古い',
            'category_id' => $category->id,
            'created_at' => now()->subDay(),
        ]);

        $newContact = Contact::factory()->create([
            'first_name' => '新しい',
            'category_id' => $category->id,
            'created_at' => now(),
        ]);

        // Act
        $response = $this->actingAs($user)
            ->get(route('contacts.export'));

        // Assert
        $response->assertStatus(200);

        $content = $response->streamedContent();

        // 新しいデータが先に出てくること確認
        $this->assertTrue(
            strpos($content, '新しい') <
            strpos($content, '古い')
        );
    }
}
