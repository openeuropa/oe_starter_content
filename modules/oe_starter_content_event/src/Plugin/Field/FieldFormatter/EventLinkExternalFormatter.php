<?php

namespace Drupal\oe_starter_content_event\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\link\Plugin\Field\FieldFormatter\LinkFormatter;

/**
 * Plugin implementation of the 'link_separate' formatter.
 *
 * @FieldFormatter(
 *   id = "event_link_external",
 *   label = @Translation("Event links external opens in new tab"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class EventLinkExternalFormatter extends LinkFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = parent::viewElements($items, $langcode);

    foreach ($items as $delta => $item) {
      $url = $this->buildUrl($item);

      if ($url->isExternal()) {
        $element[$delta]['#options']['attributes']['target'] = '_blank';
      }
    }

    return $element;
  }

}
