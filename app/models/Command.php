<?php

/**
 * A Yubnub command.
 */
class Command {

    /** The maximum length of the excerpt. */
    const EXCERPT_LENGTH = 500;

    /** The primary key, e.g., 3 */
    public $id;

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

    /**
     * Returns whether the Command defines %s or a switch like -foo
     */
    public function hasArgs() {
        return count($this->getSwitches()) > 1 || preg_match('/%s/', $this->url);
    }

    /**
     * Returns the switches present in the URL.
     *
     * @return array  a map of switch name to default value; example:
     *                {'%s' => null, '-foo' => null, '-bar' => 'baz'}
     */
    public function getSwitches() {
        $switches = array('%s' => null);
        if (preg_match_all('/\$\{([^}]+)\}/', $this->url, $matches)) {
            foreach($matches[1] as $match) {
                $parts = explode('=', $match, 2);
                $switch = '-' . $parts[0];
                $defaultValue = count($parts) > 1 ? $parts[1] : null;
                $switches[$switch] = $defaultValue;
            }
        };
        return $switches;
    }

    /**
     * Substitutes the switches into the URL.
     *
     * @param array  a map of switches; example: {%s => 'foo', 'bar' => 'baz', 'qux' => null}
     */
    public function applySwitches($switches) {
        $url = $this->url;
        $urlencodeValues = true;
        $rawurlencodeValues = false;
        $spaceReplacement = null;
        if (strpos($url, '[no url encoding]') !== false) {
            $url = str_replace('[no url encoding]', '', $url);
            $urlencodeValues = false;
        } elseif (strpos($url, '[use %20 for spaces]') !== false) {
            $url = str_replace('[use %20 for spaces]', '', $url);
            $rawurlencodeValues = true;
            $urlencodeValues = false;
        } elseif (preg_match('/\[use (.{1,4}) for spaces\]/', $url, $matches)) {
            $url = str_replace($matches[0], '', $url);
            $spaceReplacement = $matches[1];
        }
        foreach ($switches as $name => $value) {
            if ($name != '%s') {
                // Remove initial -
                $name = mb_substr($name, 1);
            }
            $originalValue = $value;
            if ($spaceReplacement !== null) {
                $value = str_replace(' ', $spaceReplacement, $value);
            }
            if ($urlencodeValues) {
                $value = urlencode($value);
            } elseif ($rawurlencodeValues) {
                $value = rawurlencode($value);
            }
            // We only want to do urlencoding and space replacement on switches in URLs, not in subcommands.
            // So first we will insert special "SUBCOMMANDSWITCH" markers in front of subcommand switches.
            // We will replace the *unmarked* switches with the urlencoded/space-replaced value.
            // We will then remove the "SUBCOMMANDSWITCH" markers and replace the remaining switches
            // with the original (non-urlencoded, non-space-replaced) value.
            // This idea comes from Strategy 1 in https://stackoverflow.com/a/48727748/2391566

            // Insert SUBCOMMANDSWITCH markers.
            if ($name == '%s') {
                $url = preg_replace('/(\{[^}]*)(%s)/', '${1}SUBCOMMANDSWITCH${2}', $url);
            } else {
                $url = preg_replace('/(\{[^}]*)(\$\{' . preg_quote($name) . '(=.*?)?\})/', '${1}SUBCOMMANDSWITCH${2}', $url);
            }
            // Replace unmarked switches with the urlencoded/space-replaced value.
            if ($name == '%s') {
                $url = preg_replace('/(?<!SUBCOMMANDSWITCH)%s/', $value, $url);
            } else {
                $url = preg_replace('/(?<!SUBCOMMANDSWITCH)\$\{' . preg_quote($name) . '(=.*?)?\}/', $value, $url);
            }
            // Remove SUBCOMMANDSWITCH markers.
            $url = str_replace('SUBCOMMANDSWITCH', '', $url);
            // Replace marked switches with the original, pristine value.
            if ($name == '%s') {
                $url = preg_replace('/%s/', $originalValue, $url);
            } else {
                $url = preg_replace('/\$\{' . preg_quote($name) . '(=.*?)?\}/', $originalValue, $url);
            }
       }
        // Clear unused switches.
        $url = preg_replace('/\$\{.*?\}/', '', $url);
        return $url;
    }

}
