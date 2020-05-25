<?php

namespace modules\darts;

use Craft;
use craft\elements\Entry;
use craft\helpers\ElementHelper;
use craft\web\twig\variables\CraftVariable;
use modules\darts\services\Darts;
use modules\darts\twigextensions\LeagueTwigExtension;
use yii\base\Event;
use yii\base\ModelEvent;

/**
 * Custom module class.
 * This class will be available throughout the system via:
 * `Craft::$app->getModule('my-module')`.
 * You can change its module ID ("my-module") to something else from
 * config/app.php.
 * If you want the module to get loaded on every request, uncomment this line
 * in config/app.php:
 *     'bootstrap' => ['my-module']
 * Learn more about Yii module development in Yii's documentation:
 * http://www.yiiframework.com/doc-2.0/guide-structure-modules.html
 */
class Module extends \yii\base\Module
{

  /**
   * Initializes the module.
   */
  public function init()
  {
    // Set a @modules alias pointed to the modules/ directory
    Craft::setAlias('@modules', __DIR__);

    // Set the controllerNamespace based on whether this is a console or web request
    if (Craft::$app->getRequest()->getIsConsoleRequest())
    {
      $this->controllerNamespace = 'modules\\console\\controllers';
    } else
    {
      $this->controllerNamespace = 'modules\\controllers';
    }

    $this->setComponents([
        'darts' => Darts::class,
    ]);

    parent::init();

    // Custom initialization code goes here...

    Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function (Event $event) {
      /** @var CraftVariable $variable */
      $variable = $event->sender;

      // Attach a service:
      $variable->set('league', LeagueTwigExtension::class);
    });

    Event::on(Entry::class, Entry::EVENT_BEFORE_SAVE, static function (ModelEvent $event) {

      /** @var Entry $entry */
      $entry = $event->sender;

      if (!ElementHelper::isDraftOrRevision($entry))
      {
        $entryType = ($entry->getType()->handle);

        if ($entryType === 'competition')
        {
          self::getInstance()->validateCompetitionEntry($entry, $event);
        }
        if ($entryType === 'games')
        {
          self::getInstance()->validateGameEntry($entry, $event);
        }
      }

    });

  }

  /**
   * @param Entry      $entry
   * @param ModelEvent $event
   */
  private function validateGameEntry(Entry $entry, ModelEvent $event)
  {

    // games are children of competitions
    if ($entry->getParent() === null)
    {
      $entry->addError("parent", "Games need to be part of a competition.");
      $event->isValid = false;

      return;
    }

    // games have a winner
    if ($entry->player1LegsWon === $entry->player2LegsWon)
    {
      $message = "Someone needs to win.";
      $entry->addError("player1LegsWon", $message);
      $entry->addError("player2LegsWon", $message);
      $event->isValid = false;
    }

    /** @var Entry $competition */
    $competition = $entry->getParent();

    // validate Round Robin games
    if ($competition->competitionType->value == "roundRobin")
    {
      $totalRounds = $competition->roundRobinRounds;

      $allGamesToBePlayed = $this->darts->roundRobin(
          array_map(function ($player) {
            return $player->title;
          }, $competition->players->all()),
          $totalRounds);

      $allGamesToBePlayed = array_values(array_filter($allGamesToBePlayed, function ($gameTitle) {
        return strpos($gameTitle, "[BYE]") === false;
      }));

      $gamesThatHaveBeenPlayed = array_map(function (Entry $game) {
        return $game->title;
      }, $competition->getChildren()->all());

      // Check if all games have been played
      if (count($gamesThatHaveBeenPlayed) >= count($allGamesToBePlayed))
      {
        $message = "All games in {$competition->title} have been played! Are you sure you've chosen the correct league for this game?";
        $entry->addError("parent", $message);
        $event->isValid = false;

      }

      foreach ($gamesThatHaveBeenPlayed as $playedGame)
      {
        $gameKey = array_search($playedGame, $allGamesToBePlayed);
        if ($gameKey !== false)
        {
          unset($allGamesToBePlayed[$gameKey]);
        }
      }

      // Check if THIS game has still to be played
      if (!in_array($entry->title, $allGamesToBePlayed))
      {
        $message      = "{$entry->title} isn't a game that's still to be played.";
        $oppositeGame = join(' vs ', array_reverse(explode(' vs ', $entry->title)));

        if (in_array($oppositeGame, $allGamesToBePlayed))
        {
          $message .= " However {$oppositeGame} hasn't been played. Did you get your home and away players mixed up?";
        }
        $entry->addError("player1", $message);
        $entry->addError("player2", $message);
        $event->isValid = false;

      }

    }


  }


  /**
   * @param Entry      $entry
   * @param ModelEvent $event
   */
  private function validateCompetitionEntry(Entry $entry, ModelEvent $event): void
  {
    // competitions can't have parents
    if ($entry->getParent() !== null)
    {
      $entry->addError("parent", "Competitions can't be children of competitions.");
      $event->isValid = false;
    }
  }

}
