<?php

namespace Unish;

if (class_exists('Unish\CommandUnishTestCase')) {

    /*
   * @file
   *   PHPUnit Tests for devel. This uses Drush's own test framework, based on PHPUnit.
   *   To run the tests, use run-tests-drush.sh from the devel directory.
   *
   *   @todo It is not ideal to download yourself in your own tests.
   */
  class develCase extends CommandUnishTestCase {

    public function testFnCommands() {
      $sites = $this->setUpDrupal(1, TRUE);
      $options = array(
        'root' => $this->webroot(),
        'uri' => key($sites),
      );
      $this->drush('pm-download', array('devel'), $options + array('cache' => NULL));
      $this->drush('pm-enable', array('devel'), $options + array('skip' => NULL, 'yes' => NULL));

      $this->drush('fn-view', array('drush_main'), $options);
      $output = $this->getOutput();
      $this->assertContains('@return', $output, 'Output contain @return Doxygen.');
      $this->assertContains('function drush_main() {', $output, 'Output contains function drush_main() declaration');

  //    $this->drush('fn-hook', array('cron'), $options);
  //    $output = $this->getOutputAsList();
  //    $expected = array('dblog', 'file', 'field', 'system', 'update');
  //    $this->assertSame($expected, $output);
    }
  }

}
