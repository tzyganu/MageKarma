<?php
/**
 * Author: Ted Bowman
 * Date: 10/30/13
 * Time: 12:09 PM
 */

namespace Drupal\entityform;

use Drupal\Core\Config\Entity\ConfigEntityListController;
use Drupal\Core\Entity\EntityInterface;

class EntityformTypeListController extends ConfigEntityListController {
  public function buildHeader() {
    $headers['form'] = $this->t('Form');
    return $headers + parent::buildHeader();
  }

  /**
   * Overrides use Drupal\Core\Config\Entity\ConfigEntityListController::buildRow().
   */
  public function   buildRow(EntityInterface $entity) {
    $row['form'] = $this->getLabel($entity);
    return $row + parent::buildRow($entity);
  }

} 