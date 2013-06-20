<?php

require_once 'app/controllers/Controller.php';

class ControllerTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->controller = new TestController(null, null);
    }

    public function testGetName() {
        $this->assertSame('test', $this->controller->getName());
    }
}

class TestController extends Controller {
    public function getName() {
        return parent::getName();
    }
}
