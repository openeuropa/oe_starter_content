<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_starter_content\Functional;

use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Tests the Publication content type.
 *
 * @group oe_starter_content
 */
class PublicationTest extends BrowserTestBase {

  use MediaTypeCreationTrait;
  use TestFileCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_starter_content_publication',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the creation of Publication content through the UI.
   */
  public function testCreatePublication(): void {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Create an image entity to be embedded.
    $image_file = File::create([
      'uri' => $this->getTestFiles('image')[0]->uri,
    ]);
    $image_file->save();
    $image_media = Media::create([
      'bundle' => 'image',
      'name' => 'Example image',
      'oe_media_image' => [
        [
          'target_id' => $image_file->id(),
          'alt' => 'Image alt',
          'title' => 'Image title',
        ],
      ],
    ]);
    $image_media->save();

    // Create a document entity to be embedded.
    $document_file = File::create([
      'uri' => $this->getTestFiles('text')[0]->uri,
    ]);
    $document_file->save();
    $document_media = Media::create([
      'bundle' => 'document',
      'oe_media_file_type' => 'local',
      'oe_media_file' => [
        'target_id' => (int) $document_file->id(),
      ],
      'name' => 'Example document',
    ]);
    $document_media->save();

    // Login with permission to create publication content.
    $user = $this->drupalCreateUser([
      'create oe_sc_publication content',
      'view media',
    ]);
    $this->drupalLogin($user);

    // Attempt to create a publication with no values, to test required fields.
    $this->drupalGet('node/add/oe_sc_publication');
    $page->pressButton('Save');
    // The node is not saved.
    $assert_session->elementTextEquals('css', 'h1', 'Create Publication');
    $assert_session->elementsCount('css', 'input.error', 1);
    $assert_session->pageTextContains('Title field is required.');

    // Create a publication with minimal required values.
    $this->drupalGet('node/add/oe_sc_publication');
    $page->fillField('Title', 'Lorem Ipsum Dolor Sit Amet');
    $page->pressButton('Save');

    // Assert only the required fields.
    $assert_session->elementTextEquals('css', 'h1', 'Lorem Ipsum Dolor Sit Amet');
    $assert_session->elementExists('css', 'time');

    // Create a publication with all values filled in.
    $this->drupalGet('node/add/oe_sc_publication');

    ini_set('xdebug.var_display_max_depth', '10');
    ini_set('xdebug.var_display_max_children', '256');
    ini_set('xdebug.var_display_max_data', '100024');
    var_dump($page->getHtml());

    $page->fillField('Title', 'Fusce commodo aliquam arcu');
    $page->fillField(
      'edit-oe-featured-media-0-featured-media-target-id',
      $image_media->label() . ' (' . $image_media->id() . ')',
    );
    $page->fillField('Caption', 'Example Image caption');
    $page->fillField('Reference code', '123456');
    $page->fillField('Short description', 'Fusce a quam. Fusce vel dui. Suspendisse nisl elit, rhoncus eget.');
    $page->fillField(
      'Long description',
      // Line breaks should turn into paragraphs.
      "Aliquam lobortis. Vestibulum facilisis, purus nec pulvinar iaculis vitae euismod ligula urna in dolor.\n\nDonec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. Donec vitae sapien ut libero venenatis faucibus.",
    );
    $page->fillField('Date', '2022-06-22');
    $page->fillField(
      'edit-oe-documents-0-target-id',
      $document_media->label() . ' (' . $document_media->id() . ')',
    );
    $page->pressButton('Save');

    // Assert contents of the Publication detail page.
    $assert_session->pageTextContains('Publication Fusce commodo aliquam arcu has been created.');
    $assert_session->elementTextEquals('css', 'h1', 'Fusce commodo aliquam arcu');

    $url = $this->getUrl();
    $nid = basename($url);

    // Visit the same page as anonymous user.
    $this->drupalLogout();
    $this->drupalGet('/node/' . $nid);

    // All fields should be visible to anonymous.
    $assert_session->elementTextEquals('css', 'h1', 'Fusce commodo aliquam arcu');
    $assert_session->responseContains('Example image');
    $assert_session->responseContains('Example Image caption');
    $assert_session->pageTextContains('123456');
    $assert_session->pageTextContains('Fusce a quam. Fusce vel dui. Suspendisse nisl elit, rhoncus eget.');
    $assert_session->responseContains('<p>Aliquam lobortis. Vestibulum facilisis, purus nec pulvinar iaculis vitae euismod ligula urna in dolor.</p>');
    $assert_session->responseContains('<p>Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. Donec vitae sapien ut libero venenatis faucibus.</p>');
    $publication_date = $page->find('css', 'time');
    $publication_date->hasAttribute('datetime');
    $this->assertMatchesRegularExpression("/\d+\/\d+\/\d+/", $publication_date->getText());
    $assert_session->responseContains($document_file->getFilename());
  }

}
