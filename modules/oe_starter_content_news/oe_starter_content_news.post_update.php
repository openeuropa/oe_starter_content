<?php

/**
 * @file
 * OpenEuropa starter content post updates.
 */

declare(strict_types = 1);

use Drupal\field\Entity\FieldConfig;

/**
 * Set default date for News publication date.
 */
function oe_starter_content_news_post_update_00001(&$sandbox) {
  $field = FieldConfig::load('node.oe_sc_news.oe_publication_date');
  $field->setDefaultValue([
    'default_date_type' => 'now',
    'default_date' => 'now',
  ]);
  $field->save();
}

/**
 * News content required.
 */
function oe_starter_content_news_post_update_00002(): void {
  $field_config = FieldConfig::load('node.oe_sc_news.body');
  $field_config->setRequired(TRUE)->save();
}
