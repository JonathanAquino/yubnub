<?php

use PHPUnit\Framework\TestCase;

require_once 'app/helpers/Dispatcher.php';

class DispatcherTest extends TestCase {

    protected function setUp(): void {
        $this->dispatcher = new TestDispatcher(null);
    }

    public function testParse_ParsesTwoPartPath() {
        $this->assertSame(array('FooController', 'action_bar'),
                $this->dispatcher->parse('/foo/bar?a=1'));
    }

    public function testParse_ThrowsException_IfCannotParsePath() {
        $this->expectException(Exception::class);
        $this->dispatcher->parse('foo/bar?a=1');
    }
}

class TestDispatcher extends Dispatcher {
    public function parse($url) {
        return parent::parse($url);
    }
}
