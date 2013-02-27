<?php

require_once 'app/helpers/Parser.php';
require_once 'app/models/Command.php';

class ParserTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->parser = new TestParser();
        $this->command = new Command();
    }

    public function testApplyArgs() {
        $this->command->url = 'http://google.com?a=%s&b=${foo}&c=${bar=baz}&d=${hello=world}';
        $expectedUrl = 'http://google.com?a=111+222&b=333+-joy&c=baz&d=444+555';
        $actualUrl = $this->parser->applyArgs($this->command, '111 222 -foo 333 -joy -hello 444 555');
        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testApplySubcommands_ThrowsException_IfTooManyCommands() {
        $parser = $this->getMock('TestParser', array('parseProper'));
        $parser->expects($this->exactly(2))->method('parseProper')
                ->with($this->equalTo('foo bar'))
                ->will($this->returnValue('baz'));
        $this->setExpectedException('Exception');
        $parser->applySubcommands('http://google.com?a={foo bar}&b={foo bar}&c={foo bar}');
    }

    public function testApplySubcommands_DoesNotThrowException_IfNotTooManyCommands() {
        $parser = $this->getMock('TestParser', array('parseProper'));
        $parser->expects($this->exactly(2))->method('parseProper')
                ->with($this->equalTo('foo bar'))
                ->will($this->returnValue('baz'));
        $expectedUrl = 'http://google.com?a=baz&b=baz';
        $actualUrl = $parser->applySubcommands('http://google.com?a={foo bar}&b={foo bar}');
        $this->assertEquals($expectedUrl, $actualUrl);
    }

}

class TestParser extends Parser {
    protected $maxCommandCount = 2;
    public function applyArgs($command, $args) {
        return parent::applyArgs($command, $args);
    }
    public function applySubcommands($url) {
        return parent::applySubcommands($url);
    }
}
