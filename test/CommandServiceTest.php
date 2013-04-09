<?php

require_once 'app/helpers/CommandService.php';

class CommandServiceTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->commandService = new CommandService();
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

}

