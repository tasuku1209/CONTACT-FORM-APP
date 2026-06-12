<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_タグが複数のお問い合わせに紐づいている(): void
    {
        $tag = Tag::factory()->create();

        $category = Category::factory()->create();

        $contacts = Contact::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);

        foreach ($contacts as $contact) {
            $contact->tags()->sync([$tag->id]);
        }

        $this->assertCount(3, $tag->contacts);

        $this->assertTrue($tag->contacts->contains($contacts[0]));
        $this->assertTrue($tag->contacts->contains($contacts[1]));
        $this->assertTrue($tag->contacts->contains($contacts[2]));
    }
}
