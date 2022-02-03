<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_starter_content\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Ensure OE Starter Content News are created.
 *
 * @group oe_starter_content
 */
class NewsIntegrationTest extends BrowserTestBase {

  use MediaTypeCreationTrait;
  use TestFileCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_starter_content_news',
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
   * Creation of a News content through the UI.
   */
  public function testCreateNews() {
    // Assert session.
    $assert_session = $this->assertSession();

    // Create a sample media entity to be embedded.
    File::create([
      'uri' => $this->getTestFiles('image')[0]->uri,
    ])->save();
    $media_image = Media::create([
      'bundle' => 'image',
      'name' => 'Starter Image test',
      'oe_media_image' => [
        [
          'target_id' => 1,
          'alt' => 'Starter Image test alt',
          'title' => 'Starter Image test title',
        ],
      ],
    ]);
    $media_image->save();

    // Create a News item.
    $this->drupalGet('node/add/oe_news');
    $page = $this->getSession()->getPage();
    $page->fillField('Title', 'Example title');
    $page->fillField('Content', 'Example Content');
    $page->fillField('Introduction', 'Example Introduction');
    $page->fillField('Date', '2022-01-24');
    $page->fillField('Time', '00:00:00');
    $media_name = $media_image->getName() . ' (' . $media_image->id() . ')';
    $page->fillField('Use existing media', $media_name);
    $page->pressButton('Save');

    // Assert media document has been created.
    $assert_session->pageTextContains('News Example title has been created.');
    $assert_session->pageTextContains('Example title');
    $assert_session->pageTextContains('Example Content');
    $assert_session->pageTextContains('Example Introduction');
    $assert_session->pageTextContains('01/24/2022 - 00:00');
    $assert_session->responseContains('image-test.png');
    $assert_session->responseContains('Starter Image test');
    $assert_session->responseContains('Starter Image test alt');
  }

}
