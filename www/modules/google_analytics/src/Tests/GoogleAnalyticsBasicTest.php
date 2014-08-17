<?php

/**
 * @file
 * Contains \Drupal\google_analytics\Tests\GoogleAnalyticsBasicTest.
 */

namespace Drupal\google_analytics\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test basic functionality of Google Analytics module.
 *
 * @group Google Analytics
 */
class GoogleAnalyticsBasicTest extends WebTestBase {

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
      'administer modules',
      'administer site configuration',
    );

    // User to set up google_analytics.
    $this->admin_user = $this->drupalCreateUser($permissions);
    $this->drupalLogin($this->admin_user);
  }

  function testGoogleAnalyticsConfiguration() {
    // Check if Configure link is available on 'Extend' page.
    // Requires 'administer modules' permission.
    $this->drupalGet('admin/modules');
    $this->assertRaw('admin/config/system/google-analytics', '[testGoogleAnalyticsConfiguration]: Configure link from Extend page to Google Analytics Settings page exists.');

    // Check if Configure link is available on 'Status Reports' page. NOTE: Link is only shown without UA code configured.
    // Requires 'administer site configuration' permission.
    $this->drupalGet('admin/reports/status');
    $this->assertRaw('admin/config/system/google-analytics', '[testGoogleAnalyticsConfiguration]: Configure link from Status Reports page to Google Analytics Settings page exists.');

    // Check for setting page's presence.
    $this->drupalGet('admin/config/system/google-analytics');
    $this->assertRaw(t('Web Property ID'), '[testGoogleAnalyticsConfiguration]: Settings page displayed.');

    // Check for account code validation.
    $edit['google_analytics_account'] = $this->randomMachineName(2);
    $this->drupalPostForm('admin/config/system/google-analytics', $edit, t('Save configuration'));
    $this->assertRaw(t('A valid Google Analytics Web Property ID is case sensitive and formatted like UA-xxxxxxx-yy.'), '[testGoogleAnalyticsConfiguration]: Invalid Web Property ID number validated.');
  }

  function testGoogleAnalyticsPageVisibility() {
    // Verify that no tracking code is embedded into the webpage; if there is
    // only the module installed, but UA code not configured. See #2246991.
    $this->drupalGet('');
    $this->assertNoRaw('//www.google-analytics.com/analytics.js', '[testGoogleAnalyticsPageVisibility]: Tracking code is not displayed without UA code configured.');

    $ua_code = 'UA-123456-1';
    \Drupal::config('google_analytics.settings')->set('account', $ua_code)->save();

    // Show tracking on "every page except the listed pages".
    \Drupal::config('google_analytics.settings')->set('visibility.pages_enable', 0)->save();
    // Disable tracking on "admin*" pages only.
    \Drupal::config('google_analytics.settings')->set('visibility.pages', "admin\nadmin/*")->save();
    // Enable tracking only for authenticated users only.
    \Drupal::config('google_analytics.settings')->set('visibility.roles', array(DRUPAL_AUTHENTICATED_RID => DRUPAL_AUTHENTICATED_RID))->save();

    // Check tracking code visibility.
    $this->drupalGet('');
    $this->assertRaw($ua_code, '[testGoogleAnalyticsPageVisibility]: Tracking code is displayed for authenticated users.');

    // Test whether tracking code is not included on pages to omit.
    $this->drupalGet('admin');
    $this->assertNoRaw($ua_code, '[testGoogleAnalyticsPageVisibility]: Tracking code is not displayed on admin page.');
    $this->drupalGet('admin/config/system/google-analytics');
    // Checking for tracking code URI here, as $ua_code is displayed in the form.
    $this->assertNoRaw('//www.google-analytics.com/analytics.js', '[testGoogleAnalyticsPageVisibility]: Tracking code is not displayed on admin subpage.');

    // Test whether tracking code display is properly flipped.
    \Drupal::config('google_analytics.settings')->set('visibility.pages_enabled', 1)->save();
    $this->drupalGet('admin');
    $this->assertRaw($ua_code, '[testGoogleAnalyticsPageVisibility]: Tracking code is displayed on admin page.');
    $this->drupalGet('admin/config/system/google-analytics');
    // Checking for tracking code URI here, as $ua_code is displayed in the form.
    $this->assertRaw('//www.google-analytics.com/analytics.js', '[testGoogleAnalyticsPageVisibility]: Tracking code is displayed on admin subpage.');
    $this->drupalGet('');
    $this->assertNoRaw($ua_code, '[testGoogleAnalyticsPageVisibility]: Tracking code is NOT displayed on front page.');

    // Test whether tracking code is not display for anonymous.
    $this->drupalLogout();
    $this->drupalGet('');
    $this->assertNoRaw($ua_code, '[testGoogleAnalyticsPageVisibility]: Tracking code is NOT displayed for anonymous.');

    // Switch back to every page except the listed pages.
    \Drupal::config('google_analytics.settings')->set('visibility.pages_enabled', 0)->save();
    // Enable tracking code for all user roles.
    \Drupal::config('google_analytics.settings')->set('visibility.roles', array())->save();

    // Test whether 403 forbidden tracking code is shown if user has no access.
    $this->drupalGet('admin');
    $this->assertResponse(403);
    $this->assertRaw('/403.html', '[testGoogleAnalyticsPageVisibility]: 403 Forbidden tracking code shown if user has no access.');

    // Test whether 404 not found tracking code is shown on non-existent pages.
    $this->drupalGet($this->randomMachineName(64));
    $this->assertResponse(404);
    $this->assertRaw('/404.html', '[testGoogleAnalyticsPageVisibility]: 404 Not Found tracking code shown on non-existent page.');

    // DNT Tests:
    // Enable system internal page cache.
    \Drupal::config('system.performance')
      ->set('cache.page.use_internal', 1)
      ->set('cache.page.max_age', 3600)
      ->save();
    // Test whether DNT headers will fail to disable embedding of tracking code.
    $this->drupalGet('', array(), array('DNT: 1'));
    $this->assertRaw('ga("send", "pageview");', '[testGoogleAnalyticsDNTVisibility]: DNT header send from client, but page caching is enabled and tracker cannot removed.');
    // DNT works only with system internal page cache disabled.
    \Drupal::config('system.performance')
      ->set('cache.page.use_internal', 0)
      ->set('cache.page.max_age', 0)
      ->save();
    $this->drupalGet('');
    $this->assertRaw('ga("send", "pageview");', '[testGoogleAnalyticsDNTVisibility]: Tracking is enabled without DNT header.');
    // Test whether DNT header is able to remove the tracking code.
    $this->drupalGet('', array(), array('DNT: 1'));
    $this->assertNoRaw('ga("send", "pageview");', '[testGoogleAnalyticsDNTVisibility]: DNT header received from client. Tracking has been disabled by browser.');
    // Disable DNT feature and see if tracker is still embedded.
    \Drupal::config('google_analytics.settings')->set('privacy.donottrack', 0)->save();
    $this->drupalGet('', array(), array('DNT: 1'));
    $this->assertRaw('ga("send", "pageview");', '[testGoogleAnalyticsDNTVisibility]: DNT feature is disabled, DNT header from browser has been ignored.');
  }

  function testGoogleAnalyticsTrackingCode() {
    $ua_code = 'UA-123456-2';
    \Drupal::config('google_analytics.settings')->set('account', $ua_code)->save();

    // Show tracking code on every page except the listed pages.
    \Drupal::config('google_analytics.settings')->set('visibility.pages_enabled', 0)->save();
    // Enable tracking code for all user roles.
    \Drupal::config('google_analytics.settings')->set('visibility.roles', array())->save();

    /* Sample JS code as added to page:
    <script type="text/javascript" src="/sites/all/modules/google_analytics/google_analytics.js?w"></script>
    <script>
    (function(i,s,o,g,r,a,m){
    i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,"script","//www.google-analytics.com/analytics.js","ga");
    ga('create', 'UA-123456-7');
    ga('send', 'pageview');
    </script>
    <!-- End Google Analytics -->
    */

    // Test whether tracking code uses latest JS.
    \Drupal::config('google_analytics.settings')->set('cache', 0)->save();
    $this->drupalGet('');
    $this->assertRaw('//www.google-analytics.com/analytics.js', '[testGoogleAnalyticsTrackingCode]: Latest tracking code used.');

    // Test whether anonymize visitors IP address feature has been enabled.
    \Drupal::config('google_analytics.settings')->set('privacy.anonymizeip', 0)->save();
    $this->drupalGet('');
    $this->assertNoRaw('ga("set", "anonymizeIp", true);', '[testGoogleAnalyticsTrackingCode]: Anonymize visitors IP address not found on frontpage.');
    // Enable anonymizing of IP addresses.
    \Drupal::config('google_analytics.settings')->set('privacy.anonymizeip', 1)->save();
    $this->drupalGet('');
    $this->assertRaw('ga("set", "anonymizeIp", true);', '[testGoogleAnalyticsTrackingCode]: Anonymize visitors IP address found on frontpage.');

    // Test if track Enhanced Link Attribution is enabled.
    \Drupal::config('google_analytics.settings')->set('track.linkid', 1)->save();
    $this->drupalGet('');
    $this->assertRaw('ga("require", "linkid", "linkid.js");', '[testGoogleAnalyticsTrackingCode]: Tracking code for Enhanced Link Attribution is enabled.');

    // Test if track Enhanced Link Attribution is disabled.
    \Drupal::config('google_analytics.settings')->set('track.linkid', 0)->save();
    $this->drupalGet('');
    $this->assertNoRaw('ga("require", "linkid", "linkid.js");', '[testGoogleAnalyticsTrackingCode]: Tracking code for Enhanced Link Attribution is not enabled.');

    // Test if tracking of url fragments is enabled.
    \Drupal::config('google_analytics.settings')->set('track.urlfragments', 1)->save();
    $this->drupalGet('');
    $this->assertRaw('ga("set", "page", location.pathname + location.search + location.hash);', '[testGoogleAnalyticsTrackingCode]: Tracking code for url fragments is enabled.');

    // Test if tracking of url fragments is disabled.
    \Drupal::config('google_analytics.settings')->set('track.urlfragments', 0)->save();
    $this->drupalGet('');
    $this->assertNoRaw('ga("set", "page", location.pathname + location.search + location.hash);', '[testGoogleAnalyticsTrackingCode]: Tracking code for url fragments is not enabled.');

    // Test if tracking of User ID is enabled.
    \Drupal::config('google_analytics.settings')->set('track.userid', 1)->save();
    $this->drupalGet('');
    $this->assertRaw(', {"userId":"', '[testGoogleAnalyticsTrackingCode]: Tracking code for User ID is enabled.');

    // Test if tracking of User ID is disabled.
    \Drupal::config('google_analytics.settings')->set('track.userid', 0)->save();
    $this->drupalGet('');
    $this->assertNoRaw(', {"userId":"', '[testGoogleAnalyticsTrackingCode]: Tracking code for User ID is disabled.');

    // Test if track display features is enabled.
    \Drupal::config('google_analytics.settings')->set('track.displayfeatures', 1)->save();
    $this->drupalGet('');
    $this->assertRaw('ga("require", "displayfeatures");', '[testGoogleAnalyticsTrackingCode]: Tracking code for display features is enabled.');

    // Test if track display features is disabled.
    \Drupal::config('google_analytics.settings')->set('track.displayfeatures', 0)->save();
    $this->drupalGet('');
    $this->assertNoRaw('ga("require", "displayfeatures");', '[testGoogleAnalyticsTrackingCode]: Tracking code for display features is not enabled.');

    // Test whether single domain tracking is active.
    $this->drupalGet('');
    $this->assertNoRaw('{"cookieDomain":"', '[testGoogleAnalyticsTrackingCode]: Single domain tracking is active.');

    // Enable "One domain with multiple subdomains".
    \Drupal::config('google_analytics.settings')->set('domain_mode', 1)->save();
    $this->drupalGet('');

    // Test may run on localhost, an ipaddress or real domain name.
    // TODO: Workaround to run tests successfully. This feature cannot tested reliable.
    global $cookie_domain;
    if (count(explode('.', $cookie_domain)) > 2 && !is_numeric(str_replace('.', '', $cookie_domain))) {
      $this->assertRaw('{"cookieDomain":"' . $cookie_domain . '"}', '[testGoogleAnalyticsTrackingCode]: One domain with multiple subdomains is active on real host.');
    }
    else {
      // Special cases, Localhost and IP addresses don't show 'cookieDomain'.
      $this->assertNoRaw('{"cookieDomain":"' . $cookie_domain . '"}', '[testGoogleAnalyticsTrackingCode]: One domain with multiple subdomains may be active on localhost (test result is not reliable).');
    }

    // Enable "Multiple top-level domains" tracking.
    \Drupal::config('google_analytics.settings')
      ->set('domain_mode', 2)
      ->set('cross_domains', "www.example.com\nwww.example.net")
      ->save();
    $this->drupalGet('');
    $this->assertRaw('ga("create", "' . $ua_code . '", {"allowLinker":true', '[testGoogleAnalyticsTrackingCode]: "allowLinker" has been found. Cross domain tracking is active.');
    $this->assertRaw('ga("require", "linker");', '[testGoogleAnalyticsTrackingCode]: Require linker has been found. Cross domain tracking is active.');
    $this->assertRaw('ga("linker:autoLink", ["www.example.com","www.example.net"]);', '[testGoogleAnalyticsTrackingCode]: "linker:autoLink" has been found. Cross domain tracking is active.');
    $this->assertRaw('"trackCrossDomains":["www.example.com","www.example.net"]', '[testGoogleAnalyticsTrackingCode]: Cross domain tracking with www.example.com and www.example.net is active.');
    \Drupal::config('google_analytics.settings')->set('domain_mode', 0)->save();

    // Test whether debugging script has been enabled.
    \Drupal::config('google_analytics.settings')->set('debug', 1)->save();
    $this->drupalGet('');
    $this->assertRaw('//www.google-analytics.com/analytics_debug.js', '[testGoogleAnalyticsTrackingCode]: Google debugging script has been enabled.');

    // Check if text and link is shown on 'Status Reports' page.
    // Requires 'administer site configuration' permission.
    $this->drupalGet('admin/reports/status');
    $this->assertRaw(t('Google Analytics module has debugging enabled. Please disable debugging setting in production sites from the <a href="@url">Google Analytics settings page</a>.', array('@url' => url('admin/config/system/google-analytics'))), '[testGoogleAnalyticsConfiguration]: Debugging enabled is shown on Status Reports page.');

    // Test whether debugging script has been disabled.
    \Drupal::config('google_analytics.settings')->set('debug', 0)->save();
    $this->drupalGet('');
    $this->assertRaw('//www.google-analytics.com/analytics.js', '[testGoogleAnalyticsTrackingCode]: Google debugging script has been disabled.');

    // Test whether the CREATE and BEFORE and AFTER code is added to the tracker.
    $codesnippet_create = array(
      'cookieDomain' => 'foo.example.com',
      'cookieName' => 'myNewName',
      'cookieExpires' => 20000,
      'allowAnchor' => TRUE,
      'sampleRate' => 4.3,
    );
    \Drupal::config('google_analytics.settings')
      ->set('codesnippet.create', $codesnippet_create)
      ->set('codesnippet.before', 'ga("set", "forceSSL", true);')
      ->set('codesnippet.after', 'ga("create", "UA-123456-3", {"name": "newTracker"});ga("newTracker.send", "pageview");')
      ->save();
    $this->drupalGet('');
    $this->assertRaw('ga("create", "' . $ua_code . '", {"cookieDomain":"foo.example.com","cookieName":"myNewName","cookieExpires":20000,"allowAnchor":true,"sampleRate":4.3});', '[testGoogleAnalyticsTrackingCode]: Create only fields have been found.');
    $this->assertRaw('ga("set", "forceSSL", true);', '[testGoogleAnalyticsTrackingCode]: Before codesnippet will force http pages to also send all beacons using https.');
    $this->assertRaw('ga("create", "UA-123456-3", {"name": "newTracker"});', '[testGoogleAnalyticsTrackingCode]: After codesnippet with "newTracker" tracker has been found.');
  }
}
