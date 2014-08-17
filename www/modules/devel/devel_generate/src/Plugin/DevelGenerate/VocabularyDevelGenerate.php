<?php

/**
 * @file
 * Contains \Drupal\devel_generate\Plugin\DevelGenerate\VocabularyDevelGenerate.
 */

namespace Drupal\devel_generate\Plugin\DevelGenerate;

use Drupal\devel_generate\DevelGenerateBase;
use Drupal\Core\Language\Language;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a VocabularyDevelGenerate plugin.
 *
 * @DevelGenerate(
 *   id = "vocabulary",
 *   label = @Translation("vocabularies"),
 *   description = @Translation("Generate a given number of vocabularies. Optionally delete current vocabularies."),
 *   url = "vocabs",
 *   permission = "administer devel_generate",
 *   settings = {
 *     "num" = 1,
 *     "title_length" = 12,
 *     "kill" = FALSE
 *   }
 * )
 */
class VocabularyDevelGenerate extends DevelGenerateBase {

  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['num'] = array(
      '#type' => 'textfield',
      '#title' => t('Number of vocabularies?'),
      '#default_value' => $this->getSetting('num'),
      '#size' => 10,
    );
    $form['title_length'] = array(
      '#type' => 'textfield',
      '#title' => t('Maximum number of characters in vocabulary names'),
      '#default_value' => $this->getSetting('title_length'),
      '#size' => 10,
    );
    $form['kill'] = array(
      '#type' => 'checkbox',
      '#title' => t('Delete existing vocabularies before generating new ones.'),
      '#default_value' => $this->getSetting('kill'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function generateElements(array $values) {

    if ($values['kill']) {
      $this->deleteVocabularies();
      $this->setMessage(t('Deleted existing vocabularies.'));
    }
    $new_vocs = $this->generateVocabs($values['num'], $values['title_length']);
    if (!empty($new_vocs)) {
      $this->setMessage(t('Created the following new vocabularies: !vocs', array('!vocs' => implode(', ', $new_vocs))));
    }
  }

  /**
   * Deletes all vocabularies.
   */
  protected function deleteVocabularies() {
    foreach (entity_load_multiple('taxonomy_vocabulary') as $vocabulary) {
      $vocabulary->delete();
    }
  }

  function generateVocabs($records, $maxlength = 12) {
    $vocs = array();

    // Insert new data:
    for ($i = 1; $i <= $records; $i++) {
      $name = $this->generateWord(mt_rand(2, $maxlength));
      $vocabulary = entity_create('taxonomy_vocabulary', array(
        'name' => $name,
        'vid' => drupal_strtolower($name),
        'langcode' => Language::LANGCODE_NOT_SPECIFIED,
        'description' => "description of $name",
        'hierarchy' => 1,
        'weight' => mt_rand(0, 10),
        'multiple' => 1,
        'required' => 0,
        'relations' => 1,
      ));
      $vocabulary->save();
      $vocs[] = $vocabulary->name;

      unset($vocabulary);
    }
    return $vocs;
  }

  public function validateDrushParams($args) {
    $values = array(
      'num' => array_shift($args),
      'kill' => drush_get_option('kill'),
      'title_length' => 12,
    );

    if ($this->isNumber($values['num']) == FALSE) {
      return drush_set_error('DEVEL_GENERATE_INVALID_INPUT', dt('Invalid number of vocabularies: !num.', array('!num' => $values['num'])));
    }
    return $values;
  }

}
