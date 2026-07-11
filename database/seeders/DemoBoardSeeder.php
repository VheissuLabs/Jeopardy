<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoBoardSeeder extends Seeder
{
    public function run(): void
    {
        $host = User::first() ?? User::factory()->create([
            'name' => 'Demo Host',
            'email' => 'host@example.com',
        ]);

        $board = Board::create([
            'user_id' => $host->id,
            'name' => 'Demo Night',
        ]);

        $categories = [
            'Science' => [
                ['This planet is known as the Red Planet.', 'What is Mars?'],
                ['H2O is the chemical formula for this.', 'What is water?'],
                ['This force keeps us on the ground.', 'What is gravity?'],
            ],
            'Movies' => [
                ['This 1997 film features a sinking ship and a famous door.', 'What is Titanic?'],
                ['"May the Force be with you" comes from this franchise.', 'What is Star Wars?'],
                ['This archaeologist hates snakes.', 'Who is Indiana Jones?'],
            ],
            'Food' => [
                ['This Italian dish is a flatbread topped with cheese and tomato.', 'What is pizza?'],
                ['Sushi originates from this country.', 'What is Japan?'],
                ['This breakfast food is squeezed from oranges.', 'What is orange juice?'],
            ],
        ];

        foreach (array_keys($categories) as $position => $categoryName) {
            $category = $board->categories()->create([
                'name' => $categoryName,
                'position' => $position + 1,
            ]);

            foreach ($categories[$categoryName] as $cluePosition => [$prompt, $correctResponse]) {
                $category->clues()->create([
                    'prompt' => $prompt,
                    'correct_response' => $correctResponse,
                    'position' => $cluePosition + 1,
                ]);
            }
        }
    }
}
