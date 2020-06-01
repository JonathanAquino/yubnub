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
        $parser = $this->getMock('TestParser', array('parseProper', 'get'));
        $parser->expects($this->exactly(2))->method('parseProper')
                ->with($this->equalTo('foo bar'))
                ->will($this->returnValue('http://foo.com/'));
        $parser->expects($this->exactly(2))->method('get')
                ->with($this->equalTo('http://foo.com/'))
                ->will($this->returnValue('baz'));
        $this->setExpectedException('Exception');
        $parser->applySubcommands('http://google.com?a={foo bar}&b={foo bar}&c={foo bar}');
    }

    public function testApplySubcommands_DoesNotThrowException_IfNotTooManyCommands() {
        $parser = $this->getMock('TestParser', array('parseProper', 'get'));
        $parser->expects($this->exactly(2))->method('parseProper')
                ->with($this->equalTo('foo bar'))
                ->will($this->returnValue('http://foo.com/'));
        $parser->expects($this->exactly(2))->method('get')
                ->with($this->equalTo('http://foo.com/'))
                ->will($this->returnValue('baz'));
        $expectedUrl = 'http://google.com?a=baz&b=baz';
        $actualUrl = $parser->applySubcommands('http://google.com?a={foo bar}&b={foo bar}');
        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testApplySubcommands_HandlesNestedSubcommands() {
        $parser = $this->getMock('TestParser', array('parseProper', 'get'));
        $parser->expects($this->at(0))->method('parseProper')
                ->with($this->equalTo('bar'))
                ->will($this->returnValue('http://baz.com/'));
        $parser->expects($this->at(1))->method('get')
                ->with($this->equalTo('http://baz.com/'))
                ->will($this->returnValue('baz'));
        $parser->expects($this->at(2))->method('parseProper')
                ->with($this->equalTo('foo baz'))
                ->will($this->returnValue('http://qux.com/'));
        $parser->expects($this->at(3))->method('get')
                ->with($this->equalTo('http://qux.com/'))
                ->will($this->returnValue('qux'));
        $expectedUrl = 'http://google.com?a=qux';
        $actualUrl = $parser->applySubcommands('http://google.com?a={foo {bar}}');
        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testLooksLikeUrl_ReturnsTrue_ForUrl() {
        $this->assertTrue($this->parser->looksLikeUrl('google.com'));
    }

    public function testLooksLikeUrl_ReturnsFalse_ForCommand() {
        $this->assertFalse($this->parser->looksLikeUrl('g porsche'));
    }

    public function testPrefixWithHttp_AddsPrefix_IfNeeded() {
        $this->assertEquals('http://google.com', $this->parser->prefixWithHttp('google.com'));
    }

    public function testPrefixWithHttp_DoesNotAddPrefix_IfNotNeeded() {
        $this->assertEquals('http://google.com', $this->parser->prefixWithHttp('http://google.com'));
    }

    public function testParseSubcommand_AppliesUrlOptimization() {
        $parser = $this->getMock('TestParser', array('parseProper', 'get'));
        $parser->expects($this->once())->method('parseProper')
                ->with($this->equalTo('foo bar'))
                ->will($this->returnValue('http://foo.com/'));
        $parser->expects($this->never())->method('get');
        $this->assertEquals('http://foo.com/', $parser->parseSubcommand(array('{url foo bar}', 'url foo bar')));
    }

    public function testParseSubcommand_DoesNotApplyUrlOptimization() {
        $parser = $this->getMock('TestParser', array('parseProper', 'get'));
        $parser->expects($this->once())->method('parseProper')
                ->with($this->equalTo('foo bar'))
                ->will($this->returnValue('http://foo.com/'));
        $parser->expects($this->once())->method('get')
                ->with($this->equalTo('http://foo.com/'))
                ->will($this->returnValue('baz'));
        $this->assertEquals('baz', $parser->parseSubcommand(array('{foo bar}', 'foo bar')));
    }

    public function testParseSubcommand_ThrowsException_IfResponseBodyExceedsLimit() {
        $parser = $this->getMock('TestParser', array('parseProper', 'get'));
        $parser->expects($this->once())->method('parseProper')
                ->with($this->equalTo('foo bar'))
                ->will($this->returnValue('http://foo.com/'));
        $parser->expects($this->once())->method('get')
                ->with($this->equalTo('http://foo.com/'))
                ->will($this->returnValue(str_repeat('a', 201)));
        $this->setExpectedException('Exception');
        $parser->parseSubcommand(array('{foo bar}', 'foo bar'));
    }

    public function testParseProper_CallsRun_WithFoundCommandWithArgs() {
        $weatherCommand = new Command();
        $weatherCommand->url = 'http://weather.com/?q=%s';
        $commandStore = $this->getMock('TestCommandStore', array('findCommand'));        
        $commandStore->expects($this->once())->method('findCommand')
                ->with($this->equalTo('weather'))
                ->will($this->returnValue($weatherCommand));
        $parser = $this->getMock('TestParser', array('run'));
        $parser->commandStore = $commandStore;
        $parser->expects($this->once())->method('run')
                ->with($this->equalTo($weatherCommand), $this->equalTo('hello world'))
                ->will($this->returnValue(null));
        $parser->parseProper('weather hello world', 'y', $c);
    }   

    public function testParseProper_CallsRun_WithFoundCommandWithoutArgs() {
        $cnnCommand = new Command();
        $cnnCommand->url = 'http://cnn.com';
        $commandStore = $this->getMock('TestCommandStore', array('findCommand'));        
        $commandStore->expects($this->once())->method('findCommand')
                ->with($this->equalTo('cnn'))
                ->will($this->returnValue($cnnCommand));
        $parser = $this->getMock('TestParser', array('run'));
        $parser->commandStore = $commandStore;
        $parser->expects($this->once())->method('run')
                ->with($this->equalTo($cnnCommand), $this->equalTo(''))
                ->will($this->returnValue(null));
        $parser->parseProper('cnn', 'y', $c);
    }       
    
    public function testParseProper_CallsRun_WithDefaultCommand_IfCommandStringHasArgsButCommandDoesNot() {
        $cnnCommand = new Command();
        $cnnCommand->url = 'http://cnn.com';
        $yahooCommand = new Command();
        $yahooCommand->url = 'http://yahoo.com/?q=%s';        
        $commandStore = $this->getMock('TestCommandStore', array('findCommand'));        
        $commandStore->expects($this->at(0))->method('findCommand')
                ->with($this->equalTo('cnn'))
                ->will($this->returnValue($cnnCommand));
        $commandStore->expects($this->at(1))->method('findCommand')
                ->with($this->equalTo('y'))
                ->will($this->returnValue($yahooCommand));                
        $parser = $this->getMock('TestParser', array('run'));
        $parser->commandStore = $commandStore;
        $parser->expects($this->once())->method('run')
                ->with($this->equalTo($yahooCommand), $this->equalTo('cnn hello world'))
                ->will($this->returnValue(null));
        $parser->parseProper('cnn hello world', 'y', $c);
    }       

}

class TestCommandStore extends CommandStore {
    public function __construct() {
    }    
}

class TestParser extends Parser {
    protected $maxCommandCount = 2;
    public $commandStore;
    public function __construct() {
    }
    public function applyArgs($command, $args) {
        return parent::applyArgs($command, $args);
    }
    public function applySubcommands($url) {
        return parent::applySubcommands($url);
    }
    public function looksLikeUrl($commandString) {
        return parent::looksLikeUrl($commandString);
    }
    public function prefixWithHttp($url) {
        return parent::prefixWithHttp($url);
    }
    public function parseSubcommand($matches) {
        return parent::parseSubcommand($matches);
    }
    public function parseProper($commandString, $defaultCommand, &$command = null) {
        return parent::parseProper($commandString, $defaultCommand, $command);
    }
}
