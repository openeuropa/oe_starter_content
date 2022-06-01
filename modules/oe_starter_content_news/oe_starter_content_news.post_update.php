<?php

/**
 * @file
 * OpenEuropa starter content post updates.
 */

declare(strict_types = 1);

use Drupal\field\Entity\FieldConfig;

/**
 * Enable OE Webtools Social share, Cookie consent and Page feedback components.
 */
function oe_starter_content_news_post_update_00001(&$sandbox) {
  $field = FieldConfig::load('node.oe_sc_news.oe_publication_date');
  $field->setDefaultValue([
    'default_date_type' => 'now',
    'default_date' => 'now',
  ]);
  $field->save();
}
