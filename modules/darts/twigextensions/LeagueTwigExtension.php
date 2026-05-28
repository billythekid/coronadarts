<?php

namespace modules\darts\twigextensions;

use Craft;
use craft\elements\Entry;
use craft\helpers\ArrayHelper;
use modules\darts\Module as Darts;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class LeagueTwigExtension extends AbstractExtension
{

  private $gamesQuery;
  private $players;
  private static $viteManifest = null;

  public function getFilters()
  {
    return [
      new TwigFilter('embedYoutube', [$this, 'embedYoutubeFilter'], ['is_safe' => ['html']]),
    ];
  }

  public function getFunctions()
  {
    return [
      new TwigFunction('vite', [$this, 'viteAssetTags'], ['is_safe' => ['html']]),
    ];
  }

  /**
   * Read the Vite manifest and return the `<link>` and `<script>` tags for
   * the given entry point (e.g. "assets/js/app.js").
   *
   * Output path matches `base: '/dist/'` in vite.config.js.
   */
  public function viteAssetTags($entry)
  {
    $manifestPath = Craft::getAlias('@webroot') . '/dist/.vite/manifest.json';

    if (self::$viteManifest === null) {
      if (!is_file($manifestPath)) {
        return "<!-- vite manifest not found at {$manifestPath} -->";
      }
      self::$viteManifest = json_decode(file_get_contents($manifestPath), true) ?: [];
    }

    if (!isset(self::$viteManifest[$entry])) {
      return "<!-- vite entry '{$entry}' not found in manifest -->";
    }

    $info = self::$viteManifest[$entry];
    $tags = [];

    if (!empty($info['css'])) {
      foreach ($info['css'] as $cssFile) {
        $tags[] = '<link rel="stylesheet" href="/dist/' . htmlspecialchars($cssFile, ENT_QUOTES) . '">';
      }
    }

    if (!empty($info['file'])) {
      $tags[] = '<script type="module" src="/dist/' . htmlspecialchars($info['file'], ENT_QUOTES) . '"></script>';
    }

    return implode("\n", $tags);
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
