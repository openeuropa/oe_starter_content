<?php

/**
 * @file
 * OpenEuropa starter content event post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

/**
 * Add Registration URL field.
 */
function oe_starter_content_event_post_update_00001(&$sandbox) {
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_starter_content_event') . '/config/post_updates/00001_new_registration_url_field');
  \Drupal::service('config.installer')->installOptionalConfig($storage);

  // Form display configurations to update.
  $form_display_values = $storage->read('core.entity_form_display.node.oe_sc_event.default');
  $form_display = EntityFormDisplay::load($form_display_values['id']);
  if ($form_display) {
    $updated_form_display = \Drupal::entityTypeManager()
      ->getStorage($form_display->getEntityTypeId())
      ->updateFromStorageRecord($form_display, $form_display_values);
    $updated_form_display->save();
  }

  // View display configurations to update.
  $view_display_values = $storage->read('core.entity_view_display.node.oe_sc_event.default');
  $view_display = EntityViewDisplay::load($form_display_values['id']);
  if ($view_display) {
    $updated_view_display = \Drupal::entityTypeManager()
      ->getStorage($view_display->getEntityTypeId())
      ->updateFromStorageRecord($view_display, $view_display_values);
    $updated_view_display->save();
  }
}
