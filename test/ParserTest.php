<?php

require_once 'app/helpers/Parser.php';
require_once 'app/models/Command.php';

class ParserTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->parser = new TestParser();
    }

    public function test() {
    }

}

class TestParser extends Parser {
}
