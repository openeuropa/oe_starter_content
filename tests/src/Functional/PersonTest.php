<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_starter_content\Functional;

use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\media\Entity\Media;
use Drupal\media\MediaInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Tests the Person content type.
 *
 * @group oe_starter_content
 */
class PersonTest extends BrowserTestBase {

  use TestFileCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_starter_content_person',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the creation of Person content through the UI.
   */
  public function testCreatePerson(): void {
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Login with permission to create person content.
    $user = $this->drupalCreateUser([
      'create oe_sc_person content',
    ]);
    $this->drupalLogin($user);

    // Attempt to create a person with no values, to test required fields.
    $this->drupalGet('node/add/oe_sc_person');
    $page->pressButton('Save');
    // The node is not saved.
    $assert_session->elementTextEquals('css', 'h1', 'Create Person');
    $assert_session->elementsCount('css', 'input.error', 4);
    $assert_session->pageTextContains('First name field is required.');
    $assert_session->pageTextContains('Last name field is required.');
    $assert_session->pageTextContains('Occupation field is required.');
    $assert_session->pageTextContains('Position field is required.');
    // Required select does not have an empty option.
    $country_field = $assert_session->selectExists('Country');
    $this->assertTrue($country_field->hasClass('required'));

    // Create a person with minimal required values.
    $this->drupalGet('node/add/oe_sc_person');
    $page->fillField('First name', 'Jane');
    $page->fillField('Last name', 'Smith');
    $page->fillField('Occupation', 'Economist');
    $page->fillField('Position', 'Accountant');
    $page->pressButton('Save');

    // Assert only the page title.
    $assert_session->elementTextEquals('css', 'h1', 'Jane Smith');
    $assert_session->pageTextContains('Economist');
    $assert_session->pageTextContains('Accountant');
    $assert_session->pageTextContains('Afghanistan');

    // Create a person with all values filled in.
    $this->drupalGet('node/add/oe_sc_person');
    $page->fillField('First name', 'Sherlock');
    $page->fillField('Last name', 'Holmes');
    $image_media = $this->createImageMedia();
    $page->fillField(
      'Short description',
      // Line breaks should turn into paragraphs.
      "Consulting Detective.\n\nElementary, my dear Watson.",
    );
    $page->fillField(
      'Use existing media',
      $image_media->label() . ' (' . $image_media->id() . ')',
    );
    $page->selectFieldOption('Country', 'United Kingdom');
    $page->fillField('Occupation', 'Private investigator');
    $page->fillField('Position', 'Director');
    $page->fillField(
      'Additional information',
      // Line breaks should turn into paragraphs.
      "Rates can be negotiated.\n\nDo not stand below the window.",
    );
    $page->fillField('oe_social_media_links[0][uri]', 'https://example.com/');
    $page->fillField('oe_social_media_links[0][title]', 'Follow the crime stories');
    $page->selectFieldOption('oe_social_media_links[0][link_type]', 'Twitter');
    // Find the documents field group.
    $documents_section = $page->find('css', '#edit-oe-sc-person-documents');
    // Add a single documents.
    $documents_section->selectFieldOption('oe_sc_person_documents[actions][bundle]', 'Document');
    $documents_section->pressButton('Add new document reference');
    $document = $this->createDocumentMedia(NULL, 0);
    $documents_section->fillField(
      'Use existing media',
      $document->label() . ' (' . $document->id() . ')',
    );
    $documents_section->pressButton('Create document reference');
    // Add a document group.
    $documents_section->selectFieldOption('oe_sc_person_documents[actions][bundle]', 'Document group');
    $documents_section->pressButton('Add new document reference');
    $documents_group_section = $documents_section->find('css', '#edit-oe-sc-person-documents-form');
    $documents_group_section->fillField('Title', 'Example documents group');
    $document = $this->createDocumentMedia(NULL, 1);
    $documents_group_section->fillField(
      'oe_sc_person_documents[form][1][oe_documents][0][target_id]',
      $document->label() . ' (' . $document->id() . ')',
    );
    $documents_group_section->pressButton('Add another item');
    $document = $this->createDocumentMedia(NULL, 2);
    $documents_group_section->fillField(
      'oe_sc_person_documents[form][1][oe_documents][1][target_id]',
      $document->label() . ' (' . $document->id() . ')',
    );
    $documents_section->pressButton('Create document reference');
    $page->pressButton('Save');

    // Assert contents of the Person detail page.
    $assert_session->pageTextContains('Person Sherlock Holmes has been created.');
    $assert_session->elementTextEquals('css', 'h1', 'Sherlock Holmes');

    $node = $this->drupalGetNodeByTitle('Sherlock Holmes');

    // Visit the same page as anonymous user.
    $this->drupalLogout();
    $this->drupalGet($node->toUrl());

    // All fields should be visible to anonymous.
    $assert_session->elementTextEquals('css', 'h1', 'Sherlock Holmes');
    $image = $assert_session->elementExists('css', 'article img');
    $this->assertSame('Example image alt', $image->getAttribute('alt'));
    $this->assertSame('Example image title', $image->getAttribute('title'));
    $assert_session->responseContains('<p>Consulting Detective.</p>');
    $assert_session->responseContains('<p>Elementary, my dear Watson.</p>');
    $this->assertStringContainsString('image-test.png', $image->getAttribute('src'));
    $assert_session->pageTextContains('United Kingdom');
    $assert_session->pageTextContains('Private investigator');
    $assert_session->pageTextContains('Director');
    $assert_session->responseContains('<p>Rates can be negotiated.</p>');
    $assert_session->responseContains('<p>Do not stand below the window.</p>');
    $assert_session->responseContains('<a href="https://example.com/">Follow the crime stories</a>Twitter');
    $assert_session->pageTextContains('Example documents group');
    $this->assertLinkHrefContains('text-0.txt', 'files/text-0.txt');
    $this->assertLinkHrefContains('text-1.txt', 'files/text-1.txt');
    $this->assertLinkHrefContains('text-2.txt', 'files/text-2.txt');
  }

  /**
   * Asserts a link by its link text and a part of its href attribute.
   *
   * @param string $text
   *   Expected link text.
   * @param string $href_part
   *   Part of the expected href attribute.
   */
  protected function assertLinkHrefContains(string $text, string $href_part): void {
    $link = $this->assertSession()->elementExists('named', ['link', $text]);
    $this->assertStringContainsString($href_part, $link->getAttribute('href'));
  }

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
