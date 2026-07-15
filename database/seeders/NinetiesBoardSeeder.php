<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\User;
use Illuminate\Database\Seeder;

class NinetiesBoardSeeder extends Seeder
{
    public function run(): void
    {
        $host = User::first() ?? User::factory()->create([
            'name' => 'Demo Host',
            'email' => 'host@example.com',
        ]);

        $board = Board::create([
            'user_id' => $host->id,
            'name' => 'Totally 90s',
        ]);

        $categories = [
            '90s Music' => [
                ['"Smells Like Teen Spirit" launched this Seattle grunge band to superstardom.', 'Who is Nirvana?'],
                ['The Spice Girls\' debut single tells you what they really, really want.', 'What is "Wannabe"?'],
                ['Whitney Houston\'s "I Will Always Love You" was written by this country legend.', 'Who is Dolly Parton?'],
                ['The 1996 megahit "Macarena" came from this Spanish duo.', 'Who is Los del Río?'],
                ['This 1997 Radiohead album features "Paranoid Android" and "Karma Police".', 'What is OK Computer?'],
            ],
            '90s Movies' => [
                ['According to this 1994 Tom Hanks character, life is like a box of chocolates.', 'Who is Forrest Gump?'],
                ['This 1993 Spielberg blockbuster proved that clever girls open kitchen doors.', 'What is Jurassic Park?'],
                ['Keanu Reeves takes the red pill in this 1999 sci-fi film.', 'What is The Matrix?'],
                ['"To infinity and beyond!" debuted in this 1995 film, the first fully computer-animated feature.', 'What is Toy Story?'],
                ['Verbal Kint spins the legend of Keyser Söze in this 1995 thriller.', 'What is The Usual Suspects?'],
            ],
            '90s TV' => [
                ['This NBC sitcom about six New Yorkers and a coffee shop debuted in 1994.', 'What is Friends?'],
                ['Will Smith moved in with his auntie and uncle in this town-titled sitcom.', 'What is The Fresh Prince of Bel-Air?'],
                ['"The truth is out there" for agents Mulder and Scully on this show.', 'What is The X-Files?'],
                ['This self-described "show about nothing" ended its nine-season run in 1998.', 'What is Seinfeld?'],
                ['Agent Dale Cooper investigates Laura Palmer\'s murder in this David Lynch series.', 'What is Twin Peaks?'],
            ],
            '90s Toys & Fads' => [
                ['This egg-shaped virtual pet from Japan died if you ignored it.', 'What is a Tamagotchi?'],
                ['These bean-stuffed collectibles were "retired" to drive up demand — tag protectors sold separately.', 'What are Beanie Babies?'],
                ['Kids slammed and traded these circular milk-cap game pieces.', 'What are POGs?'],
                ['This Nintendo handheld launched with Tetris and ruled the decade.', 'What is the Game Boy?'],
                ['This furry gibberish-speaking robot of 1998 supposedly "learned" English over time.', 'What is a Furby?'],
            ],
            '90s Tech' => [
                ['"You\'ve got mail!" greeted users of this dial-up giant.', 'What is AOL?'],
                ['Microsoft launched this OS in 1995 to the Rolling Stones\' "Start Me Up".', 'What is Windows 95?'],
                ['Two Stanford grad students founded this search engine in 1998.', 'What is Google?'],
                ['Sony\'s first game console, launched in the mid-90s, made the CD-ROM king.', 'What is the PlayStation?'],
                ['Shawn Fanning\'s 1999 MP3-sharing service made Metallica very, very angry.', 'What is Napster?'],
            ],
            '90s Sports' => [
                ['This Chicago Bulls legend collected six NBA titles in the decade.', 'Who is Michael Jordan?'],
                ['This 20-year-old won the 1997 Masters by a record 12 strokes.', 'Who is Tiger Woods?'],
                ['He and Sammy Sosa chased Roger Maris\'s home-run record in the summer of 1998.', 'Who is Mark McGwire?'],
                ['Mike Tyson was disqualified for biting this heavyweight\'s ear in 1997.', 'Who is Evander Holyfield?'],
                ['Her penalty kick — and celebration — won the US the 1999 Women\'s World Cup.', 'Who is Brandi Chastain?'],
            ],
            '90s Snacks & Drinks' => [
                ['Pepsi\'s see-through cola experiment of 1992.', 'What is Crystal Pepsi?'],
                ['Kangaroo-branded cookies made for dunking in frosting.', 'What are Dunkaroos?'],
                ['This General Mills snack unrolls three feet of fruity fun.', 'What is Fruit by the Foot?'],
                ['Coca-Cola launched this hyper-caffeinated citrus soda in 1997 to battle Mountain Dew.', 'What is Surge?'],
                ['Fat-free Pringles used this controversial fake fat with a famous warning label.', 'What is Olestra?'],
            ],
            '90s Slang' => [
                ['Wayne\'s World popularized ending a sentence with this one-word reversal.', 'What is "NOT!"?'],
                ['"As if!" and "Whatever!" were popularized by this 1995 Alicia Silverstone film.', 'What is Clueless?'],
                ['Something truly cool was "all that and a bag of" these.', 'What are chips?'],
                ['Telling someone to relax, you might prescribe this rhyming remedy.', 'What is a chill pill?'],
                ['Announcing "I\'m Audi 5000" meant you were doing this.', 'What is leaving?'],
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
