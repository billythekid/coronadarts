<?php

namespace modules\darts\services;

use craft\base\Component;
use craft\elements\Entry;
use Illuminate\Support\Collection;

class Darts extends Component
{
    public function getPosition($player, $competition)
    {
        $leaderBoard = $this->getLeaderboard($competition);
        $playersInComp = count($leaderBoard);
        for ($i = 1; $i <= $playersInComp; $i++) {
            $leaguePlayer = $leaderBoard[$i - 1];
            if ($leaguePlayer->playerName === $player->title) {
                if ($i === 1) {
                    $beaten = $playersInComp - 1;
                    return "WINNER (beating {$beaten} others)";

                } elseif ($i === count($leaderBoard)) {
                    return "Last (of {$playersInComp} players)";
                }
                return $i . date("S", date_timestamp_get(date_create("2000-01-" . ($i)))) . " (of {$playersInComp} players)";
            }

        }

        return null;
    }

    public function mostPlayed($player)
    {
        $games = Entry::find()->section('games')->level(2)->with(['player1', 'player2'])->relatedTo($player->id)->all();

        $opponents = collect($games)->map(function ($game) use ($player) {
            return (($game->player1[0]->id === $player->id) ? ("<a class='underline' href='{$game->player2[0]->getUrl()}'>" . $game->player2[0]->title) : ("<a class='underline' href='{$game->player1[0]->getUrl()}'>" . $game->player1[0]->title)) . "</a>";
        });

        // Use Collection's countBy to replace array_count_values
        $opponentsByGames = $opponents->countBy()->sortDesc();

        return $opponentsByGames->all();
    }

    public function roundRobin($players, $rounds = 1)
    {
        $allPlayers = collect($players);

        $response = collect();
        if ($allPlayers->count() % 2 != 0) {
            // insert [BYE] into position 2. This way no rounds have BYE at the start in every round (the return legs especially)
            $allPlayers = $allPlayers->take(2)->concat(['[BYE]'])->concat($allPlayers->skip(2));
        }

        for ($thisRound = 0; $thisRound < $rounds; $thisRound++) {
            $currentPlayers = $allPlayers;
            $round = collect();
            $halfCount = intval($currentPlayers->count() / 2);
            $away = $currentPlayers->skip($halfCount)->values();
            $home = $currentPlayers->take($halfCount)->values();

            for ($i = 0; $i < $home->count() + $away->count() - 1; $i++) {
                for ($j = 0; $j < $home->count(); $j++) {
                    if ($thisRound % 2 === 0) {
                        $round->push($home->get($j) . ' vs ' . $away->get($j));
                    } else {
                        $round->push($away->get($j) . ' vs ' . $home->get($j));
                    }
                }
                if ($home->count() + $away->count() - 1 > 2) {
                    // Rotate players for next round using Collection methods
                    $rotatedPlayer = $home->slice(1, 1)->first();
                    $away = collect([$rotatedPlayer])->concat($away->slice(0, -1));
                    $home = collect([$home->first()])->concat($home->slice(2))->concat([$away->last()]);
                }
            }
            $response = $response->merge($round);
        }

        return $response->all();
    }

