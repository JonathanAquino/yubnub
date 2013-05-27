<?php

/**
 * Toolkit used by the Command Controller.
 */
class CommandService {

    /**
     * Surrounds the input with {url[no url encoding] ... } if it looks like
     * a command, e.g., gim porsche
     *
     * @param string $url  the URL field as entered by the user, for example,
     *                     http://yahoo.com, yahoo.com, or {y test}
     * @param CommandStore $commandStore  data-access object for Command objects.
     */
    public function surroundWithUrlCommandIfNecessary($url, $commandStore) {
        if (mb_strpos($url, 'http://') === 0) {
            return $url;
        }
        if (mb_strpos($url, 'https://') === 0) {
            return $url;
        }
        if (mb_strpos($url, '{') === 0) {
            return $url;
        }
        $parts = preg_split('/\s+/', $url);
        if (count($parts) > 0 && $commandStore->findCommand($parts[0])) {
            // Do not url-encode the stuff between {}, because it is not a URL.
            // See "rewriting bl, but it insists on transforming characters",
            // http://groups.google.com/group/YubNub/browse_thread/thread/abcf3e5852268d85/fb1896ec6f341003#fb1896ec6f341003  [Jon Aquino 2006-04-01]
            return '{url[no url encoding] ' . $url . '}';
        }
        return $url;
    }

    /**
     * Adds "http://" to the start of the given URL if needed.
     *
     * @param string $url  the URL field as entered by the user, for example,
     *                     http://yahoo.com, yahoo.com, or {y test}
     */
    public function prefixWithHttpIfNecessary($url) {
        if (mb_strpos($url, 'http://') === 0) {
            return $url;
        }
        if (mb_strpos($url, 'https://') === 0) {
            return $url;
        }
        if (mb_strpos($url, '{') === 0) {
            return $url;
        }
        return 'http://' . $url;
    }

    /**
     * Returns the URL resulting from applying a command string to a command
     * definition($url).
     *
     * @param string $url  the command definition, e.g., http://google.com?q=%s
     * @param string $commandString  the command, e.g., g porsche
     * @param Parser $parser  an object that transforms a command into a URL
     * @return string  the resulting URL, e.g., http://google.com?q=porsche
     */
    public function run($url, $commandString, $parser) {
        $command = new Command();
        $command->url = $url;
        $args = $this->dropFirstWord($commandString);
        return $parser->run($command, $args);
    }

    /**
     * Removes the first word from the string.
     *
     * @param string $s  a string, e.g., Hello world
     * @return string  the string with the first word removed, e.g., Hello
     */
    protected function dropFirstWord($s) {
        $s = trim($s);
        $parts = preg_split('/\s+/u', $s, 2);
        return isset($parts[1]) ? $parts[1] : '';
    }

    /**
     * Returns the current date in the format used by the database.
     *
     * @param integer $time  the current time, or null to obtain it automatically
     * @return string  the current date, e.g., 2005-02-04 20:39:14
     */
    public function getDate($time = null) {
        $time = $time ? $time : time();
        return date('Y-m-d H:i:s', $time);
    }

}
