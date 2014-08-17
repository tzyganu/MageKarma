<?php

namespace Drupal\devel_generate;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;

abstract class DevelGenerateBase extends PluginBase implements DevelGenerateBaseInterface {

  use DependencySerializationTrait;

  /**
   * The plugin settings.
   *
   * @var array
   */
  protected $settings = array();

  /**
   * Implements Drupal\devel_generate\DevelGenerateBaseInterface::getSetting().
   */
  public function getSetting($key) {
    // Merge defaults if we have no value for the key.
    if (!array_key_exists($key, $this->settings)) {
      $this->settings = $this->getDefaultSettings();
    }
    return isset($this->settings[$key]) ? $this->settings[$key] : NULL;
  }

  /**
   * Implements Drupal\devel_generate\DevelGenerateBaseInterface::getDefaultSettings().
   */
  public function getDefaultSettings() {
    $definition = $this->getPluginDefinition();
    return $definition['settings'];
  }

  /**
   * Implements Drupal\devel_generate\DevelGenerateBaseInterface::getSettings().
   */
  public function getSettings() {
    return $this->settings;
  }

  /**
   * Implements Drupal\devel_generate\DevelGenerateBaseInterface::settingsForm().
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return array();
  }

  /**
   * Implements Drupal\devel_generate\DevelGenerateBaseInterface::generate().
   */
  public function generate(array $values) {
    $this->generateElements($values);
    $this->setMessage("Generate process complete.");
  }

  /**
   * Business logic relating with each DevelGenerate plugin
   *
   * @param array $values
   *   The input values from the settings form.
   */
  protected function generateElements(array $values) {

  }

  /**
   * Implements Drupal\devel_generate\DevelGenerateBaseInterface::handleDrushValues().
   */
  public function handleDrushParams($args) {

  }

  /**
   * Set a message for either drush or the web interface.
   *
   * @param $msg
   *  The message to display.
   * @param $type
   *  The message type, as defined by drupal_set_message().
   *
   * @return
   *  Context-appropriate message output.
   */
  protected function setMessage($msg, $type = 'status') {
    $function = function_exists('drush_log') ? 'drush_log' : 'drupal_set_message';
    $function($msg, $type);
  }

  /**
   * Generates a random string
   * @param int $length
   *    the expected word's length
   *
   * @Return Boolean
   */
  public static function generateWord($length) {
    mt_srand((double)microtime()*1000000);

    $vowels = array("a", "e", "i", "o", "u");
    $cons = array("b", "c", "d", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "u", "v", "w", "tr",
      "cr", "br", "fr", "th", "dr", "ch", "ph", "wr", "st", "sp", "sw", "pr", "sl", "cl", "sh");

    $num_vowels = count($vowels);
    $num_cons = count($cons);
    $word = '';

    while(strlen($word) < $length){
      $word .= $cons[mt_rand(0, $num_cons - 1)] . $vowels[mt_rand(0, $num_vowels - 1)];
    }

    return substr($word, 0, $length);
  }

  /**
   * Check if a given param is a number
   * @Return Boolean
   */
  public static function isNumber($number) {
    if ($number == NULL) return FALSE;
    if (!is_numeric($number)) return FALSE;
    return TRUE;
  }

