<?php
/**
 * Configuration variables for the current environment.
 */
class MyConfig implements Config {

    /** @implements */
    public function getErrorReportingLevel() {
        return E_ALL|E_STRICT;
    }

    /** @implements */
    public function shouldDisplayErrors() {
        return true;
    }

    /** @implements */
    public function createPdo() {
        return new PDO('mysql:host=localhost;dbname=yubnub;charset=utf8',
                'yubnub',
                'passwordgoeshere',
                array(
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    }

    /** @implements */
    public function getCaptchaPublicKey() {
        return 'your-turnstile-public-key-here';
    }

    /** @implements */
    public function getCaptchaPrivateKey() {
        return 'your-turnstile-private-key-here';
    }

}

