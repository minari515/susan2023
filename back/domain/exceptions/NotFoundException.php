<?php

class NotFoundException extends Exception {

  public $info;

  public function __construct($message, $info = null) {
    parent::__construct($message, 404);
    $this->info = $info;
  }

  public function getSource() {
    return $this->info;
  }
}