    public function elimination($competition)
    {
        $rounds = collect();

        $allPlayers = $competition->players->map(function ($player) {
            return $player->title;
        });

        if ($allPlayers->count() % 2 != 0) {
            $allPlayers = $allPlayers->push("[BYE]");
        }

        $gamesThatHaveBeenPlayed = collect($competition->children)->map(function ($game) {
            return $game->title;
        });

        $totalMatchesLeftToPlay = $allPlayers->count() - 1;
        $totalRounds = 0;

        while ($totalMatchesLeftToPlay > 0) {
            $totalMatchesLeftToPlay = round($totalMatchesLeftToPlay / 2, 0, PHP_ROUND_HALF_DOWN);
            $totalRounds++;
        }

        $playersStillIn = $allPlayers;
        for ($i = 0; $i < $totalRounds; $i++) {
            $games = $this->getNextRoundGames($playersStillIn);

            // if the round has an uneven number of games, the last 2 players both get a bye
            if ($games->count() % 2 !== 0 && $games->count() > 1) {
                $byeGame = $games->pop();
                $byePlayers = explode(' vs ', $byeGame);
                $games->push($byePlayers[0] . ' vs [BYE]');
                $games->push($byePlayers[1] . ' vs [BYE]');
            }

            $rounds->put($i, $games->all());
            $playersStillIn = collect();

            foreach ($games as $game) {
                if ($gamesThatHaveBeenPlayed->contains($game)) {
                    // winner goes through
                    $gameThatWasPlayed = collect($competition->children)->first(function ($child) use ($game) {
                        return $child->title == $game;
                    });

                    if ($gameThatWasPlayed) {
                        $winner = $gameThatWasPlayed->player1LegsWon > $gameThatWasPlayed->player2LegsWon ?
                            $gameThatWasPlayed->player1[0]->title :
                            $gameThatWasPlayed->player2[0]->title;
                        $playersStillIn->push($winner);
                    }
                } elseif (str_contains($game, "[BYE]")) {
                    // person who got a bye goes through
                    $player = str_replace([' vs [BYE]', '[BYE] vs '], '', $game);
                    $playersStillIn->push($player);
                } else {
                    // placeholder goes through
                    $playersStillIn->push("(Winner of {$game})");
                }
            }
        }

        return $rounds->all();
    }

    public function eliminationBlindDraw($competition)
    {
        $rounds = [];
        foreach ($competition->blindDrawRounds as $round) {
            $roundGames = [];
            foreach ($round->games as $game) {
                if ($game->player2->first() === null) {
                    $roundGames[] = $game->player1->first()->title . ' vs [BYE]';
                } else {
                    $roundGames[] = $game->player1->first()->title . ' vs ' . $game->player2->first()->title;
                }
            }
            $rounds[] = $roundGames;
        }

        return $rounds;
    }

    public function winnerStaysOn($competition)
    {
        $totalWins = [];
        $totalLosses = [];
        $winners = [];
        $losers = [];
        $games = [];

        foreach ($competition->children as $game) {
            $winner = $game->player1LegsWon > $game->player2LegsWon ? $game->player1[0] : $game->player2[0];
            $loser = $game->player1LegsWon > $game->player2LegsWon ? $game->player2[0] : $game->player1[0];
            $winners[] = $winner;
            $losers[] = $loser;
            if (array_key_exists($winner->title, $totalWins)) {
                $totalWins[$winner->title] += 1;
            } else {
                $totalWins[$winner->title] = 1;
            }
            if (array_key_exists($loser->title, $totalLosses)) {
                $totalLosses[$loser->title] += 1;
            } else {
                $totalLosses[$loser->title] = 1;
            }
            $games[] = [
                'winner' => $winner,
                'loser' => $loser,
                'game' => $game,
            ];
        }
        arsort($totalWins);
        arsort($totalLosses);

        $streaks = [];
        $streakNumber = 0;

        if (!empty($games)) {
            $previousWinner = $games[0]['winner']->title;
        }

        foreach ($games as $game) {
            if ($game['winner']->title == $previousWinner) {
                if (array_key_exists($streakNumber, $streaks)) {
                    $streaks[$streakNumber]['length'] += 1;
                } else {
                    $streaks[$streakNumber]['length'] = 1;
                    $streaks[$streakNumber]['player'] = $game['winner']->title;
                }
            } else {
                $streakNumber++;
                $streaks[$streakNumber]['length'] = 1;
                $streaks[$streakNumber]['player'] = $game['winner']->title;
            }
            $previousWinner = $game['winner']->title;
        }

        usort($streaks, function ($a, $b) {
            return $b['length'] <=> $a['length'];
        });

        return compact('winners', 'losers', 'totalWins', 'totalLosses', 'games', 'streaks');
    }

