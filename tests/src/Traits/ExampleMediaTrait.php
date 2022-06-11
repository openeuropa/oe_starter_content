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
   * Creates a document media entity.
   *
   * @param string|null $name
   *   Base part of the name/title.
   * @param int|null $index
   *   Index for the file entity.
   *
   * @return \Drupal\media\MediaInterface
   *   The media entity.
   */
  protected function createDocumentMedia(string $name = NULL, int $index = NULL): MediaInterface {
    $name = $name ?? 'Example document' . (($index !== NULL) ? ' ' . $index : '');
    $media = Media::create([
      'bundle' => 'document',
      'oe_media_file_type' => 'local',
      'name' => "$name name",
      'oe_media_file' => [
        'target_id' => $this->createFileEntity('text', $index ?? 0)->id(),
        'title' => "$name title",
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
   * @param int $index
   *   Index to distinguish from other files of the same type.
   *   This is limited by the number of files returned from ->getTestFiles().
   *
   * @return \Drupal\file\FileInterface
   *   The file entity.
   */
  protected function createFileEntity(string $type, int $index = 0): FileInterface {
    /** @var object[] $files */
    $files = $this->getTestFiles($type);
    $this->assertArrayHasKey($index, $files);
    $file_entity = File::create([
      'uri' => $files[$index]->uri,
    ]);
    $file_entity->save();
    return $file_entity;
  }

}
