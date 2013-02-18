<?php

/**
 * Data-access object for Command objects.
 */
class CommandStore {

    /** The MySQL database. */
    protected $pdo;

    /**
     * Creates the CommandStore.
     *
     * @param PDO $pdo  the MySQL database
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Retrieves a page of Command objects.
     *
     * @param integer $start  the inclusive start index
     * @param integer $count  the number of Commands to retrieve
     * @return array  a two-element array: the Commands, and the total count
     */
    public function findCommands($start, $count) {
        $query = $this->pdo->prepare('SELECT * FROM yubnub.commands LIMIT :start, :count');
        $query->bindValue(':start', $start, PDO::PARAM_INT);
        $query->bindValue(':count', $count, PDO::PARAM_INT);
        $query->execute();
        $commands = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $commands[] = $this->createCommand($row);
        }
        return $commands;
    }

    /**
     * Creates a Command object based on the given arguments.
     *
     * @param array  key-value pairs from the database
     */
    protected function createCommand($args) {
        $command = new Command();
        $command->name = $args['name'];
        $command->url = $args['url'];
        $command->description = $args['description'];
        $command->uses = $args['uses'];
        $command->creationDate = date('c', strtotime($args['creationDate']));
        $command->lastUseDate = date('c', strtotime($args['lastUseDate']));
        $command->goldenEggDate = date('c', strtotime($args['goldenEggDate']));
        return $command;
    }

}
