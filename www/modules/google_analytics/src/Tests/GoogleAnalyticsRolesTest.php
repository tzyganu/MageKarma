<?php

/**
 * @file
 * Contains \Drupal\google_analytics\Tests\GoogleAnalyticsBasicTest.
 */

namespace Drupal\google_analytics\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test roles functionality of Google Analytics module.
 *
 * @group Google Analytics
 */
class GoogleAnalyticsRolesTest extends WebTestBase {

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

  function testGoogleAnalyticsRolesTracking() {
    $ua_code = 'UA-123456-4';
    \Drupal::config('google_analytics.settings')->set('account', $ua_code)->save();

    // Test if the default settings are working as expected.

    // Add to the selected roles only.
    \Drupal::config('google_analytics.settings')->set('visibility.roles_enabled', 0)->save();
    // Enable tracking for all users.
    \Drupal::config('google_analytics.settings')->set('visibility.roles', array())->save();

    // Check tracking code visibility.
    $this->drupalGet('');
    $this->assertRaw($ua_code, '[testGoogleAnalyticsRoleVisibility]: Tracking code is displayed for anonymous users on frontpage with default settings.');
    $this->drupalGet('admin');
    $this->assertResponse(403);
    $this->assertRaw('/403.html', '[testGoogleAnalyticsRoleVisibility]: 403 Forbidden tracking code is displayed for anonymous users in admin section with default settings.');

    $this->drupalLogin($this->admin_user);

    $this->drupalGet('');
    $this->assertRaw($ua_code, '[testGoogleAnalyticsRoleVisibility]: Tracking code is displayed for authenticated users on frontpage with default settings.');
    $this->drupalGet('admin');
    $this->assertNoRaw($ua_code, '[testGoogleAnalyticsRoleVisibility]: Tracking code is NOT displayed for authenticated users in admin section with default settings.');

    // Test if the non-default settings are working as expected.

    // Enable tracking only for authenticated users.
    \Drupal::config('google_analytics.settings')->set('visibility.roles', array(DRUPAL_AUTHENTICATED_RID => DRUPAL_AUTHENTICATED_RID))->save();

    $this->drupalGet('');
    $this->assertRaw($ua_code, '[testGoogleAnalyticsRoleVisibility]: Tracking code is displayed for authenticated users only on frontpage.');

    $this->drupalLogout();
    $this->drupalGet('');
    $this->assertNoRaw($ua_code, '[testGoogleAnalyticsRoleVisibility]: Tracking code is NOT displayed for anonymous users on frontpage.');

    // Add to every role except the selected ones.
    \Drupal::config('google_analytics.settings')->set('visibility.roles_enabled', 1)->save();
    // Enable tracking for all users.
    \Drupal::config('google_analytics.settings')->set('visibility.roles', array())->save();

    // Check tracking code visibility.
    $this->drupalGet('');
    $this->assertRaw($ua_code, '[testGoogleAnalyticsRoleVisibility]: Tracking code is added to every role and displayed for anonymous users.');
    $this->drupalGet('admin');
    $this->assertResponse(403);
    $this->assertRaw('/403.html', '[testGoogleAnalyticsRoleVisibility]: 403 Forbidden tracking code is shown for anonymous users if every role except the selected ones is selected.');

    $this->drupalLogin($this->admin_user);

    $this->drupalGet('');
    $this->assertRaw($ua_code, '[testGoogleAnalyticsRoleVisibility]: Tracking code is added to every role and displayed on frontpage for authenticated users.');
    $this->drupalGet('admin');
    $this->assertNoRaw($ua_code, '[testGoogleAnalyticsRoleVisibility]: Tracking code is added to every role and NOT displayed in admin section for authenticated users.');

    // Disable tracking for authenticated users.
    \Drupal::config('google_analytics.settings')->set('visibility.roles', array(DRUPAL_AUTHENTICATED_RID => DRUPAL_AUTHENTICATED_RID))->save();

    $this->drupalGet('');
    $this->assertNoRaw($ua_code, '[testGoogleAnalyticsRoleVisibility]: Tracking code is NOT displayed on frontpage for excluded authenticated users.');
    $this->drupalGet('admin');
    $this->assertNoRaw($ua_code, '[testGoogleAnalyticsRoleVisibility]: Tracking code is NOT displayed in admin section for excluded authenticated users.');

    $this->drupalLogout();
    $this->drupalGet('');
    $this->assertRaw($ua_code, '[testGoogleAnalyticsRoleVisibility]: Tracking code is displayed on frontpage for included anonymous users.');
  }

}
