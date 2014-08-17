<?php

/**
 * @file
 * Contains \Drupal\devel\Controller\DevelController.
 */

namespace Drupal\devel\Controller;

use Drupal\comment\CommentInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\field\Field;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\user\UserInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns responses for devel module routes.
 */
class DevelController extends ControllerBase {

  /**
   * Clears all caches, then redirects to the previous page.
   */
  public function cacheClear() {
    drupal_flush_all_caches();
    drupal_set_message('Cache cleared.');
    return $this->redirect('<front>');
  }

  /**
   * Returns a list of all currently defined user functions in the current
   * request lifecycle, with links their documentation.
   */
  public function functionReference() {
    $functions = get_defined_functions();
    $version = static::getCoreVersion(\Drupal::VERSION);
    $ufunctions = $functions['user'];
    sort($ufunctions);
    $api = $this->config('devel.settings')->get('api_url');
    $links = array();
    foreach ($ufunctions as $function) {
      $links[] = l($function, "http://$api/api/$version/function/$function");
    }
    return theme('item_list', array('items' => $links));
  }

  public function menuItem() {
    $item = menu_get_item(current_path());
    return kdevel_print_object($item);
  }

  public function nodeLoad(NodeInterface $node) {
    return $this->loadObject('node', $node);
  }

  public function nodeRender(NodeInterface $node) {
    return $this->renderObject('node', $node);
  }

  public function userLoad(UserInterface $user) {
    return $this->loadObject('user', $user);
  }

  public function userRender(UserInterface $user) {
    return $this->renderObject('user', $user);
  }

  public function commentLoad(CommentInterface $comment) {
    return $this->loadObject('comment', $comment);
  }

  public function commentRender(CommentInterface $comment) {
    return $this->renderObject('comment', $comment);
  }

  public function taxonomyTermLoad(TermInterface $taxonomy_term) {
    return $this->loadObject('term', $taxonomy_term);
  }

  public function taxonomyTermRender(TermInterface $taxonomy_term) {

    return $this->renderObject('term', $taxonomy_term);
  }

  public function themeRegistry() {
    drupal_theme_initialize();
    $hooks = theme_get_registry();
    ksort($hooks);
    return kprint_r($hooks, TRUE);
  }

  public function elementsPage() {
    return kdevel_print_object($this->moduleHandler()->invokeAll('element_info'));
  }

  public function fieldInfoPage() {
    $field_info = Field::fieldInfo();
    $info = $field_info->getFields();
    $output = kprint_r($info, TRUE, t('Fields'));

    $info = $field_info->getInstances();
    $output .= kprint_r($info, TRUE, t('Instances'));

    $info = entity_get_bundles();
    $output .= kprint_r($info, TRUE, t('Bundles'));

    $info = \Drupal::service('plugin.manager.field.field_type')->getConfigurableDefinitions();
    $output .= kprint_r($info, TRUE, t('Field types'));

    $info = \Drupal::service('plugin.manager.field.formatter')->getDefinitions();
    $output .= kprint_r($info, TRUE, t('Formatter types'));

    //$info = field_info_storage_types();
    //$output .= kprint_r($info, TRUE, t('Storage types'));

    $info = \Drupal::service('plugin.manager.field.widget')->getDefinitions();
    $output .= kprint_r($info, TRUE, t('Widget types'));
    return $output;
  }

  /**
   * Menu callback for devel/entity/info.
   */
  public function entityInfoPage() {
    $types = $this->entityManager()->getEntityTypeLabels();
    ksort($types);
    $result = array();
    foreach (array_keys($types) as $type) {
      $definition = $this->entityManager()->getDefinition($type);
      $reflected_definition = new \ReflectionClass($definition);
      $props = array();
      foreach ($reflected_definition->getProperties() as $property) {
        $property->setAccessible(TRUE);
        $value = $property->getValue($definition);
        $props[$property->name] = $value;
      }
      $result[$type] = $props;
    }
    return kprint_r($result, TRUE);
  }

