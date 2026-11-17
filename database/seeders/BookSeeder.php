<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        if (Book::count() > 0) {
            return;
        }

        Book::factory()->count(20)->create();
    }
}
