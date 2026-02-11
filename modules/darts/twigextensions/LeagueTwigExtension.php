<?php

namespace modules\darts\twigextensions;

use Craft;
use craft\elements\Entry;
use craft\helpers\ArrayHelper;
use modules\darts\Module as Darts;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class LeagueTwigExtension extends AbstractExtension
{

  private $gamesQuery;
  private $players;

  public function getFilters()
  {
    return [
      new TwigFilter('embedYoutube', [$this, 'embedYoutubeFilter'], ['is_safe' => ['html']]),
    ];
  }

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

  /**
   * Convert YouTube URLs to embedded video players
   * Supports various YouTube URL formats:
   * - https://www.youtube.com/watch?v=VIDEO_ID
   * - https://youtu.be/VIDEO_ID
   * - https://www.youtube.com/embed/VIDEO_ID
   * - https://m.youtube.com/watch?v=VIDEO_ID
   *
   * This filter is designed to work AFTER the markdown filter,
   * which converts all plain URLs to <a> tags. We only need to
   * process those <a> tags and replace them with video embeds.
   */
  public function embedYoutubeFilter($content)
  {
    if (empty($content)) {
      return $content;
    }

    // Match entire <a> tags containing YouTube URLs (created by the markdown filter)
    // This pattern matches the entire <a> tag and extracts the video ID
    $markdownLinkPattern = '/<a[^>]*href=["\']?((?:https?:\/\/)?(?:www\.)?(?:m\.)?(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})(?:[&?][^\s"\']*)?)["\']?[^>]*>.*?<\/a>/i';

    $content = preg_replace_callback($markdownLinkPattern, function($matches) {
      $videoId = $matches[2];
      return '<div class="video-wrapper my-4" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%;">'
        . '<iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" '
        . 'src="https://www.youtube.com/embed/' . $videoId . '" '
        . 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" '
        . 'allowfullscreen'
        . '></iframe>'
        . '</div>';
    }, $content);

    return $content;
  }


}
