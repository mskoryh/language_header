<?php

namespace Drupal\language_header\Plugin\LanguageNegotiation;

use Drupal\Component\Utility\UserAgent;
use Drupal\language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language from an arbitrary HTTP header.
 *
 * @LanguageNegotiation(
 *   id = \Drupal\language_header\Plugin\LanguageNegotiation\HttpHeader::METHOD_ID,
 *   weight = -2,
 *   name = @Translation("HTTP Header"),
 *   description = @Translation("Language from the browser's headers."),
 *   config_route_name = "language_header.settings"
 * )
 */
class HttpHeader extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-header';

  /**
   * {@inheritdoc}
   */
  public function getLangcode(Request $request = NULL) {
    $langcode = NULL;

    if ($this->languageManager && $request) {
      $mappings = $this->config->get('language_header.mappings')->get('map');
      if (is_array($mappings)) {
        foreach ($mappings as $header => $drupal_langcode) {
          list($header_name, $header_value) = explode(':', $header);
          if (!$request->headers->has($header_name)) {
            continue;
          }

          if ($request->headers->get($header_name) == $header_value) {
            $langcode = $drupal_langcode;
            \Drupal::service('page_cache_kill_switch')->trigger();
            break;
          }
        }
      }
    }

    return $langcode;
  }

}
