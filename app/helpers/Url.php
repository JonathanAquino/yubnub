<?php

/**
 * A Url, with a convenient method for accessing its parameters.
 */
class Url {

    /** An absolute URL. */
    protected $url;

    /**
     * Creates the URL object.
     *
     * @param string $url  an absolute URL
     */
    public function __construct($url) {
        $this->url = $url;
    }

    /**
     * Returns the underlying URL.
     *
     * @return string  an absolute URL
     */
    public function toString() {
        return $this->url;
    }

    /**
     * Returns the URL parameters.
     *
     * @return array 'name' and 'value' for each key
     */
    public function getParameters() {
        $queryString = parse_url($this->url, PHP_URL_QUERY);
        if ($queryString === false || strlen($queryString) == 0) {
            return array();
        }
        $parameters = array();
        parse_str($queryString, $parameters);
        $result = array();
        foreach ($parameters as $name => $value) {
            $result[] = array('name' => $name, 'value' => $value);
        }
        return $result;
    }

}
