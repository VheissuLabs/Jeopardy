<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Game> $games
 * @property-read int|null $games_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\BoardFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Board newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Board newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Board query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Board whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Board whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Board whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Board whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Board whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperBoard {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $game_clue_id
 * @property int $player_id
 * @property \App\Enums\BuzzStatus $status
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\GameClue $gameClue
 * @property-read \App\Models\Player $player
 * @method static \Database\Factories\BuzzFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Buzz newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Buzz newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Buzz query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Buzz whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Buzz whereGameClueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Buzz whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Buzz wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Buzz whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Buzz whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperBuzz {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $board_id
 * @property string $name
 * @property int $position
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Board $board
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Clue> $clues
 * @property-read int|null $clues_count
 * @method static \Database\Factories\CategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereBoardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCategory {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $category_id
 * @property string $prompt
 * @property string $correct_response
 * @property int $position
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Category $category
 * @method static \Database\Factories\ClueFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clue query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clue whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clue whereCorrectResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clue wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clue wherePrompt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clue whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperClue {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $board_id
 * @property int $user_id
 * @property string $code
 * @property string $host_token
 * @property \App\Enums\GameStatus $status
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property int|null $controlling_player_id
 * @property-read \App\Models\Board $board
 * @property-read \App\Models\Player|null $controllingPlayer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GameClue> $gameClues
 * @property-read int|null $game_clues_count
 * @property-read \App\Models\User $host
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Player> $players
 * @property-read int|null $players_count
 * @method static \Database\Factories\GameFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game recentlyHostedBy(\App\Models\User $host)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereBoardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereControllingPlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereHostToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperGame {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $game_id
 * @property int $clue_id
 * @property \App\Enums\GameClueStatus $status
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property int $value
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Buzz> $buzzes
 * @property-read int|null $buzzes_count
 * @property-read \App\Models\Clue $clue
 * @property-read \App\Models\Game $game
 * @method static \Database\Factories\GameClueFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameClue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameClue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameClue query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameClue whereClueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameClue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameClue whereGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameClue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameClue whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameClue whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameClue whereValue($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperGameClue {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $game_id
 * @property string $name
 * @property int $score
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Game $game
 * @method static \Database\Factories\PlayerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereGameId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPlayer {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Carbon\CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property \Carbon\CarbonImmutable|null $two_factor_confirmed_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Board> $boards
 * @property-read int|null $boards_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passkeys\Passkey> $passkeys
 * @property-read int|null $passkeys_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

