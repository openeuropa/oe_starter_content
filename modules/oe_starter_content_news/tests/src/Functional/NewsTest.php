<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_starter_content_news\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Ensure OE Starter Content News are created.
 *
 * @group oe_starter_content_news
 */
class NewsTest extends BrowserTestBase {

  use MediaTypeCreationTrait;
  use TestFileCreationTrait {
    getTestFiles as drupalGetTestFiles;
  }

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'datetime',
    'media',
    'system',
    'user',
    'node',
    'oe_starter_content_news',
    'config',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create user.
    $user = $this->drupalCreateUser([
      'access content overview',
      'create oe_news content',
      'delete own oe_news content',
      'edit own oe_news content',
    ]);
    $this->drupalLogin($user);
  }

  /**
   * Creation of a News content through the UI..
   */
  public function testCreateNews() {
    // Assert session.
    $assert_session = $this->assertSession();

    // Create a sample media entity to be embedded.
    $this->createMediaType('image', ['id' => 'image']);
    File::create([
      'uri' => $this->getTestFiles('image')[0]->uri,
    ])->save();
    $mediaImage = Media::create([
      'bundle' => 'image',
      'name' => 'Starter Image test',
      'field_media_image' => [
        [
          'target_id' => 1,
          'alt' => 'default alt',
          'title' => 'default title',
        ],
      ],
    ]);
    $mediaImage->save();

    // Create a News item.
    $this->drupalGet('node/add/oe_news');
    $page = $this->getSession()->getPage();
    $page->fillField('title[0][value]', 'Example title');
    $page->fillField('body[0][value]', 'Example Content');
    $page->fillField('field_oe_summary[0][value]', 'Example Introduction');
    $page->fillField('field_oe_publication_date[0][value][date]', '2022-01-24');
    $page->fillField('field_oe_publication_date[0][value][time]', '00:00:00');
    $mediaName = $mediaImage->getName() . ' (' . $mediaImage->id() . ')';
    $page->fillField('field_oe_featured_media[0][target_id]', $mediaName);
    $page->pressButton('Save');

    // Assert News content.
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getSession()->getPage();
    $assert_session->pageTextContains('Example title');
    $assert_session->pageTextContains('Example Content');
    $assert_session->pageTextContains('Example Introduction');
    $assert_session->pageTextContains('01/24/2022 - 00:00');
    $assert_session->pageTextContains('Starter Image test');
  }

}
