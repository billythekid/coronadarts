<?php

namespace modules\darts\twigextensions;

use Craft;
use craft\elements\Entry;
use craft\helpers\ArrayHelper;
use modules\darts\Module as Darts;
use Twig\Extension\AbstractExtension;

class LeagueTwigExtension extends AbstractExtension
{

  private $gamesQuery;
  private $players;

  public function getPosition($player, $competition)
  {
    return Darts::getInstance()->darts->getPosition($player, $competition);
  }

  public function mostPlayed($player)
  {
    return Darts::getInstance()->darts->mostPlayed($player);
  }

  public function roundRobin($players, $rounds = 1)
  {
    return Darts::getInstance()->darts->roundRobin($players, $rounds);
  }

  public function elimination($competition)
  {
    return Darts::getInstance()->darts->elimination($competition);
  }

  public function eliminationBlindDraw($competition)
  {
    return Darts::getInstance()->darts->eliminationBlindDraw($competition);
  }

  public function winnerStaysOn($competition)
  {
    return Darts::getInstance()->darts->winnerstaysOn($competition);
  }

  public function getLeaderboard($competition = null)
  {
    return Darts::getInstance()->darts->getLeaderBoard($competition);
  }

  public function getPlayerStats()
  {
    return Darts::getInstance()->darts->getPlayerStats();
  }


}
