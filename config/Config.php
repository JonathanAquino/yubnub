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
     * Creates the database object.
     *
     * @return PDO  a connection to the MySQL database
     */
    public function createPdo();

    /**
     * Returns the ReCaptcha/Turnstile public key.
     *
     * @return string  the public site key
     */
    public function getCaptchaPublicKey();

    /**
     * Returns the ReCaptcha/Turnstile private key.
     *
     * @return string  the private secret key
     */
    public function getCaptchaPrivateKey();

}