  /**
   * Page callback that lists all the state variables.
   */
  public function stateSystemPage() {
    $page['states'] = array(
      '#type' => 'table',
      '#header' => array(
      'name' => array('data' => t('Name'), 'field' => 'name', 'sort' => 'asc'),
      'value' => array('data' => t('Value'), 'field' => 'value'),
      'edit' => array('data' => t('Operations')),
      ),
      '#empty' => t('No state variables.'),
    );

    // Get all states from the KeyValueStorage and put them in the table.
    foreach ($this->state()->getAll() as $state_name => $state) {
      $page['states'][$state_name] = array(
        'name' => array('#markup' => $state_name),
        // Output value in krumo if necessary with kprint_r.
        'value' => array('#markup' => kprint_r($state, TRUE)),
        'edit' => array(
        '#type' => 'link',
        '#title' => t('Edit'),
        '#href' => 'devel/state/edit/' . $state_name,
        ),
      );
    }

    return $page;
  }

  /**
   * Menu callback: display the session.
   */
  public function session() {
    $output = kprint_r($_SESSION, TRUE);
    $headers = array(t('Session name'), t('Session ID'));
    // @todo don't call theme() directly.
    $output .= theme('table', array('headers' => $headers, 'rows' => array(array(session_name(), session_id()))));
    return $output;
  }

  protected function loadObject($type, $object, $name = NULL) {
    $name = isset($name) ? $name : $type;
    return kdevel_print_object($object, '$' . $name . '->');
  }

  protected function renderObject($type, $object, $name = NULL) {
    $name = isset($name) ? $name : $type;
    $function = $type . '_view';
    $build = $function($object);
    return kdevel_print_object($build, '$' . $name . '->');
  }

  /**
   * Switches to a different user.
   *
   * We don't call session_save_session() because we really want to change users.
   * Usually unsafe!
   *
   * @param string $name
   *   The username to switch to, or NULL to log out.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function switchUser($name = NULL) {
    global $user;
    $module_handler = $this->moduleHandler();

    if ($uid = $this->currentUser()->id()) {
      $module_handler->invokeAll('user_logout', array($user));
    }
    if (isset($name) && $account = user_load_by_name($name)) {
      $old_uid = $uid;
      $user = $account;
      $user->timestamp = time() - 9999;
      if (!$old_uid) {
        // Switch from anonymous to authorized.
        drupal_session_regenerate();
      }
      $module_handler->invokeAll('user_login', array($user));
    }
    elseif ($uid) {
      session_destroy();
    }
    $destination = drupal_get_destination();
    $url = $this->urlGenerator()->generateFromPath($destination['destination'], array('absolute' => TRUE));
    return new RedirectResponse($url);
  }

  /**
   * Returns the core version.
   */
  public static function getCoreVersion($version) {
    $version_parts = explode('.', $version);
    // Map from 4.7.10 -> 4.7
    if ($version_parts[0] < 5) {
      return $version_parts[0] . '.' . $version_parts[1];
    }
    // Map from 5.5 -> 5 or 6.0-beta2 -> 6
    else {
      return $version_parts[0];
    }
  }

  /**
   * Explain query callback called by the AJAX link in the query log.
   */
  function queryLogExplain($request_id = NULL, $qid = NULL) {
    if (!is_numeric($request_id)) {
      throw new AccessDeniedHttpException();
    }

    $path = "temporary://devel_querylog/$request_id.txt";
    $path = file_stream_wrapper_uri_normalize($path);
    $queries = json_decode(file_get_contents($path));
    $query = $queries[$qid];
    $result = db_query('EXPLAIN ' . $query->query, (array)$query->args)->fetchAllAssoc('table');
    $i = 1;
    foreach ($result as $row) {
      $row = (array)$row;
      if ($i == 1) {
        $header = array_keys($row);
      }
      $rows[] = array_values($row);
      $i++;
    }
    // @todo don't call theme() directly.
    $build['explain'] = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    );

    $GLOBALS['devel_shutdown'] = FALSE;
    return new Response(drupal_render($build));
  }

  /**
   * Show query arguments, called by the AJAX link in the query log.
   */
  function queryLogArguments($request_id = NULL, $qid = NULL) {
    if (!is_numeric($request_id)) {
      throw new AccessDeniedHttpException();
    }

    $path = "temporary://devel_querylog/$request_id.txt";
    $path = file_stream_wrapper_uri_normalize($path);
    $queries = json_decode(file_get_contents($path));
    $query = $queries[$qid];
    $conn = \Drupal\Core\Database\Database::getConnection();
    $quoted = array();
    foreach ((array)$query->args as $key => $val) {
      $quoted[$key] = is_null($val) ? 'NULL' : $conn->quote($val);
    }
    $output = strtr($query->query, $quoted);

    $GLOBALS['devel_shutdown'] = FALSE;
    return new Response($output);
  }

}
