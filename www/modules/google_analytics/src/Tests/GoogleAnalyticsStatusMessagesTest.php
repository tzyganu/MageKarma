<?php

/**
 * @file
 * Contains \Drupal\google_analytics\Tests\GoogleAnalyticsStatusMessagesTest.
 */

namespace Drupal\google_analytics\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test status messages functionality of Google Analytics module.
 *
 * @group Google Analytics
 */
class GoogleAnalyticsStatusMessagesTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('google_analytics');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $permissions = array(
      'access administration pages',
      'administer google analytics',
    );

    // User to set up google_analytics.
    $this->admin_user = $this->drupalCreateUser($permissions);
  }

  function testGoogleAnalyticsStatusMessages() {
    $ua_code = 'UA-123456-4';
    \Drupal::config('google_analytics.settings')->set('account', $ua_code)->save();

    // Enable logging of errors only.
    \Drupal::config('google_analytics.settings')->set('track.messages', array('error' => 'error'))->save();

    $this->drupalPostForm('user/login', array(), t('Log in'));
    $this->assertRaw('ga("send", "event", "Messages", "Error message", "Username field is required.");', '[testGoogleAnalyticsStatusMessages]: Event message "Username field is required." is shown.');
    $this->assertRaw('ga("send", "event", "Messages", "Error message", "Password field is required.");', '[testGoogleAnalyticsStatusMessages]: Event message "Password field is required." is shown.');

    // @todo: investigate why drupal_set_message() fails.
    //drupal_set_message('Example status message.', 'status');
    //drupal_set_message('Example warning message.', 'warning');
    //drupal_set_message('Example error message.', 'error');
    //drupal_set_message('Example error <em>message</em> with html tags and <a href="http://example.com/">link</a>.', 'error');
    //$this->drupalGet('');
    //$this->assertNoRaw('ga("send", "event", "Messages", "Status message", "Example status message.");', '[testGoogleAnalyticsStatusMessages]: Example status message is not enabled for tracking.');
    //$this->assertNoRaw('ga("send", "event", "Messages", "Warning message", "Example warning message.");', '[testGoogleAnalyticsStatusMessages]: Example warning message is not enabled for tracking.');
    //$this->assertRaw('ga("send", "event", "Messages", "Error message", "Example error message.");', '[testGoogleAnalyticsStatusMessages]: Example error message is shown.');
    //$this->assertRaw('ga("send", "event", "Messages", "Error message", "Example error message with html tags and link.");', '[testGoogleAnalyticsStatusMessages]: HTML has been stripped successful from Example error message with html tags and link.');
  }
}
