<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_starter_content\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Ensure OE Starter Content Event are created.
 *
 * @group oe_starter_content
 */
class EventIntegrationTest extends WebDriverTestBase {

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
   * The default display settings to use for the formatters.
   *
   * @var array
   */
  protected $defaultSettings = ['timezone_override' => '', 'separator' => '-'];

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
      'update any media'
    ]);
    $this->drupalLogin($user);
  }

  /**
   * Creation of a Event content through the UI.
   */
  public function testCreateEvent() {
    // Assert session.
    $assert_session = $this->assertSession();

    // Create a sample media entity to be embedded.
    // File::create([
    //   'uri' => $this->getTestFiles('text')[0]->uri,
    // ])->save();
    // $media_document = Media::create([
    //   'bundle' => 'document',
    //   'name' => 'Starter text test',
    //   'oe_media_file_type' => 'local',
    //   'oe_media_file' => [
    //     [
    //       'target_id' => 1,
    //       'alt' => 'Starter text test alt',
    //       'title' => 'Starter text test title',
    //     ],
    //   ],
    // ]);
    // $media_document->save();

    // Create a Event item.
    $this->drupalGet('node/add/oe_event');
    $page = $this->getSession()->getPage();
    $page->fillField('title[0][value]', 'Example Event title');
    $page->fillField('body[0][value]', 'Example Event content');
    $page->fillField('oe_summary[0][value]', 'Example Event introduction');

    // $page->fillField('oe_event_dates[0][value][date]', '24-01-2022');
    // $page->fillField('oe_event_dates[0][value][time]', '20:00:00');
    // $page->fillField('oe_event_dates[0][end_value][date]', '24-01-2022');
    // $page->fillField('oe_event_dates[0][end_value][time]', '22:00:00');
    // $mediaName = $media_document->getName() . ' (' . $media_document->id() . ')';
    // $page->fillField('oe_documents[0][target_id]', $mediaName);
    
    $page->selectFieldOption('oe_location[0][address][country_code]', 'BE');
    $assert_session->waitForElementVisible('css', '.address-line1 .form-text .required');
    $page->fillField('oe_location[0][address][address_line1]', 'Rue Philippe Le Bon 1');
    $page->fillField('oe_location[0][address][postal_code]', '1000');
    $page->fillField('oe_location[0][address][locality]', 'Bruxelles');
    $page->pressButton('Save');

    // Assert Event content.
    $assert_session->pageTextContains('Event Example Event title has been created.');
    $assert_session->pageTextContains('Example Event title');
    $assert_session->pageTextContains('Example Event content');
    $assert_session->pageTextContains('Example Event introduction');
  }

}
