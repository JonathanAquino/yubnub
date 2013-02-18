<?php

require_once 'app/helpers/Dispatcher.php';

class DispatcherTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->dispatcher = new TestDispatcher();
    }

    public function testParse_ParsesTwoPartPath() {
        $this->assertSame(array('FooController', 'action_bar'),
                $this->dispatcher->parse('/foo/bar?a=1'));
    }

    public function testParse_ThrowsException_IfCannotParsePath() {
        $this->setExpectedException('Exception');
        $this->dispatcher->parse('foo/bar?a=1');
    }
}

class TestDispatcher extends Dispatcher {
    public function parse($url) {
        return parent::parse($url);
    }
}
