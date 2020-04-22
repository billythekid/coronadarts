<?php

namespace modules\twigextensions;

use craft\elements\Entry;
use Twig\Extension\AbstractExtension;

class LeagueTwigExtension extends AbstractExtension
{

  private $gamesQuery;
  private $players;

  public function getPosition($player)
  {
    for ($i = 0; $i <= count($this->getLeaderboard()); $i++)
    {
      $leaguePlayer = $this->getLeaderBoard()[$i];
      if ($leaguePlayer->playerName === $player->title)
      {
        return $i + 1 . date("S", date_timestamp_get(date_create("2000-01-" . ($i + 1))));
      }

    }

    return null;
  }

  public function mostPlayed($player)
  {
    $gamesQuery = Entry::find()->section('games')->with(['player1', 'player2']);

    $opponents = [];
    foreach ($gamesQuery->relatedTo($player)->all() as $game)
    {
      $opponents[] = (($game->player1[0]->id === $player->id) ? ("<a class='underline' href='{$game->player2[0]->url}'>" . $game->player2[0]->title) : ("<a class='underline' href='{$game->player1[0]->url}'>" . $game->player1[0]->title)) . "</a>";
    }
    $opponentsByGames = array_count_values($opponents);
    arsort($opponentsByGames);

    return $opponentsByGames;
  }

  function roundRobin($players, $rounds = 1)
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
      if ($thisRound % 2 !== 0)
      {
        $players = array_reverse($players);
      }
      $round = [];
      $away  = array_splice($players, (count($players) / 2));
      $home  = $players;
      for ($i = 0; $i < count($home) + count($away) - 1; $i++)
      {
        for ($j = 0; $j < count($home); $j++)
        {
          $round[] = $home[$j] . ' vs ' . $away[$j];
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


  public function getLeaderboard()
  {
    $gamesQuery = Entry::find()->section('games')->with(['player1', 'player2']);
    $players    = Entry::find()->section('players')->all();

    $leaderboard = [];
    foreach ($players as $player)
    {
      $homeGames        = clone ($gamesQuery)->relatedTo([
          'element' => $player,
          'field'   => 'player1',
      ]);
      $awayGames        = clone ($gamesQuery)->relatedTo([
          'element' => $player,
          'field'   => 'player2',
      ]);
      $gamesPlayed      = clone ($gamesQuery)->relatedTo($player);
      $totalGamesPlayed = $gamesPlayed->count();
      $homeLegsFor      = $homeGames->sum('field_player1LegsWon');
      $homeLegsAgainst  = $homeGames->sum('field_player2LegsWon');
      $awayLegsFor      = $awayGames->sum('field_player2LegsWon');
      $awayLegsAgainst  = $awayGames->sum('field_player1LegsWon');
      $totalLegsFor     = $homeLegsFor + $awayLegsFor;
      $totalLegsAgainst = $homeLegsAgainst + $awayLegsAgainst;
      $homeGamesWon     = $homeGames->where('field_player1LegsWon > field_player2LegsWon')->count();
      $awayGamesWon     = $awayGames->where('field_player2LegsWon > field_player1LegsWon')->count();
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

    return array_reverse($leaderboard);
  }

}
