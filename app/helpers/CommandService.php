<?php

/**
 * Toolkit used by the Command Controller.
 */
class CommandService {

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

}
