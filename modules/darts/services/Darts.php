<?php


namespace modules\darts\services;


use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\helpers\ArrayHelper;

class Darts extends Component
{
  public function getPosition($player, $competition)
  {
    $leaderBoard = $this->getLeaderboard($competition);
    for ($i = 1; $i < count($leaderBoard); $i++)
    {
      $leaguePlayer = $leaderBoard[$i-1];
      if ($leaguePlayer->playerName === $player->title)
      {
        return $i . date("S", date_timestamp_get(date_create("2000-01-" . ($i))));
      }

    }

    return null;
  }

  public function mostPlayed($player)
  {
    $games = Entry::find()->section('games')->level(2)->with(['player1', 'player2'])->relatedTo($player->id)->all();

    $opponents = array_map(function ($game) use ($player) {
      return (($game->player1[0]->id === $player->id) ? ("<a class='underline' href='{$game->player2[0]->getUrl()}'>" . $game->player2[0]->title) : ("<a class='underline' href='{$game->player1[0]->getUrl()}'>" . $game->player1[0]->title)) . "</a>";
    }, $games);

    $opponentsByGames = array_count_values($opponents);
    arsort($opponentsByGames);

    return $opponentsByGames;
  }

  public function roundRobin($players, $rounds = 1)
  {
    $allPlayers = $players;

    $response = [];
    if (count($allPlayers) % 2 != 0)
    {
      // insert [BYE] into position 2 in the array. This way no rounds have BYE at the start in every round (the return legs especially)
      array_splice($allPlayers, 2, 0, "[BYE]");
    }


    for ($thisRound = 0; $thisRound < $rounds; $thisRound++)
    {
      $players = $allPlayers;
      $round   = [];
      $away    = array_splice($players, (count($players) / 2));
      $home    = $players;


      for ($i = 0; $i < count($home) + count($away) - 1; $i++)
      {
        for ($j = 0; $j < count($home); $j++)
        {
          if ($thisRound % 2 === 0)
          {
            $round[] = $home[$j] . ' vs ' . $away[$j];
          } else
          {
            $round[] = $away[$j] . ' vs ' . $home[$j];
          }
        }
        if (count($home) + count($away) - 1 > 2)
        {
          $s     = array_splice($home, 1, 1);
          $slice = array_shift($s);
          array_unshift($away, $slice);
          array_push($home, array_pop($away));
        }
      }
      $response[] = $round;
    }

    return array_merge(...$response);
  }

  public function elimination($competition)
  {
    $rounds = [];

    $allPlayers = array_map(function ($player) {
      return $player->title;
    }, $competition->players);

    if (count($allPlayers) % 2 != 0)
    {
      $allPlayers[] = "[BYE]";
    }

    $gamesThatHaveBeenPlayed = array_map(function ($game) {
      return $game->title;
    }, $competition->children);

    $totalMatchesLeftToPlay = count($allPlayers) - 1;
    $totalRounds            = 0;

    while ($totalMatchesLeftToPlay > 0)
    {
      $totalMatchesLeftToPlay = round($totalMatchesLeftToPlay / 2, 0, PHP_ROUND_HALF_DOWN);
      $totalRounds++;
    }

    $playersStillIn = $allPlayers;
    for ($i = 0; $i < $totalRounds; $i++)
    {
      $games = $this->getNextRoundGames($playersStillIn);

      // if the round has an uneven number of games, the last 2 players both get a bye
      if (count($games) % 2 !== 0 && count($games) > 1)
      {
        $byeGame    = array_pop($games);
        $byePlayers = explode(' vs ', $byeGame);
        $games[]    = $byePlayers[0] . ' vs [BYE]';
        $games[]    = $byePlayers[1] . ' vs [BYE]';
      }
      $rounds[$i]     = $games;
      $playersStillIn = [];
      foreach ($games as $game)
      {
        if (in_array($game, $gamesThatHaveBeenPlayed))
        {
          // winner goes through
          $gameThatWasPlayed = ArrayHelper::firstValue(array_filter($competition->children, function($child) use ($game) {return $child->title == $game;}));
          $playersStillIn[]  = $gameThatWasPlayed->player1LegsWon > $gameThatWasPlayed->player2LegsWon ? $gameThatWasPlayed->player1[0]->title : $gameThatWasPlayed->player2[0]->title;
        } elseif (strpos($game, "[BYE]") !== false)
        {
          // person who got a bye goes through
          $playersStillIn[] = str_replace([' vs [BYE]', '[BYE] vs '], '', $game);
        } else
        {
          // placeholder goes through
          $playersStillIn[] = "(Winner of {$game})";
        }
      }
    }

    return $rounds;
  }

  public function eliminationBlindDraw($competition)
  {
    $rounds = [];
    foreach ($competition->blindDrawRounds as $round)
    {
      $roundGames = [];
      foreach ($round->games as $game)
      {
        if (empty($game->player2))
        {
          $roundGames[] = $game->player1[0]->title . ' vs [BYE]';
        } else
        {
          $roundGames[] = $game->player1[0]->title . ' vs ' . $game->player2[0]->title;
        }
      }
      $rounds[] = $roundGames;
    }

    return $rounds;
  }

