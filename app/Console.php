<?php

namespace App;
use Knp\Command\Command;

class Console extends Command {
  /**
   * @return \Monolog\Logger
   */
  protected function getLogger() {
    return $this->getSilexApplication()['monolog'];
  }

  /**
   * @return \PDO
   */
  protected function getDb() {
    return $this->getSilexApplication()['db'];
  }

  /**
   * @return \App\Esl
   */
  protected function getEsl() {
    return $this->getSilexApplication()['esl'];
  }
}