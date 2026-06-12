<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_カテゴリに紐づく複数のお問い合わせを取得できる(): void
    {
        $category = Category::factory()->create();

        $contacts = Contact::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);

        $result = $category->contacts;

        $this->assertCount(3, $result);

        $this->assertTrue($result->contains($contacts[0]));
        $this->assertTrue($result->contains($contacts[1]));
        $this->assertTrue($result->contains($contacts[2]));
    }
}
