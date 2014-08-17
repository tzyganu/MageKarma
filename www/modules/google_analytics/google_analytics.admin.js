(function ($, window) {

"use strict";
  
/**
 * Provide the summary information for the tracking settings vertical tabs.
 */
Drupal.behaviors.trackingSettingsSummary = {
  attach: function () {
    // Make sure this behavior is processed only if drupalSetSummary is defined.
    if (typeof jQuery.fn.drupalSetSummary == 'undefined') {
      return;
    }

    $('#edit-page-vis-settings').drupalSetSummary(function (context) {
      var $radio = $('input[name="google_analytics_visibility_pages"]:checked', context);
      if ($radio.val() == 0) {
        if (!$('textarea[name="google_analytics_pages"]', context).val()) {
          return Drupal.t('Not restricted');
        }
        else {
          return Drupal.t('All pages with exceptions');
        }
      }
      else {
        return Drupal.t('Restricted to certain pages');
      }
    });

    $('#edit-role-vis-settings').drupalSetSummary(function (context) {
      var vals = [];
      $('input[type="checkbox"]:checked', context).each(function () {
        vals.push($.trim($(this).next('label').text()));
      });
      if (!vals.length) {
        return Drupal.t('Not restricted');
      }
      else if ($('input[name="google_analytics_visibility_roles"]:checked', context).val() == 1) {
        return Drupal.t('Excepted: @roles', {'@roles' : vals.join(', ')});
      }
      else {
        return vals.join(', ');
      }
    });

    $('#edit-user-vis-settings').drupalSetSummary(function (context) {
      var $radio = $('input[name="google_analytics_custom"]:checked', context);
      if ($radio.val() == 0) {
        return Drupal.t('Not customizable');
      }
      else if ($radio.val() == 1) {
        return Drupal.t('On by default with opt out');
      }
      else {
        return Drupal.t('Off by default with opt in');
      }
    });

    $('#edit-linktracking').drupalSetSummary(function (context) {
      var vals = [];
      if ($('input#edit-google-analytics-trackoutbound', context).is(':checked')) {
        vals.push(Drupal.t('Outbound links'));
      }
      if ($('input#edit-google-analytics-trackmailto', context).is(':checked')) {
        vals.push(Drupal.t('Mailto links'));
      }
      if ($('input#edit-google-analytics-trackfiles', context).is(':checked')) {
        vals.push(Drupal.t('Downloads'));
      }
      if (!vals.length) {
        return Drupal.t('Not tracked');
      }
      return Drupal.t('@items enabled', {'@items' : vals.join(', ')});
    });

    $('#edit-messagetracking').drupalSetSummary(function (context) {
      var vals = [];
      $('input[type="checkbox"]:checked', context).each(function () {
        vals.push($.trim($(this).next('label').text()));
      });
      if (!vals.length) {
        return Drupal.t('Not tracked');
      }
      return Drupal.t('@items enabled', {'@items' : vals.join(', ')});
    });

    $('#edit-search-and-advertising').drupalSetSummary(function (context) {
      var vals = [];
      if ($('input#edit-google-analytics-site-search', context).is(':checked')) {
        vals.push(Drupal.t('Site search'));
      }
      if ($('input#edit-google-analytics-trackadsense', context).is(':checked')) {
        vals.push(Drupal.t('AdSense ads'));
      }
      if ($('input#edit-google-analytics-trackdisplayfeatures', context).is(':checked')) {
        vals.push(Drupal.t('Display features'));
      }
      if (!vals.length) {
        return Drupal.t('Not tracked');
      }
      return Drupal.t('@items enabled', {'@items' : vals.join(', ')});
    });

    $('#edit-domain-tracking').drupalSetSummary(function (context) {
      var $radio = $('input[name="google_analytics_domain_mode"]:checked', context);
      if ($radio.val() == 0) {
        return Drupal.t('A single domain');
      }
      else if ($radio.val() == 1) {
        return Drupal.t('One domain with multiple subdomains');
      }
      else {
        return Drupal.t('Multiple top-level domains');
      }
    });

    $('#edit-privacy').drupalSetSummary(function (context) {
      var vals = [];
      if ($('input#edit-google-analytics-tracker-anonymizeip', context).is(':checked')) {
        vals.push(Drupal.t('Anonymize IP'));
      }
      if ($('input#edit-google-analytics-privacy-donottrack', context).is(':checked')) {
        vals.push(Drupal.t('Universal web tracking opt-out'));
      }
      if (!vals.length) {
        return Drupal.t('No privacy');
      }
      return Drupal.t('@items enabled', {'@items' : vals.join(', ')});
    });
  }
};

})(jQuery, window);
