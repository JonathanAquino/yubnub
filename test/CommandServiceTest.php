<?php

require_once 'app/helpers/CommandService.php';

class CommandServiceTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->commandService = new TestCommandService();
        $this->commandStore = $this->getMock('CommandStore', array('findCommand'), array(), '', false);

    }

    public function testPrefixWithHttp_DoesNothing_IfInputBeginsWithHttp() {
        $this->assertSame('http://yahoo.com',
                $this->commandService->prefixWithHttpIfNecessary('http://yahoo.com'));
    }

    public function testPrefixWithHttp_DoesNothing_IfInputBeginsWithHttps() {
        $this->assertSame('https://yahoo.com',
                $this->commandService->prefixWithHttpIfNecessary('https://yahoo.com'));
    }

    public function testPrefixWithHttp_DoesNothing_IfInputBeginsWithCurlyBrackets() {
        $this->assertSame('{y test}',
                $this->commandService->prefixWithHttpIfNecessary('{y test}'));
    }

    public function testPrefixWithHttp_AddsHttp_IfInputDoesNotBeginWithHttp() {
        $this->assertSame('http://yahoo.com',
                $this->commandService->prefixWithHttpIfNecessary('yahoo.com'));
    }

    public function testSurroundWithUrlCommand_DoesNothing_IfInputBeginsWithHttp() {
        $this->assertSame('http://yahoo.com',
                $this->commandService->surroundWithUrlCommandIfNecessary('http://yahoo.com', $this->commandStore));
    }

    public function testSurroundWithUrlCommand_DoesNothing_IfInputBeginsWithHttps() {
        $this->assertSame('https://yahoo.com',
                $this->commandService->surroundWithUrlCommandIfNecessary('https://yahoo.com', $this->commandStore));
    }

    public function testSurroundWithUrlCommand_DoesNothing_IfInputBeginsWithCurlyBrackets() {
        $this->assertSame('{y test}',
                $this->commandService->surroundWithUrlCommandIfNecessary('{y test}', $this->commandStore));
    }

    public function testSurroundWithUrlCommand_DoesNothing_IfFirstWordNotCommand() {
        $this->commandStore->expects($this->once())->method('findCommand')
                ->with($this->equalTo('www.yahoo.com'))
                ->will($this->returnValue(null));
        $this->assertSame('www.yahoo.com',
                $this->commandService->surroundWithUrlCommandIfNecessary('www.yahoo.com', $this->commandStore));
    }

    public function testSurroundWithUrlCommand_SurroundsWithUrlCommand_IfFirstWordIsCommand() {
        $this->commandStore->expects($this->once())->method('findCommand')
                ->with($this->equalTo('foo'))
                ->will($this->returnValue('[Command]'));
        $this->assertSame('{url[no url encoding] foo bar}',
                $this->commandService->surroundWithUrlCommandIfNecessary('foo bar', $this->commandStore));
    }

    public function testDropFirstWord_DropsFirstWord() {
        $this->assertEquals('world, Jon!',
                $this->commandService->dropFirstWord('Hello, world, Jon!'));
    }

    public function testDropFirstWord_ReturnsEmptyString_IfOnlyOneWord() {
        $this->assertEquals('',
                $this->commandService->dropFirstWord('Hello!'));
    }

    public function testDropFirstWord_ReturnsEmptyString_IfNoWords() {
        $this->assertEquals('',
                $this->commandService->dropFirstWord(''));
    }

    public function testDropFirstWord_TrimsInput() {
        $this->assertEquals('world, Jon!',
                $this->commandService->dropFirstWord(' Hello, world, Jon! '));
    }

}

class TestCommandService extends CommandService {
    public function dropFirstWord($s) {
        return parent::dropFirstWord($s);
    }
}
