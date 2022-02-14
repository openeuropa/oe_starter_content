<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_starter_content\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Ensure OE Starter Content Event are created.
 *
 * @group oe_starter_content
 */
class EventIntegrationTest extends BrowserTestBase {

  use MediaTypeCreationTrait;
  use TestFileCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_starter_content_event',
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
      'create oe_event content',
      'delete own oe_event content',
      'edit own oe_event content',
      'create media',
      'update any media',
    ]);
    $this->drupalLogin($user);

  }

  /**
   * Creation of an Event content through the UI.
   */
  public function testCreateEvent(): void {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Create the file.
    $file = File::create([
      'uri' => $this->getTestFiles('text')[0]->uri,
    ]);
    $file->save();

    // Create a media entity.
    $media_document = Media::create([
      'bundle' => 'document',
      'oe_media_file_type' => 'local',
      'name' => 'Test document',
      'oe_media_file' => [
        'target_id' => (int) $file->id(),
        'alt' => 'Starter text test alt',
        'title' => 'Starter text test title',
      ],
      'status' => 1,
    ]);
    $media_document->save();

    // Create an event.
    $this->drupalGet('node/add/oe_event');
    $page->fillField('Title', 'Example Event title');
    $page->fillField('Content', 'Example Event content');
    $page->fillField('Introduction', 'Example Event introduction');
    $media_name = $media_document->getName() . ' (' . $media_document->id() . ')';
    $page->fillField('oe_documents[0][target_id]', $media_name);
    $edit = [
      'Country' => 'BE',
    ];
    $this->submitForm($edit, 'Save');

    // Assert errors filling the location form.
    $assert_session->elementsCount('xpath', '//input[contains(@class, "error")]', 3);
    $assert_session->pageTextContains('Street address field is required.');
    $assert_session->pageTextContains('Postal code field is required.');
    $assert_session->pageTextContains('City field is required.');

    // Fill values.
    $page->fillField('Street address', 'Rue Philippe Le Bon 1');
    $page->fillField('Postal code', '1000');
    $page->fillField('City', 'Bruxelles');
    $page->fillField('oe_event_dates[0][value][date]', '2022-01-22');
    $page->fillField('oe_event_dates[0][value][time]', '02:12:22');
    $page->fillField('oe_event_dates[0][end_value][date]', '2022-02-24');
    $page->fillField('oe_event_dates[0][end_value][time]', '20:00:00');
    $page->pressButton('Save');

    // Assert Event content.
    $assert_session->pageTextContains('Event Example Event title has been created.');
    $assert_session->pageTextContains('Example Event title');
    $assert_session->pageTextContains('Example Event content');
    $assert_session->pageTextContains('Example Event introduction');
    $assert_session->responseContains('text-0.txt');
    $assert_session->pageTextContains('Sat, 01/22/2022 - 02:12');
    $assert_session->pageTextContains('Thu, 02/24/2022 - 20:00');
  }

}
