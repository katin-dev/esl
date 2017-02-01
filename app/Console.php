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
}