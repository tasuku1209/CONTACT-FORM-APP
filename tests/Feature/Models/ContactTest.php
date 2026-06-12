<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_お問い合わせがカテゴリに属し、タグを同期できる(): void
    {
        $category = Category::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $tags = Tag::factory()->count(3)->create();

        $contact->tags()->sync($tags->pluck('id'));

        $this->assertEquals($category->id, $contact->category->id);

        $this->assertCount(3, $contact->tags);

        $this->assertTrue($contact->tags->contains($tags[0]));
        $this->assertTrue($contact->tags->contains($tags[1]));
        $this->assertTrue($contact->tags->contains($tags[2]));
    }
}
