<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 3 user
        User::factory(3)->create();

        // 5 categories & 8 tags
        $categories = Category::factory(5)->create();
        $tags = Tag::factory(8)->create();

        // 20 posts total
        Post::factory(20)
            ->create()
            ->each(function ($post) use ($categories, $tags) {
                $post->categories()->attach($categories->random(rand(1, 2))->pluck('id')->toArray());
                $post->tags()->attach($tags->random(rand(2, 4))->pluck('id')->toArray());
            });
    }
}
