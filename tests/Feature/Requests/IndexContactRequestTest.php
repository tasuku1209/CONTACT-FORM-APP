<?php

namespace Tests\Feature\Requests;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_有効な検索条件で管理画面検索できる(): void
    {
        $user = User::factory()->create();

        $category = Category::factory()->create();

        $query = http_build_query([
            'keyword' => 'test',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)
            ->get('/admin?'.$query);

        $response->assertStatus(200);

        $response->assertSessionHasNoErrors();
    }

    public function test_不正な性別値はバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin?gender=999');

        $response->assertSessionHasErrors('gender');
    }
}
