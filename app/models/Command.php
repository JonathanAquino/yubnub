<?php

/**
 * A Yubnub command.
 */
class Command {

    /** The command name, e.g., g */
    public $name;

    /**
     * The URL for the command, which may be composed of other commands.
     * Example: http://www.google.com/search?q=%s
     */
    public $url;

    /** Documentation for the command. */
    public $description;

    /** Number of times the command has been run. */
    public $uses;

    /** ISO-8601 date on which the command was created. */
    public $creationDate;

    /** ISO-8601 date on which the command was last run. */
    public $lastUseDate;

    /** ISO-8601 date on which the command was made into a Golden Egg (featured command). */
    public $goldenEggDate;

}
