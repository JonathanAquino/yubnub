<?php

use PHPUnit\Framework\TestCase;

require_once 'app/helpers/functions.php';
require_once 'app/helpers/Parser.php';
require_once 'app/models/Command.php';

class ParserTest extends TestCase {

    protected function setUp(): void {
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
        $parser = $this->getMockBuilder(TestParser::class)
                ->onlyMethods(['parseProper', 'get'])
                ->getMock();
        $parser->expects($this->exactly(2))->method('parseProper')
                ->with($this->equalTo('foo bar'))
                ->willReturn('http://foo.com/');
        $parser->expects($this->exactly(2))->method('get')
                ->with($this->equalTo('http://foo.com/'))
                ->willReturn('baz');
        $this->expectException(Exception::class);
        $parser->applySubcommands('http://google.com?a={foo bar}&b={foo bar}&c={foo bar}');
    }

    public function testApplySubcommands_DoesNotThrowException_IfNotTooManyCommands() {
        $parser = $this->getMockBuilder(TestParser::class)
                ->onlyMethods(['parseProper', 'get'])
                ->getMock();
        $parser->expects($this->exactly(2))->method('parseProper')
                ->with($this->equalTo('foo bar'))
                ->willReturn('http://foo.com/');
        $parser->expects($this->exactly(2))->method('get')
                ->with($this->equalTo('http://foo.com/'))
                ->willReturn('baz');
        $expectedUrl = 'http://google.com?a=baz&b=baz';
        $actualUrl = $parser->applySubcommands('http://google.com?a={foo bar}&b={foo bar}');
        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testApplySubcommands_HandlesNestedSubcommands() {
        $parser = $this->getMockBuilder(TestParser::class)
                ->onlyMethods(['parseProper', 'get'])
                ->getMock();
        $parser->expects($this->exactly(2))->method('parseProper')
                ->willReturnOnConsecutiveCalls('http://baz.com/', 'http://qux.com/');
        $parser->expects($this->exactly(2))->method('get')
                ->willReturnOnConsecutiveCalls('baz', 'qux');
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
        $parser = $this->getMockBuilder(TestParser::class)
                ->onlyMethods(['parseProper', 'get'])
                ->getMock();
        $parser->expects($this->once())->method('parseProper')
                ->with($this->equalTo('foo bar'))
                ->willReturn('http://foo.com/');
        $parser->expects($this->never())->method('get');
        $this->assertEquals('http://foo.com/', $parser->parseSubcommand(array('{url foo bar}', 'url foo bar')));
    }

    public function testParseSubcommand_DoesNotApplyUrlOptimization() {
        $parser = $this->getMockBuilder(TestParser::class)
                ->onlyMethods(['parseProper', 'get'])
                ->getMock();
        $parser->expects($this->once())->method('parseProper')
                ->with($this->equalTo('foo bar'))
                ->willReturn('http://foo.com/');
        $parser->expects($this->once())->method('get')
                ->with($this->equalTo('http://foo.com/'))
                ->willReturn('baz');
        $this->assertEquals('baz', $parser->parseSubcommand(array('{foo bar}', 'foo bar')));
    }

    public function testParseSubcommand_ThrowsException_IfResponseBodyExceedsLimit() {
        $parser = $this->getMockBuilder(TestParser::class)
                ->onlyMethods(['parseProper', 'get'])
                ->getMock();
        $parser->expects($this->once())->method('parseProper')
                ->with($this->equalTo('foo bar'))
                ->willReturn('http://foo.com/');
        $parser->expects($this->once())->method('get')
                ->with($this->equalTo('http://foo.com/'))
                ->willReturn(str_repeat('a', 10001));  // MAX_SUBCOMMAND_RESPONSE_SIZE is 10000
        $this->expectException(Exception::class);
        $parser->parseSubcommand(array('{foo bar}', 'foo bar'));
    }

    public function testParseProper_CallsRun_WithFoundCommandWithArgs() {
        $weatherCommand = new Command();
        $weatherCommand->url = 'http://weather.com/?q=%s';
        $commandStore = $this->getMockBuilder(TestCommandStore::class)
                ->onlyMethods(['findCommand'])
                ->getMock();
        $commandStore->expects($this->once())->method('findCommand')
                ->with($this->equalTo('weather'))
                ->willReturn($weatherCommand);
        $parser = $this->getMockBuilder(TestParser::class)
                ->onlyMethods(['run'])
                ->getMock();
        $parser->commandStore = $commandStore;
        $parser->expects($this->once())->method('run')
                ->with($this->equalTo($weatherCommand), $this->equalTo('hello world'))
                ->willReturn(null);
        $parser->parseProper('weather hello world', 'y', $c);
    }

    public function testParseProper_CallsRun_WithFoundCommandWithoutArgs() {
        $cnnCommand = new Command();
        $cnnCommand->url = 'http://cnn.com';
        $commandStore = $this->getMockBuilder(TestCommandStore::class)
                ->onlyMethods(['findCommand'])
                ->getMock();
        $commandStore->expects($this->once())->method('findCommand')
                ->with($this->equalTo('cnn'))
                ->willReturn($cnnCommand);
        $parser = $this->getMockBuilder(TestParser::class)
                ->onlyMethods(['run'])
                ->getMock();
        $parser->commandStore = $commandStore;
        $parser->expects($this->once())->method('run')
                ->with($this->equalTo($cnnCommand), $this->equalTo(''))
                ->willReturn(null);
        $parser->parseProper('cnn', 'y', $c);
    }

    public function testParseProper_CallsRun_WithDefaultCommand_IfCommandStringHasArgsButCommandDoesNot() {
        $cnnCommand = new Command();
        $cnnCommand->url = 'http://cnn.com';
        $yahooCommand = new Command();
        $yahooCommand->url = 'http://yahoo.com/?q=%s';
        $commandStore = $this->getMockBuilder(TestCommandStore::class)
                ->onlyMethods(['findCommand'])
                ->getMock();
        $commandStore->expects($this->exactly(2))->method('findCommand')
                ->willReturnOnConsecutiveCalls($cnnCommand, $yahooCommand);
        $parser = $this->getMockBuilder(TestParser::class)
                ->onlyMethods(['run'])
                ->getMock();
        $parser->commandStore = $commandStore;
        $parser->expects($this->once())->method('run')
                ->with($this->equalTo($yahooCommand), $this->equalTo('cnn hello world'))
                ->willReturn(null);
        $parser->parseProper('cnn hello world', 'y', $c);
    }

    public function testLooksLikeUrl_RecognizesAboutUrls() {
        $this->assertTrue($this->parser->looksLikeUrl('about:downloads'));
        $this->assertTrue($this->parser->looksLikeUrl('about:config'));
        $this->assertTrue($this->parser->looksLikeUrl('about:blank'));
    }

    public function testLooksLikeUrl_RecognizesFtpUrls() {
        $this->assertTrue($this->parser->looksLikeUrl('ftp://example.com'));
        $this->assertTrue($this->parser->looksLikeUrl('ftps://secure.example.com'));
    }

    public function testLooksLikeUrl_RecognizesIrcUrls() {
        $this->assertTrue($this->parser->looksLikeUrl('irc://irc.freenode.net'));
    }

    public function testLooksLikeUrl_DoesNotRecognizeCommandsWithColons() {
        $this->assertFalse($this->parser->looksLikeUrl('foo:bar'));
        $this->assertFalse($this->parser->looksLikeUrl('my:command'));
        $this->assertFalse($this->parser->looksLikeUrl('test:123'));
    }

    public function testPrefixWithHttp_LeavesAboutUrlsUnchanged() {
        $this->assertEquals('about:downloads', $this->parser->prefixWithHttp('about:downloads'));
        $this->assertEquals('about:config', $this->parser->prefixWithHttp('about:config'));
    }

    public function testPrefixWithHttp_LeavesProtocolUrlsUnchanged() {
        $this->assertEquals('http://example.com', $this->parser->prefixWithHttp('http://example.com'));
        $this->assertEquals('https://example.com', $this->parser->prefixWithHttp('https://example.com'));
        $this->assertEquals('ftp://example.com', $this->parser->prefixWithHttp('ftp://example.com'));
        $this->assertEquals('irc://irc.example.com', $this->parser->prefixWithHttp('irc://irc.example.com'));
    }

    public function testPrefixWithHttp_AddsHttpToPlainDomains() {
        $this->assertEquals('http://example.com', $this->parser->prefixWithHttp('example.com'));
        $this->assertEquals('http://google.com/search', $this->parser->prefixWithHttp('google.com/search'));
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