  public static function createGreeking($word_count, $title = FALSE) {
    $dictionary = array("abbas", "abdo", "abico", "abigo", "abluo", "accumsan",
      "acsi", "ad", "adipiscing", "aliquam", "aliquip", "amet", "antehabeo",
      "appellatio", "aptent", "at", "augue", "autem", "bene", "blandit",
      "brevitas", "caecus", "camur", "capto", "causa", "cogo", "comis",
      "commodo", "commoveo", "consectetuer", "consequat", "conventio", "cui",
      "damnum", "decet", "defui", "diam", "dignissim", "distineo", "dolor",
      "dolore", "dolus", "duis", "ea", "eligo", "elit", "enim", "erat",
      "eros", "esca", "esse", "et", "eu", "euismod", "eum", "ex", "exerci",
      "exputo", "facilisi", "facilisis", "fere", "feugiat", "gemino",
      "genitus", "gilvus", "gravis", "haero", "hendrerit", "hos", "huic",
      "humo", "iaceo", "ibidem", "ideo", "ille", "illum", "immitto",
      "importunus", "imputo", "in", "incassum", "inhibeo", "interdico",
      "iriure", "iusto", "iustum", "jugis", "jumentum", "jus", "laoreet",
      "lenis", "letalis", "lobortis", "loquor", "lucidus", "luctus", "ludus",
      "luptatum", "macto", "magna", "mauris", "melior", "metuo", "meus",
      "minim", "modo", "molior", "mos", "natu", "neo", "neque", "nibh",
      "nimis", "nisl", "nobis", "nostrud", "nulla", "nunc", "nutus", "obruo",
      "occuro", "odio", "olim", "oppeto", "os", "pagus", "pala", "paratus",
      "patria", "paulatim", "pecus", "persto", "pertineo", "plaga", "pneum",
      "populus", "praemitto", "praesent", "premo", "probo", "proprius",
      "quadrum", "quae", "qui", "quia", "quibus", "quidem", "quidne", "quis",
      "ratis", "refero", "refoveo", "roto", "rusticus", "saepius",
      "sagaciter", "saluto", "scisco", "secundum", "sed", "si", "similis",
      "singularis", "sino", "sit", "sudo", "suscipere", "suscipit", "tamen",
      "tation", "te", "tego", "tincidunt", "torqueo", "tum", "turpis",
      "typicus", "ulciscor", "ullamcorper", "usitas", "ut", "utinam",
      "utrum", "uxor", "valde", "valetudo", "validus", "vel", "velit",
      "veniam", "venio", "vereor", "vero", "verto", "vicis", "vindico",
      "virtus", "voco", "volutpat", "vulpes", "vulputate", "wisi", "ymo",
      "zelus");
    $dictionary_flipped = array_flip($dictionary);

    $greeking = '';

    if (!$title) {
      $words_remaining = $word_count;
      while ($words_remaining > 0) {
        $sentence_length = mt_rand(3, 10);
        $words = array_rand($dictionary_flipped, $sentence_length);
        $sentence = implode(' ', $words);
        $greeking .= ucfirst($sentence) . '. ';
        $words_remaining -= $sentence_length;
      }
    }
    else {
      // Use slightly different method for titles.
      $words = array_rand($dictionary_flipped, $word_count);
      $words = is_array($words) ? implode(' ', $words) : $words;
      $greeking = ucwords($words);
    }

    // Work around possible php garbage collection bug. Without an unset(), this
    // function gets very expensive over many calls (php 5.2.11).
    unset($dictionary, $dictionary_flipped);
    return trim($greeking);
  }

  public static function createContent($type = NULL) {
    $nparas = mt_rand(1,12);
    $type = empty($type) ? mt_rand(0,3) : $type;

    $output = "";
    switch($type % 3) {
      // MW: This appears undesireable. Was giving <p> in text fields
      // case 1: // html
      //       for ($i = 1; $i <= $nparas; $i++) {
      //         $output .= devel_create_para(mt_rand(10,60),1);
      //       }
      //       break;
      //
      //     case 2: // brs only
      //       for ($i = 1; $i <= $nparas; $i++) {
      //         $output .= devel_create_para(mt_rand(10,60),2);
      //       }
      //       break;

      default: // plain text
        for ($i = 1; $i <= $nparas; $i++) {
          $output .= static::createPara(mt_rand(10,60)) ."\n\n";
        }
    }

    return $output;
  }

public static function createPara($words, $type = 0) {
    $output = '';
    switch ($type) {
      case 1:
        $output .= "<p>" . static::createGreeking($words)  . "</p>";
        break;

      case 2:
        $output .= static::createGreeking($words) . "<br />";
        break;

      default:
        $output .= static::createGreeking($words);
    }
    return $output;
  }
}
