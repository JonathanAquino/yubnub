<?php

require_once 'app/helpers/CommandStore.php';
require_once 'app/models/Command.php';

class CommandStoreTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->commandStore = new TestComandStore(null);
    }

    public function testCreateCommand_ReturnsIso8601Dates() {
        $command = $this->commandStore->createCommand(
                array(
                    'name' => null,
                    'url' => null,
                    'description' => null,
                    'uses' => null,
                    'creation_date' => '2005-02-04 20:39:14',
                    'last_use_date' => null,
                    'golden_egg_date' => null));
        $this->assertSame('2005-02-04 20:39:14',
                $command->creationDate);
    }

}

class TestComandStore extends CommandStore {
    public function createCommand($args) {
        return parent::createCommand($args);
    }
}
