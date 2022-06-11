<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_starter_content\Traits;

use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\media\Entity\Media;
use Drupal\media\MediaInterface;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Helper methods to create example media entities.
 */
trait ExampleMediaTrait {

  use TestFileCreationTrait;

  /**
   * Creates an image media entity.
   *
   * @param string|null $name
   *   Base part of the name/title.
   *
   * @return \Drupal\media\MediaInterface
   *   The media entity.
   */
  protected function createImageMedia(string $name = 'Example image'): MediaInterface {
    $media = Media::create([
      'bundle' => 'image',
      'name' => "$name name",
      'oe_media_image' => [
        [
          'target_id' => $this->createFileEntity('image')->id(),
          'alt' => "$name alt",
          'title' => "$name title",
        ],
      ],
    ]);
    $media->save();
    return $media;
  }

  /**
   * Creates a file entity.
   *
   * @param string $type
   *   File type, e.g. 'text' or 'image'.
   *
   * @return \Drupal\file\FileInterface
   *   The file entity.
   */
  protected function createFileEntity(string $type): FileInterface {
    $file_entity = File::create([
      'uri' => $this->getTestFiles($type)[0]->uri,
    ]);
    $file_entity->save();
    return $file_entity;
  }

}
