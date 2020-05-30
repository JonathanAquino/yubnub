<?php

require_once 'app/models/Command.php';

class CommandTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->command = new Command();
    }

    public function testGetDescriptionExcerpt_RemovesDescriptionHeading() {
        $this->command->description = 'Hello
DESCRIPTION
Foo';
        $expectedExcerpt = 'Hello Foo';
        $this->assertEquals($expectedExcerpt, $this->command->getDescriptionExcerpt());
    }

    public function testGetDescriptionExcerpt_TruncatesLongDescription() {
        $this->command->description = str_repeat('a', 501);
        $expectedExcerpt = str_repeat('a', 499) . '…';
        $this->assertEquals($expectedExcerpt, $this->command->getDescriptionExcerpt());
    }

    public function testGetDescriptionExcerpt_DoesNotTruncateDescriptionWithMaxLength() {
        $this->command->description = str_repeat('a', 500);
        $expectedExcerpt = str_repeat('a', 500);
        $this->assertEquals($expectedExcerpt, $this->command->getDescriptionExcerpt());
    }

    public function testGetRawurlencodedName() {
        $this->command->name = 'A&W';
        $this->assertEquals('A%26W', $this->command->getRawurlencodedName());
    }

    public function testGetDisplayUrl_HandlesOldFormat() {
        $this->command->url = '{url tiny {url ierdp %s}}';
        $this->assertEquals('tiny {url ierdp %s}', $this->command->getDisplayUrl());
    }

    public function testGetDisplayUrl_HandlesNewFormat() {
        $this->command->url = '{url[no url encoding] gg %s site:nbr.tumblr.com}';
        $this->assertEquals('gg %s site:nbr.tumblr.com', $this->command->getDisplayUrl());
    }

    public function testGetSwitches_ReturnsPercentSwitch_IfNoExplicitSwitches() {
        $this->command->url = 'http://google.com/';
        $this->assertSame(array('%s' => null), $this->command->getSwitches());
    }

    public function testGetSwitches_ReturnsExplicitSwitch() {
        $this->command->url = 'http://google.com/?a=${foo}&b=${bar}';
        $this->assertSame(array('%s' => null, '-foo' => null, '-bar' => null), $this->command->getSwitches());
    }

    public function testGetSwitches_ReturnsDefaultValues() {
        $this->command->url = 'http://google.com/?a=${foo=baz=qux}&b=${bar}';
        $this->assertSame(array('%s' => null, '-foo' => 'baz=qux', '-bar' => null), $this->command->getSwitches());
    }

    public function testApplySwitches() {
        $this->command->url = 'http://google.com/?a=${foo=baz=qux}&b=${bar}&c=%s&d=${hello}';
        $url = $this->command->applySwitches(array('%s' => 'A', '-foo' => 'B', '-bar' => 'C'));
        $this->assertSame('http://google.com/?a=B&b=C&c=A&d=', $url);
    }

    public function testApplySwitches_DoesUrlencoding() {
        $this->command->url = 'http://google.com/?a=%s';
        $url = $this->command->applySwitches(array('%s' => 'A&W'));
        $this->assertSame('http://google.com/?a=A%26W', $url);
    }

    public function testApplySwitches_HeedsNoUrlEncoding() {
        $this->command->url = 'http://google.com/?a=%s[no url encoding]';
        $url = $this->command->applySwitches(array('%s' => 'A&W'));
        $this->assertSame('http://google.com/?a=A&W', $url);
    }

    public function testApplySwitches_DoesMultipleReplacements() {
        $this->command->url = 'http://google.com/?a=%s&b=%s&c=${foo}&d=${foo}';
        $url = $this->command->applySwitches(array('%s' => 'A', '-foo' => 'B'));
        $this->assertSame('http://google.com/?a=A&b=A&c=B&d=B', $url);
    }

    public function testApplySwitches_UsesPlusForSpaces() {
        $this->command->url = 'http://google.com/?a=%s';
        $url = $this->command->applySwitches(array('%s' => 'A B'));
        $this->assertSame('http://google.com/?a=A+B', $url);
    }

    public function testApplySwitches_UsesPercent20ForSpaces() {
        $this->command->url = 'http://google.com/?a=%s[use %20 for spaces]';
        $url = $this->command->applySwitches(array('%s' => 'A B'));
        $this->assertSame('http://google.com/?a=A%20B', $url);
    }

    public function testApplySwitches_UsesXForSpaces() {
        $this->command->url = 'http://google.com/?a=%s[use X for spaces]';
        $url = $this->command->applySwitches(array('%s' => 'A B'));
        $this->assertSame('http://google.com/?a=AXB', $url);
    }

    public function testHasArgs_ReturnsFalse_IfNoArgs() {
        $this->command->url = 'http://google.com/';
        $this->assertFalse($this->command->hasArgs());
    }    

    public function testHasArgs_ReturnsTrue_IfPercentS() {
        $this->command->url = 'http://google.com/?a=%s[use X for spaces]';
        $this->assertTrue($this->command->hasArgs());
    }        
    
    public function testHasArgs_ReturnsTrue_IfSwitches() {
        $this->command->url = 'http://google.com/?a=${foo}&b=${bar}';
        $this->assertTrue($this->command->hasArgs());
    }            

    public function testHasArgs_ReturnsTrue_ForMlpngCommand() {
        $this->command->url = 'http://lista.mercadolivre.com.br/%s_OrderId_PRICE_ItemTypeID_N_G_OrderId_PRICE_U';
        $this->assertTrue($this->command->hasArgs());
    }
}

