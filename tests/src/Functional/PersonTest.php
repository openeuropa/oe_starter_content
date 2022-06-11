<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_starter_content\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;
use Drupal\Tests\oe_starter_content\Traits\ExampleMediaTrait;

/**
 * Tests the Person content type.
 *
 * @group oe_starter_content
 */
class PersonTest extends BrowserTestBase {

  use ExampleMediaTrait;
  use MediaTypeCreationTrait;

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
    $assert_session->elementsCount('css', 'input.error', 2);
    $assert_session->pageTextContains('First name field is required.');
    $assert_session->pageTextContains('Last name field is required.');

    // Create a person with minimal required values.
    $this->drupalGet('node/add/oe_sc_person');
    $page->fillField('First name', 'Jane');
    $page->fillField('Last name', 'Smith');
    $page->pressButton('Save');

    // Assert only the page title.
    $assert_session->elementTextEquals('css', 'h1', 'Jane Smith');

    // Create a person with all values filled in.
    $this->drupalGet('node/add/oe_sc_person');
    $page->fillField('First name', 'Sherlock');
    $page->fillField('Last name', 'Holmes');
    $image_media = $this->createImageMedia();
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
    $page->pressButton('Save');

    // Assert contents of the Person detail page.
    $assert_session->pageTextContains('Person Sherlock Holmes has been created.');
    $assert_session->elementTextEquals('css', 'h1', 'Sherlock Holmes');

    $url = $this->getUrl();
    $nid = basename($url);

    // Visit the same page as anonymous user.
    $this->drupalLogout();
    $this->drupalGet('/node/' . $nid);

    // All fields should be visible to anonymous.
    $assert_session->elementTextEquals('css', 'h1', 'Sherlock Holmes');
    $image = $assert_session->elementExists('css', 'article img');
    $this->assertSame('Example image alt', $image->getAttribute('alt'));
    $this->assertSame('Example image title', $image->getAttribute('title'));
    $this->assertStringContainsString('image-test.png', $image->getAttribute('src'));
    $assert_session->pageTextContains('United Kingdom');
    $assert_session->pageTextContains('Private investigator');
    $assert_session->pageTextContains('Director');
    $assert_session->responseContains('<p>Rates can be negotiated.</p>');
    $assert_session->responseContains('<p>Do not stand below the window.</p>');
    $assert_session->responseContains('<a href="https://example.com/">Follow the crime stories</a>Twitter');
  }

}