  public function winnerStaysOn($competition)
  {
    $totalWins   = [];
    $totalLosses = [];
    $winners     = [];
    $losers      = [];
    $games       = [];

    foreach ($competition->children as $game)
    {
      $winner    = $game->player1LegsWon > $game->player2LegsWon ? $game->player1[0] : $game->player2[0];
      $loser     = $game->player1LegsWon > $game->player2LegsWon ? $game->player2[0] : $game->player1[0];
      $winners[] = $winner;
      $losers[]  = $loser;
      if (array_key_exists($winner->title, $totalWins))
      {
        $totalWins[$winner->title] += 1;
      } else
      {
        $totalWins[$winner->title] = 1;
      }
      if (array_key_exists($loser->title, $totalLosses))
      {
        $totalLosses[$loser->title] += 1;
      } else
      {
        $totalLosses[$loser->title] = 1;
      }
      $games[] = [
          'winner' => $winner,
          'loser'  => $loser,
          'game'   => $game,
      ];
    }
    arsort($totalWins);
    arsort($totalLosses);

    $streaks      = [];
    $streakNumber = 0;

    if(!empty($games))
    {
      $previousWinner = $games[0]['winner']->title;
    }

    foreach ($games as $game)
    {
      if ($game['winner']->title == $previousWinner)
      {
        if (array_key_exists($streakNumber, $streaks))
        {
          $streaks[$streakNumber]['length'] += 1;
        } else
        {
          $streaks[$streakNumber]['length'] = 1;
          $streaks[$streakNumber]['player'] = $game['winner']->title;
        }
      } else
      {
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
    $players     = Entry::find()->section('players')->all();
    $games       = Entry::find()->section('games')->with(['player1', 'player2'])->level(2)->all();
    foreach ($players as $player)
    {
      $homeGames        = array_filter($games, function ($game) use ($player) {
        return $game->player1[0]->id === $player->id;
      });
      $awayGames        = array_filter($games, function ($game) use ($player) {
        return $game->player2[0]->id === $player->id;
      });
      $homeGamesWon     = array_filter($homeGames, function ($game) {
        return $game->player1LegsWon > $game->player2LegsWon;
      });
      $awayGamesWon     = array_filter($awayGames, function ($game) {
        return $game->player2LegsWon > $game->player1LegsWon;
      });
      $totalGamesPlayed = count($homeGames) + count($awayGames);
      $totalGamesWon    = count($homeGamesWon) + count($awayGamesWon);

      $playerStats[] = [
          'playerUrl'        => $player->getUrl(),
          'playerName'       => $player->title,
          'totalGamesPlayed' => $totalGamesPlayed,
          'totalGamesWon'    => $totalGamesWon,
          'percentage'       => round($totalGamesWon / $totalGamesPlayed * 100),
      ];
    }

    return $playerStats;
  }

  public function getLeaderboard($competition = null)
  {
    if ($competition === null)
    {
      return [];
    }

    $gamesQuery = Entry::find()->section('games')->level(2)->descendantOf($competition);
    $players    = $competition->players;

    $leaderboard = [];
    if (!empty($competition))
    {
      foreach ($players as $player)
      {
        $homeGames = clone ($gamesQuery)->relatedTo(['targetElement' => $player, 'field' => 'player1']);
        $awayGames = clone ($gamesQuery)->relatedTo(['targetElement' => $player, 'field' => 'player2']);
        $homeGames = $homeGames->all();
        $awayGames = $awayGames->all();

        $totalGamesPlayed = count($homeGames) + count($awayGames);
        $homeLegsFor      = array_sum(array_column($homeGames, 'player1LegsWon'));
        $homeLegsAgainst  = array_sum(array_column($homeGames, 'player2LegsWon'));
        $awayLegsFor      = array_sum(array_column($awayGames, 'player2LegsWon'));
        $awayLegsAgainst  = array_sum(array_column($awayGames, 'player1LegsWon'));
        $totalLegsFor     = $homeLegsFor + $awayLegsFor;
        $totalLegsAgainst = $homeLegsAgainst + $awayLegsAgainst;
        $homeGamesWon     = count(array_filter($homeGames, function ($game) {
          return $game->player1LegsWon > $game->player2LegsWon;
        }));
        $awayGamesWon     = count(array_filter($awayGames, function ($game) {
          return $game->player2LegsWon > $game->player1LegsWon;
        }));
        $totalGamesWon    = $homeGamesWon + $awayGamesWon;
        $leaderboard[]    = (object)[
            'playerUrl'        => $player->url,
            'playerName'       => $player->title,
            'totalGamesWon'    => $totalGamesWon,
            'totalGamesPlayed' => $totalGamesPlayed,
            'totalLegsFor'     => $totalLegsFor,
            'totalLegsAgainst' => $totalLegsAgainst,
        ];


        usort($leaderboard, function ($a, $b) {
          // if the player has won more games they're higher
          if ($a->totalGamesWon !== $b->totalGamesWon)
          {
            return $a->totalGamesWon <=> $b->totalGamesWon;
            // if they've won the same number of games, check the leg difference
          } elseif (($a->totalLegsFor - $a->totalLegsAgainst) !== ($b->totalLegsFor - $b->totalLegsAgainst))
          {
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
   * @param array $players
   * @param array $gamesThatHaveBeenPlayed
   * @return array
   */
  private function getNextRoundGames(array $players): array
  {
    $round = [];
    for ($i = 0; $i < count($players); $i += 2)
    {
      if (@$players[$i + 1])
      {
        $gameTitle = $players[$i] . ' vs ' . $players[$i + 1];
      } else
      {
        $gameTitle = $players[$i] . ' vs [BYE]';
      }
      $round[] = $gameTitle;
    }

    return $round;
  }
}
