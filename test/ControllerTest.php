<?php

use PHPUnit\Framework\TestCase;

require_once 'app/controllers/Controller.php';

class ControllerTest extends TestCase {

    protected function setUp(): void {
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