    public function getPlayerStats()
    {
        $playerStats = [];
        $players = Entry::find()->section('players')->all();
        $games = Entry::find()->section('games')->with(['player1', 'player2'])->level(2)->all();
        foreach ($players as $player) {
            $homeGames = collect($games)->filter(function ($game) use ($player) {
                return $game->player1[0]->id === $player->id;
            });
            $awayGames = collect($games)->filter(function ($game) use ($player) {
                return $game->player2[0]->id === $player->id;
            });
            $homeGamesWon = $homeGames->filter(function ($game) {
                return $game->player1LegsWon > $game->player2LegsWon;
            });
            $awayGamesWon = $awayGames->filter(function ($game) {
                return $game->player2LegsWon > $game->player1LegsWon;
            });
            $totalGamesPlayed = $homeGames->count() + $awayGames->count();
            $totalGamesWon = $homeGamesWon->count() + $awayGamesWon->count();


            $playerStats[] = [
                'playerUrl' => $player->getUrl(),
                'playerName' => $player->title,
                'totalGamesPlayed' => $totalGamesPlayed,
                'totalGamesWon' => $totalGamesWon,
                'percentage' => $totalGamesPlayed > 0 ? round($totalGamesWon / $totalGamesPlayed * 100) : 0,
            ];
        }

        return $playerStats;
    }

    public function getLeaderboard($competition = null)
    {
        if ($competition === null) {
            return [];
        }

        $gamesQuery = Entry::find()->section('games')->level(2)->descendantOf($competition);
        $players = $competition->players;

        $leaderboard = [];
        if (!empty($competition)) {
            foreach ($players as $player) {
                $homeGames = clone ($gamesQuery)->relatedTo(['targetElement' => $player, 'field' => 'player1']);
                $awayGames = clone ($gamesQuery)->relatedTo(['targetElement' => $player, 'field' => 'player2']);
                $homeGames = $homeGames->all();
                $awayGames = $awayGames->all();

                $totalGamesPlayed = count($homeGames) + count($awayGames);
                $homeLegsFor = collect($homeGames)->sum('player1LegsWon');
                $homeLegsAgainst = collect($homeGames)->sum('player2LegsWon');
                $awayLegsFor = collect($awayGames)->sum('player2LegsWon');
                $awayLegsAgainst = collect($awayGames)->sum('player1LegsWon');
                $totalLegsFor = $homeLegsFor + $awayLegsFor;
                $totalLegsAgainst = $homeLegsAgainst + $awayLegsAgainst;
                $homeGamesWon = collect($homeGames)->filter(function ($game) {
                    return $game->player1LegsWon > $game->player2LegsWon;
                })->count();
                $awayGamesWon = collect($awayGames)->filter(function ($game) {
                    return $game->player2LegsWon > $game->player1LegsWon;
                })->count();
                $totalGamesWon = $homeGamesWon + $awayGamesWon;
                $leaderboard[] = (object)[
                    'playerUrl' => $player->url,
                    'playerName' => $player->title,
                    'totalGamesWon' => $totalGamesWon,
                    'totalGamesPlayed' => $totalGamesPlayed,
                    'totalLegsFor' => $totalLegsFor,
                    'totalLegsAgainst' => $totalLegsAgainst,
                ];


                usort($leaderboard, function ($a, $b) {
                    // if the player has won more games they're higher
                    if ($a->totalGamesWon !== $b->totalGamesWon) {
                        return $a->totalGamesWon <=> $b->totalGamesWon;
                        // if they've won the same number of games, check the leg difference
                    } elseif (($a->totalLegsFor - $a->totalLegsAgainst) !== ($b->totalLegsFor - $b->totalLegsAgainst)) {
                        return ($a->totalLegsFor - $a->totalLegsAgainst) <=> ($b->totalLegsFor - $b->totalLegsAgainst);
                    }

                    // if the leg difference is the same, check the games played (games in hand)
                    return ($a->totalGamesPlayed <=> $b->totalGamesPlayed);

                });
            }
        }

        return array_reverse($leaderboard);
    }

    /**
     * @param Collection $players
     * @return Collection
     */
    private function getNextRoundGames(Collection $players): Collection
    {
        $round = collect();

        $players->chunk(2)->each(function ($pair) use ($round) {
            if ($pair->count() === 2) {
                $round->push($pair->first() . ' vs ' . $pair->last());
            } else {
                $round->push($pair->first() . ' vs [BYE]');
            }
        });

        return $round;
    }
}
