<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactScopeTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::factory()->create();
    }

    public function test_gender_scope(): void
    {
        Contact::factory()->create([
            'gender' => 1,
            'category_id' => $this->category->id,
        ]);

        Contact::factory()->create([
            'gender' => 2,
            'category_id' => $this->category->id,
        ]);

        $contacts = Contact::gender(1)->get();

        $this->assertCount(1, $contacts);

        $this->assertEquals(
            1,
            $contacts->first()->gender
        );
    }

    public function test_category_scope(): void
    {
        $category2 = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $this->category->id,
        ]);

        Contact::factory()->create([
            'category_id' => $category2->id,
        ]);

        $contacts = Contact::categoryFilter($this->category->id)
            ->get();

        $this->assertCount(1, $contacts);

        $this->assertEquals(
            $this->category->id,
            $contacts->first()->category_id
        );
    }

    public function test_keyword_scope(): void
    {
        Contact::factory()->create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'yamada@test.com',
            'category_id' => $this->category->id,
        ]);

        Contact::factory()->create([
            'first_name' => '花子',
            'last_name' => '佐藤',
            'email' => 'sato@test.com',
            'category_id' => $this->category->id,
        ]);

        $contacts = Contact::keyword('山田')
            ->get();

        $this->assertCount(1, $contacts);

        $this->assertEquals(
            '山田',
            $contacts->first()->last_name
        );
    }

    public function test_date_scope(): void
    {
        Contact::factory()->create([
            'category_id' => $this->category->id,
            'created_at' => '2024-01-01',
        ]);

        Contact::factory()->create([
            'category_id' => $this->category->id,
            'created_at' => '2025-01-01',
        ]);

        $contacts = Contact::date('2024-01-01')
            ->get();

        $this->assertCount(1, $contacts);
    }

    public function test_gender_scope_null_returns_all(): void
    {
        Contact::factory()->count(2)->create([
            'category_id' => $this->category->id,
        ]);

        $contacts = Contact::gender(null)
            ->get();

        $this->assertCount(2, $contacts);
    }
}
