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
                [200, 'This planet is known as the Red Planet.', 'What is Mars?'],
                [400, 'H2O is the chemical formula for this.', 'What is water?'],
                [600, 'This force keeps us on the ground.', 'What is gravity?'],
            ],
            'Movies' => [
                [200, 'This 1997 film features a sinking ship and a famous door.', 'What is Titanic?'],
                [400, '"May the Force be with you" comes from this franchise.', 'What is Star Wars?'],
                [600, 'This archaeologist hates snakes.', 'Who is Indiana Jones?'],
            ],
            'Food' => [
                [200, 'This Italian dish is a flatbread topped with cheese and tomato.', 'What is pizza?'],
                [400, 'Sushi originates from this country.', 'What is Japan?'],
                [600, 'This breakfast food is squeezed from oranges.', 'What is orange juice?'],
            ],
        ];

        foreach (array_keys($categories) as $position => $categoryName) {
            $category = $board->categories()->create([
                'name' => $categoryName,
                'position' => $position + 1,
            ]);

            foreach ($categories[$categoryName] as $cluePosition => [$value, $prompt, $correctResponse]) {
                $category->clues()->create([
                    'prompt' => $prompt,
                    'correct_response' => $correctResponse,
                    'value' => $value,
                    'position' => $cluePosition + 1,
                ]);
            }
        }
    }
}
