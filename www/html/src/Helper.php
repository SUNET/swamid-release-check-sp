<?php

namespace releasecheck;

/**
 * Helper class containig useful functions
 *
 */
class Helper
{
  protected array $replacements = array ();

  protected Configuration $config;

  public function __construct(Configuration $config)
  {
    $this->config = $config;
    $this->replacements = array (
      "FED_NAME" => ($this->config->getFederation()["displayName"] ?? _("IdP home Federation")),
      "EC_RANDS"      => "http://refeds.org/category/research-and-scholarship",         # NOSONAR Should be http://
      "EC_COCO1"      => "http://www.geant.net/uri/dataprotection-code-of-conduct/v1",  # NOSONAR Should be http://
      "EC_COCO2"      => "https://refeds.org/category/code-of-conduct/v2",
      "EC_ANON"       => "https://refeds.org/category/anonymous",
      "EC_PERS"       => "https://refeds.org/category/personalized",
      "EC_PANON"      => "https://refeds.org/category/pseudonymous",
      "EC_ESI"        => "https://myacademicid.org/entity-categories/esi",
      "RAF_ASSURANCE" => "https://refeds.org/assurance",
    );
  }

  /**
   * Get string from array of results.
   *
   * @param array $statusTextArr of results string
   *
   * @return string
   */
  public function getStatusTranslated(array|string $statusTextArr): string
  {
    $string = [];

    if (is_string($statusTextArr)) {
      $decoded  = json_decode($statusTextArr, true);

      if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        return $statusTextArr;
      }
      $statusTextArr = $decoded ;
    }

    if (!empty($statusTextArr)) {
      $placeholders = is_array($this->replacements) ? $this->replacements : [];

      foreach ($statusTextArr as $entry) {
        $trans = _($entry);
        $trans = preg_replace_callback(
          '/\[\[(.*?)\]\]/',
          function ($matches) use ($placeholders) {
            $key = $matches[1];
            return $placeholders[$key] ?? $key;
          },
          $trans
        );
        $string[] = $trans;
      }
    }

    return implode("<br>", $string);
  }

  /**
   * Get translated string for missing attributes.
   *
   * @param string $statusTextString of results string
   *
   * @return string
   */
  public function getMissingAttributesTranslated(string $statusTextString): string
  {
    $statusArr = explode("#", $statusTextString);

    foreach ($statusArr as $key => $attrText) {
      [$attribute, $description] = explode(' - ', trim($attrText), 2);
      $description = _($description);
      $statusArr[$key] = "$attribute - $description";
    }

    return implode("#", $statusArr);
  }

  public function trans(string $statusTextArr): string
  {
    $placeholders = is_array($this->replacements) ? $this->replacements : [];

    $trans = (string) _($statusTextArr);
    $trans = preg_replace_callback(
      '/\[\[(.*?)\]\]/',
      function ($matches) use ($placeholders) {
        $key = $matches[1];

        return $placeholders[$key] ?? $key;
      },
      $trans
    );

    return $trans;
  }
}
