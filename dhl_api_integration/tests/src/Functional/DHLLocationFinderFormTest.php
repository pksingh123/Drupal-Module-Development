<?php

declare(strict_types=1);

namespace Drupal\Tests\dhl_api_integration\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the DHLLocationFinderForm functionality.
 *
 * @group dhl_api_integration
 */
class DHLLocationFinderFormTest extends BrowserTestBase
{

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'dhl_api_integration',
    'config',
  ];
  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Set up the test.
   */
  protected function setUp(): void
  {
    parent::setUp();
    // Set configuration values for the DHL API.
    $this->configFactory = \Drupal::configFactory();
    $this->configFactory->getEditable('dhl_api_integration.settings')
      ->set('api_key', 'demo-key')
      ->set('api_base_url', 'https://api.dhl.com/location-finder/v1')
      ->save();
  }

  /**
   * Tests the DHLLocationFinderForm submission and output.
   */
  public function testFormSubmission()
  {
    // Create a user with permission to access the form.
    $user = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($user);
    $this->drupalGet('/location-finder');
    $this->assertSession()->fieldExists('country');
    $this->assertSession()->fieldExists('city');
    $this->assertSession()->fieldExists('postal_code');
    $edit = [
      'country' => 'CZ',
      'city' => 'Prague',
      'postal_code' => '11000',
    ];
    $this->submitForm($edit, 'Find Locations');
    $this->assertSession()->statusCodeEquals(200);
  }
}
