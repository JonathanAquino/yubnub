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
     * Returns the database object.
     *
     * @return PDO  a connection to the MySQL database
     */
    public function getPdo();

}

