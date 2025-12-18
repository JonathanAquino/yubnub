<?php
/**
 * Configuration variables for the Docker development environment.
 *
 * To use this config, copy it to MyConfig.php:
 *   cp config/DockerConfig.php config/MyConfig.php
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
        return new PDO('mysql:host=mysql;dbname=yubnub;charset=utf8',
                'yubnub',
                'yubnub_dev_password',
                array(
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    }

    /** @implements */
    public function getCaptchaPublicKey() {
        // For local development, CAPTCHA can be disabled or use test keys
        // See https://developers.cloudflare.com/turnstile/troubleshooting/testing/
        return '1x00000000000000000000AA';  // Turnstile test key (always passes)
    }

    /** @implements */
    public function getCaptchaPrivateKey() {
        return '1x0000000000000000000000000000000AA';  // Turnstile test key (always passes)
    }

}
