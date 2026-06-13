<?php

namespace Tests\Feature\Controller;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証ユーザーは管理画面にアクセスできる(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get(route('admin.index'));

        // Assert
        $response->assertStatus(200);
    }

    public function test_未認証ユーザーは管理画面にアクセスできない(): void
    {
        // Act
        $response = $this->get(route('admin.index'));

        // Assert
        $response->assertRedirect(route('login'));
    }

    public function test_管理画面の検索が機能し、ページネーションされて表示される(): void
    {
        // Arrange
        $user = User::factory()->create();

        $categoryA = Category::factory()->create(['content' => 'A']);
        $categoryB = Category::factory()->create(['content' => 'B']);

        Contact::factory()->count(15)->create([
            'first_name' => 'テスト1',
            'gender' => 1,
            'category_id' => $categoryA->id,
            'created_at' => now(),
        ]);

        Contact::factory()->create([
            'first_name' => 'テスト2',
            'gender' => 1,
            'category_id' => $categoryA->id,
            'created_at' => now(),
        ]);

        Contact::factory()->create([
            'first_name' => 'テスト1',
            'gender' => 2,
            'category_id' => $categoryA->id,
            'created_at' => now(),
        ]);

        Contact::factory()->create([
            'first_name' => 'テスト1',
            'gender' => 1,
            'category_id' => $categoryB->id,
            'created_at' => now(),
        ]);

        Contact::factory()->create([
            'first_name' => 'テスト1',
            'gender' => 1,
            'category_id' => $categoryA->id,
            'created_at' => now()->subDay(),
        ]);

        // Act
        $response = $this->actingAs($user)->get(route('admin.index', [
            'keyword' => 'テスト1',
            'gender' => 1,
            'category_id' => $categoryA->id,
            'date' => now()->toDateString(),
        ]));

        // Assert
        $response->assertStatus(200);

        $contacts = $response->viewData('contacts');

        foreach ($contacts as $contact) {
            $this->assertEquals('テスト1', $contact->first_name);
            $this->assertEquals(1, $contact->gender);
            $this->assertEquals($categoryA->id, $contact->category_id);
            $this->assertEquals(now()->toDateString(), $contact->created_at->toDateString());
        }

        $this->assertLessThanOrEqual(7, $contacts->count());
    }

    public function test_お問い合わせ詳細ページが表示される(): void
    {
        // Arrange
        $user = User::factory()->create();

        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        // Act
        $response = $this->actingAs($user)
            ->get(route('admin.show', $contact));

        // Assert
        $response->assertStatus(200);
        $response->assertSee($category->content);
    }

    public function test_お問い合わせを削除できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        Category::factory()->create();
        $contact = Contact::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->delete(route('admin.destroy', $contact));

        // Assert
        $response->assertRedirect(route('admin.index'));

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }
}
