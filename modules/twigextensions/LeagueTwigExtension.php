<?php

namespace modules\twigextensions;

use craft\elements\db\ElementQuery;
use craft\elements\Entry;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LeagueTwigExtension extends AbstractExtension
{
  /** @var ElementQuery */
  public $gamesQuery;
  public $gamesPlayed;
  public $homeGames;
  public $awayGames;
  public $homeLegsFor;
  public $awayLegsFor;
  public $homeLegsAgainst;
  public $awayLegsAgainst;
  public $homeGamesWon;
  public $awayGamesWon;
  public $players;
  public $leaderboard;
  public $totalLegsFor;
  public $totalGamesPlayed;
  public $totalLegsAgainst;
  /**
   * @var int|string
   */
  public $totalGamesWon;

  public function __construct()
  {
    $this->gamesQuery  = Entry::find()->section('games')->with(['player1', 'player2']);
    $this->players     = Entry::find()->section('players')->all();
    $this->players     = Entry::find()->section('players')->all();
    $this->leaderboard = [];
    foreach ($this->players as $player)
    {
      $gamesQuery             = $this->gamesQuery;
      $this->homeGames        = clone ($gamesQuery)->relatedTo([
          'element' => $player,
          'field'   => 'player1',
      ]);
      $this->awayGames        = clone ($gamesQuery)->relatedTo([
          'element' => $player,
          'field'   => 'player2',
      ]);
      $this->gamesPlayed      = clone ($gamesQuery)->relatedTo($player);
      $this->totalGamesPlayed = $this->gamesPlayed->count();
      $this->homeLegsFor      = $this->homeGames->sum('field_player1LegsWon');
      $this->homeLegsAgainst  = $this->homeGames->sum('field_player2LegsWon');
      $this->awayLegsFor      = $this->awayGames->sum('field_player2LegsWon');
      $this->awayLegsAgainst  = $this->awayGames->sum('field_player1LegsWon');
      $this->totalLegsFor     = $this->homeLegsFor + $this->awayLegsFor;
      $this->totalLegsAgainst = $this->homeLegsAgainst + $this->awayLegsAgainst;
      $this->homeGamesWon     = $this->homeGames->where('field_player1LegsWon > field_player2LegsWon')->count();
      $this->awayGamesWon     = $this->awayGames->where('field_player2LegsWon > field_player1LegsWon')->count();
      $this->totalGamesWon    = $this->homeGamesWon + $this->awayGamesWon;
      $this->leaderboard[]    = (object)[
          'playerUrl'        => $player->url,
          'playerName'       => $player->title,
          'totalGamesWon'    => $this->totalGamesWon,
          'totalGamesPlayed' => $this->totalGamesPlayed,
          'totalLegsFor'     => $this->totalLegsFor,
          'totalLegsAgainst' => $this->totalLegsAgainst,
      ];

      usort($this->leaderboard, function ($a, $b) {
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
      $this->leaderboard = array_reverse($this->leaderboard);
    }
  }

  public function getFunctions()
  {
    return new TwigFunction('position', [$this, 'getPosition']);
  }

  public function getPosition($player)
  {
    for ($i = 0; $i <= count($this->leaderboard); $i++)
    {
      $leaguePlayer = $this->leaderboard[$i];
      if ($leaguePlayer->playerName === $player->title)
      {
        return $i + 1 . date("S", date_timestamp_get(date_create("2000-01-" . ($i + 1))));
      }

    }

    return null;
  }

  public function mostPlayed($player)
  {
    $opponents = [];
    foreach ($this->gamesQuery->relatedTo($player)->all() as $game)
    {
      $opponents[] = (($game->player1[0]->id === $player->id) ? ("<a class='underline' href='{$game->player2[0]->url}'>" . $game->player2[0]->title) : ("<a class='underline' href='{$game->player1[0]->url}'>" . $game->player1[0]->title)) . "</a>";
    }
    $opponentsByGames = array_count_values($opponents);
    arsort($opponentsByGames);

    return $opponentsByGames;


  }


}
