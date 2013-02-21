<?php
/**
 * Configuration variables for the current environment.
 */
interface Config {

    /**
     * Returns the error-reporting level to use.
     *
     * @return integer  an argument to pass to error_reporting()
     */
    public function getErrorReportingLevel();

    /**
     * Returns whether errors should be output to the browser.
     *
     * @return boolean  whether to display errors
     */
    public function shouldDisplayErrors();

    /**
     * Returns the database object. The Config should keep a single copy of
     * the PDO and return it, instead of creating a new one each time this
     * method is called.
     *
     * @return PDO  a connection to the MySQL database
     */
    public function getPdo();

}

