<?php

/**
 * @file
 * Contains \Drupal\google_analytics\Tests\GoogleAnalyticsCustomDimensionsAndMetricsTest.
 */

namespace Drupal\google_analytics\Tests;

use Drupal\Component\Serialization\Json;
use Drupal\simpletest\WebTestBase;

/**
 * Test custom dimensions and metrics functionality of Google Analytics module.
 *
 * @group Google Analytics
 * @requires module token
 */
class GoogleAnalyticsCustomDimensionsAndMetricsTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('google_analytics', 'token');

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

  function testGoogleAnalyticsCustomDimensions() {
    $ua_code = 'UA-123456-3';
    \Drupal::config('google_analytics.settings')->set('account', $ua_code)->save();

    // Basic test if the feature works.
    $google_analytics_custom_dimension = array(
      1 => array(
        'index' => 1,
        'value' => 'Bar 1',
      ),
      2 => array(
        'index' => 2,
        'value' => 'Bar 2',
      ),
      3 => array(
        'index' => 3,
        'value' => 'Bar 3',
      ),
      4 => array(
        'index' => 4,
        'value' => 'Bar 4',
      ),
      5 => array(
        'index' => 5,
        'value' => 'Bar 5',
      ),
    );
    \Drupal::config('google_analytics.settings')->set('custom.dimension', $google_analytics_custom_dimension)->save();
    $this->drupalGet('');

    foreach ($google_analytics_custom_dimension as $dimension) {
      $this->assertRaw('ga("set", ' . Json::encode('dimension' . $dimension['index']) . ', ' . Json::encode($dimension['value']) . ');', '[testGoogleAnalyticsCustomDimensionsAndMetrics]: Dimension #' . $dimension['index'] . ' is shown.');
    }

    // Test whether tokens are replaced in custom dimension values.
    $site_slogan = $this->randomMachineName(16);
    \Drupal::config('system.site')->set('slogan', $site_slogan)->save();

    $google_analytics_custom_dimension = array(
      1 => array(
        'index' => 1,
        'value' => 'Value: [site:slogan]',
      ),
      2 => array(
        'index' => 2,
        'value' => $this->randomMachineName(16),
      ),
      3 => array(
        'index' => 3,
        'value' => '',
      ),
      // #2300701: Custom dimensions and custom metrics not outputed on zero value.
      4 => array(
        'index' => 4,
        'value' => '0',
      ),
    );
    \Drupal::config('google_analytics.settings')->set('custom.dimension', $google_analytics_custom_dimension)->save();
    $this->verbose('<pre>' . print_r($google_analytics_custom_dimension, TRUE) . '</pre>');

    $this->drupalGet('');
    $this->assertRaw('ga("set", ' . Json::encode('dimension1') . ', ' . Json::encode("Value: $site_slogan") . ');', '[testGoogleAnalyticsCustomDimensionsAndMetrics]: Tokens have been replaced in dimension value.');
    $this->assertRaw('ga("set", ' . Json::encode('dimension2') . ', ' . Json::encode($google_analytics_custom_dimension['2']['value']) . ');', '[testGoogleAnalyticsCustomDimensionsAndMetrics]: Random value is shown.');
    $this->assertNoRaw('ga("set", ' . Json::encode('dimension3') . ', ' . Json::encode('') . ');', '[testGoogleAnalyticsCustomDimensionsAndMetrics]: Empty value is not shown.');
    $this->assertRaw('ga("set", ' . Json::encode('dimension4') . ', ' . Json::encode('0') . ');', '[testGoogleAnalyticsCustomDimensionsAndMetrics]: Value 0 is shown.');
  }

  function testGoogleAnalyticsCustomMetrics() {
    $ua_code = 'UA-123456-3';
    \Drupal::config('google_analytics.settings')->set('account', $ua_code)->save();

    // Basic test if the feature works.
    $google_analytics_custom_metric = array(
      1 => array(
        'index' => 1,
        'value' => '6',
        'value_expected' => 6,
      ),
      2 => array(
        'index' => 2,
        'value' => '8000',
        'value_expected' => 8000,
      ),
      3 => array(
        'index' => 3,
        'value' => '7.8654',
        'value_expected' => 7.8654,
      ),
      4 => array(
        'index' => 4,
        'value' => '1123.4',
        'value_expected' => 1123.4,
      ),
      5 => array(
        'index' => 5,
        'value' => '5,67',
        'value_expected' => 5,
      ),
    );
    \Drupal::config('google_analytics.settings')->set('custom.metric', $google_analytics_custom_metric)->save();
    $this->drupalGet('');

    foreach ($google_analytics_custom_metric as $metric) {
      $this->assertRaw('ga("set", ' . Json::encode('metric' . $metric['index']) . ', ' . Json::encode($metric['value_expected']) . ');', '[testGoogleAnalyticsCustomDimensionsAndMetrics]: Metric #' . $metric['index'] . ' is shown.');
    }

    // Test whether tokens are replaced in custom metric values.
    $google_analytics_custom_metric = array(
      1 => array(
        'index' => 1,
        'value' => '[current-user:roles:count]',
      ),
      2 => array(
        'index' => 2,
        'value' => mt_rand(),
      ),
      3 => array(
        'index' => 3,
        'value' => '',
      ),
      // #2300701: Custom dimensions and custom metrics not outputed on zero value.
      4 => array(
        'index' => 4,
        'value' => '0',
      ),
    );
    \Drupal::config('google_analytics.settings')->set('custom.metric', $google_analytics_custom_metric)->save();
    $this->verbose('<pre>' . print_r($google_analytics_custom_metric, TRUE) . '</pre>');

    $this->drupalGet('');
    $this->assertRaw('ga("set", ' . Json::encode('metric1') . ', ', '[testGoogleAnalyticsCustomDimensionsAndMetrics]: Tokens have been replaced in metric value.');
    $this->assertRaw('ga("set", ' . Json::encode('metric2') . ', ' . Json::encode($google_analytics_custom_metric['2']['value']) . ');', '[testGoogleAnalyticsCustomDimensionsAndMetrics]: Random value is shown.');
    $this->assertNoRaw('ga("set", ' . Json::encode('metric3') . ', ' . Json::encode('') . ');', '[testGoogleAnalyticsCustomDimensionsAndMetrics]: Empty value is not shown.');
    $this->assertRaw('ga("set", ' . Json::encode('metric4') . ', ' . Json::encode(0) . ');', '[testGoogleAnalyticsCustomDimensionsAndMetrics]: Value 0 is shown.');
  }
}
