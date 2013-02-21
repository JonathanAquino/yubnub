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

    public function testGetDisplayUrl_HandlesAlternativeFormat1() {
        $this->command->url = '{url tiny {url ierdp %s}}';
        $this->assertEquals('tiny {url ierdp %s}', $this->command->getDisplayUrl());
    }

    public function testGetDisplayUrl_HandlesAlternativeFormat2() {
        $this->command->url = '{url[no url encoding] gg %s site:nbr.tumblr.com}';
        $this->assertEquals('gg %s site:nbr.tumblr.com', $this->command->getDisplayUrl());
    }

}

