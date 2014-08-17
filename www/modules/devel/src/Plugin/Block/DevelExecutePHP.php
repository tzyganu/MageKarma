<?php

/**
 * @file
 * Contains \Drupal\devel\Plugin\Block\DevelExecutePHP.
 */

namespace Drupal\devel\Plugin\Block;

use Drupal\block\BlockBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block for executing PHP code.
 *
 * @Block(
 *   id = "devel_execute_php",
 *   admin_label = @Translation("Execute PHP")
 * )
 */
class DevelExecutePHP extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    return $account->hasPermission('execute php code');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\devel\Form\ExecutePHP');
  }

}
