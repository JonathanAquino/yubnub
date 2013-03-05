<?php

require_once 'app/helpers/Url.php';

class UrlTest extends PHPUnit_Framework_TestCase {

    public function testToString_ReturnsOriginalUrl() {
        $url = new Url('http://foo.com/?a=1&b=2');
        $this->assertEquals('http://foo.com/?a=1&b=2', $url->toString());
    }

    public function testGetParameters_ReturnsParameters() {
        $url = new Url('http://foo.com/?a=1&b=2');
        $this->assertSame(array(
            array('name' => 'a', 'value' => '1'),
            array('name' => 'b', 'value' => '2')
        ), $url->getParameters());
    }

    public function testGetParameters_ReturnsEmptyArray_IfNoParameters() {
        $url = new Url('http://foo.com/');
        $this->assertSame(array(), $url->getParameters());
    }

    public function testGetParameters_ReturnsEmptyArray_IfUrlMalformed() {
        $url = new Url('!!!');
        $this->assertSame(array(), $url->getParameters());
    }

}
