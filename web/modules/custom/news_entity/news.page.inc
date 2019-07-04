<?php

/**
 * @file
 * Contains news.page.inc.
 *
 * Page callback for News entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for News templates.
 *
 * Default template: news.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_news(array &$variables) {
  // Fetch News Entity Object.
  $news = $variables['elements']['#news'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
