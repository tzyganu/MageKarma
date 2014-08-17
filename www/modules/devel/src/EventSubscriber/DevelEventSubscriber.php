<?php

/**
 * @file
 * Contains \Drupal\devel\EventSubscriber\DevelEventSubscriber.
 */

namespace Drupal\devel\EventSubscriber;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class DevelEventSubscriber implements EventSubscriberInterface {

  /**
   * The devel.settings config object.
   *
   * @var \Drupal\Core\Config\Config;
   */
  protected $config;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a DevelEventSubscriber object.
   */
  public function __construct(ConfigFactoryInterface $config, AccountInterface $account, ModuleHandlerInterface $module_handler) {
    $this->config = $config->get('devel.settings');
    $this->account = $account;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Initializes devel module requirements.
   */
  public function onRequest(GetResponseEvent $event) {
    if (!devel_silent()) {
      if ($this->config->get('memory')) {
        global $memory_init;
        $memory_init = memory_get_usage();
      }

      if (devel_query_enabled()) {
        Database::startLog('devel');
      }

      if ($this->account->hasPermission('access devel information')) {
        devel_set_handler(devel_get_handlers());
        // We want to include the class early so that anyone may call krumo()
        // as needed. See http://krumo.sourceforge.net/
        has_krumo();

        // See http://www.firephp.org/HQ/Install.htm
        $path = NULL;
        if ((@include_once 'fb.php') || (@include_once 'FirePHPCore/fb.php')) {
          // FirePHPCore is in include_path. Probably a PEAR installation.
          $path = '';
        }
        elseif ($this->moduleHandler->moduleExists('libraries')) {
          // Support Libraries API - http://drupal.org/project/libraries
          $firephp_path = libraries_get_path('FirePHPCore');
          $firephp_path = ($firephp_path ? $firephp_path . '/lib/FirePHPCore/' : '');
          $chromephp_path = libraries_get_path('chromephp');
        }
        else {
          $firephp_path = DRUPAL_ROOT . '/libraries/FirePHPCore/lib/FirePHPCore/';
          $chromephp_path = './' . drupal_get_path('module', 'devel') . '/chromephp';
        }

        // Include FirePHP if it exists.
        if (!empty($firephp_path) && file_exists($firephp_path . 'fb.php')) {
          include_once $firephp_path . 'fb.php';
          include_once $firephp_path . 'FirePHP.class.php';
        }

        // Include ChromePHP if it exists.
        if (!empty($chromephp_path) && file_exists($chromephp_path .= '/ChromePhp.php')) {
          include_once $chromephp_path;
        }

      }
    }

    if ($this->config->get('rebuild_theme_registry')) {
      drupal_theme_rebuild();
      if (\Drupal::service('flood')->isAllowed('devel.rebuild_registry_warning', 1)) {
        \Drupal::service('flood')->register('devel.rebuild_registry_warning');
        if (!devel_silent() && $this->account->hasPermission('access devel information')) {
          drupal_set_message(t('The theme registry is being rebuilt on every request. Remember to <a href="!url">turn off</a> this feature on production websites.', array("!url" => url('admin/config/development/devel'))));
        }
      }
    }

    drupal_register_shutdown_function('devel_shutdown');
  }

  /**
   * Implements EventSubscriberInterface::getSubscribedEvents().
   *
   * @return array
   *   An array of event listener definitions.
   */
  static function getSubscribedEvents() {
    // Set a low value to start as early as possible.
    $events[KernelEvents::REQUEST][] = array('onRequest', -100);

    return $events;
  }

}
