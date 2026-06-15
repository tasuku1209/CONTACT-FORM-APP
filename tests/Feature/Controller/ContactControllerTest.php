<?php

namespace Tests\Feature\Controller;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_お問い合わせフォームが正常に表示される(): void
    {
        // Arrange
        $categories = Category::factory()->count(3)->create();
        $tags = Tag::factory()->count(2)->create();

        // Act
        $response = $this->get(route('contacts.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');

        foreach ($categories as $category) {
            $response->assertSee($category->content);
        }

        foreach ($tags as $tag) {
            $response->assertSee($tag->name);
        }
    }

    public function test_サンクスページが正常に表示される(): void
    {
        // Act
        $response = $this->get(route('contacts.thanks'));

        // Assert
        $response->assertStatus(200);
    }

    public function test_お問い合わせ確認ページが表示される(): void
    {
        // Arrange
        $category = Category::factory()->create([
            'content' => '商品のお届けについて',
        ]);

        $tag = Tag::factory()->create([
            'name' => 'テストタグ',
        ]);

        // Act
        $response = $this->post(route('contacts.confirm'), [
            'first_name' => 'test名',
            'last_name' => 'test姓',
            'gender' => 1,
            'email' => 'test@test.com',
            'tel' => '11111111111',
            'address' => 'test住所',
            'building' => 'test建物名',
            'category_id' => $category->id,
            'detail' => 'testお問い合わせ内容',
            'tag_ids' => [$tag->id],
        ]);

        // Assert
        $response->assertStatus(200);

        $response->assertSee('test名');
        $response->assertSee('test姓');
        $response->assertSee('男性');
        $response->assertSee('test@test.com');
        $response->assertSee('11111111111');
        $response->assertSee('test住所');
        $response->assertSee('test建物名');
        $response->assertSee('商品のお届けについて');
        $response->assertSee('testお問い合わせ内容');
        $response->assertSee('テストタグ');
    }

    public function test_確認画面バリデーションエラーでリダイレクトされる(): void
    {
        // Act
        $response = $this->post(route('contacts.confirm'), [
            'first_name' => '',
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'first_name',
        ]);
    }

    public function test_お問い合わせが保存されてサンクスへリダイレクトされる(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        // Act
        $response = $this->post(route('contacts.store'), [
            'first_name' => 'test名',
            'last_name' => 'test姓',
            'gender' => 1,
            'email' => 'test@test.com',
            'tel' => '11111111111',
            'address' => 'test住所',
            'category_id' => $category->id,
            'detail' => 'testお問い合わせ内容',
            'tag_ids' => $tags->pluck('id')->toArray(),
        ]);

        // Assert
        $response->assertRedirect(route('contacts.thanks'));

        $this->assertDatabaseHas('contacts', [
            'first_name' => 'test名',
            'last_name' => 'test姓',
            'email' => 'test@test.com',
        ]);

        $this->assertDatabaseCount('contact_tag', 2);
    }

    public function test_お問い合わせ送信バリデーションエラー(): void
    {
        // Act
        $response = $this->post(route('contacts.store'), [
            'first_name' => '',
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'first_name',
        ]);
    }
}
