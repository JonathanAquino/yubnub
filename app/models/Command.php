<?php

/**
 * A Yubnub command.
 */
class Command {

    /** The maximum length of the excerpt. */
    const EXCERPT_LENGTH = 500;

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

    /**
     * Returns a plain-text excerpt of the description.
     *
     * @return string  an excerpt, suitable for lists of commands
     */
    public function getDescriptionExcerpt() {
        $excerpt = $this->description;
        $excerpt = preg_replace('/DESCRIPTION.*/', '', $excerpt);
        $excerpt = preg_replace('/\s+/s', ' ', $excerpt);
        if (mb_strlen($excerpt) > self::EXCERPT_LENGTH) {
            $excerpt = mb_substr($excerpt, 0, self::EXCERPT_LENGTH - 1) . '…';
        }
        return $excerpt;
    }

    /**
     * Returns the name, rawurlencoded.
     *
     * @return string  the name with rawurlencoding applied
     */
    public function getRawurlencodedName() {
        return rawurlencode($this->name);
    }

    /**
     * Returns the URL as entered by the user.
     *
     * @return string  the URL for display to the user
     */
    public function getDisplayUrl() {
        return preg_replace('/^\{url(?:\[no url encoding\])? (.*)\}$/', '\1', $this->url);
    }

}
