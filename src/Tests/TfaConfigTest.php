<?php

namespace Drupal\tfa\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the Tfa UI.
 *
 * @group Tfa
 */
class TfaConfigTest extends WebTestBase {
  /**
   * User doing the TFA Validation.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $webUser;

  /**
   * Administrator to handle configurations.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'tfa_test_plugins',
    'tfa',
    'encrypt',
    'encrypt_test',
    'key',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    // Enable TFA module and the test module.
    parent::setUp();
    $this->webUser = $this->drupalCreateUser(['setup own tfa']);
    $this->adminUser = $this->drupalCreateUser(['administer users', 'administer site configuration']);
    $this->generateRoleKey();
    $this->generateEncryptionProfile();
  }

  /**
   * Test to check if configurations are working as desired.
   */
  public function testTfaConfig() {
    // Check that config form is restricted for users.
    $this->drupalLogin($this->webUser);
    $this->drupalGet('admin/config/people/tfa');
    $this->assertResponse(403);

    // Check that config form is accessible to admins.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/config/people/tfa');
    $this->assertResponse(200);
    $this->assertText($this->uiStrings('config-form'));

    $edit = [
      'tfa_enabled' => TRUE,
      'tfa_validate' => 'tfa_test_plugins_validation',
      'tfa_login[tfa_trusted_browser]' => 'tfa_trusted_browser',
    ];

    $this->drupalPostForm(NULL, $edit, t('Save configuration'));
    $this->assertText($this->uiStrings('config-saved'));
    $this->assertOptionSelected('edit-tfa-validate', 'tfa_test_plugins_validation', t('Plugin selected'));
  }

  /**
   * TFA module user interface strings.
   *
   * @param string $id
   *   ID of string.
   *
   * @return string
   *   UI message for corresponding id.
   */
  protected function uiStrings($id) {
    switch ($id) {
      case 'config-form':
        return 'TFA Settings';

      case 'config-saved':
        return 'The configuration options have been saved.';
    }
  }

  /**
   * Generate a Role key.
   */
  public function generateRoleKey() {
    // Generate a key; at this stage the key hasn't been configured completely.
    $values = [
      'id' => 'testing_key_128',
      'label' => 'Testing Key 128 bit',
      'key_type' => "encryption",
      'key_type_settings' => ['key_size' => '128'],
      'key_provider' => 'config',
      'key_input' => 'none',
      // This is actually 16bytes but oh well..
      'key_provider_settings' => ['key_value' => 'mustbesixteenbit'],
    ];
    \Drupal::entityTypeManager()
      ->getStorage('key')
      ->create($values)
      ->save();
  }

  /**
   * Generate an Encryption profile for a Role key.
   */
  public function generateEncryptionProfile() {
    $values = [
      'id' => 'test_encryption_profile',
      'label' => 'Test encryption profile',
      'encryption_method' => 'test_encryption_method',
      'encryption_key' => 'testing_key_128',
    ];

    \Drupal::entityTypeManager()
      ->getStorage('encryption_profile')
      ->create($values)
      ->save();
  }
}
