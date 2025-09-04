<?php


namespace modules\darts\controllers;


use Craft;
use craft\elements\Entry;
use craft\web\Controller;

class DartsController extends Controller
{
  protected array|bool|int $allowAnonymous = true;

  public function actionScorers()
  {
    $cumulativeGames = ['Halfit', 'Shanghai', "Scotty's Game", "Martyn's Game"];
    $loseLivesGames  = ['25s and Bulls', '27s', '50 to 60'];
    $n01Games = ['n01 (under devlopment)'];

    $game = Craft::$app->getRequest()->getBodyParam('game');
    $players = Craft::$app->getRequest()->getBodyParam('players');
    if (empty($players))
    {
      $players = [];
    } else {
      $players = Entry::find()->section('players')->id($players)->all();
    }

    if(in_array($game, $cumulativeGames))
    {
      // /scorers/cumulative-scores
      return $this->renderTemplate("/scorers/_cumulative-scores.twig", compact('game', 'players'));
    } elseif(in_array($game, $loseLivesGames))
    {
      // /scorers/lose-lives
      return $this->renderTemplate("/scorers/_lose-lives.twig", compact('game', 'players'));
    } elseif(in_array($game, $n01Games))
    {
      // /scorers/n01
      return $this->renderTemplate("/scorers/_n01.twig", compact('game', 'players'));
    }

    // Default return if no game matches
    return $this->renderTemplate("/scorers/index.twig");
  }

}
