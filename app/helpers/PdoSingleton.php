<?php

/**
 * Provides access to a single PDO instance that is created only when needed.
 */
class PdoSingleton {

    /** The MySQL database. */
    protected $pdo;

    /** Configuration variables for the current environment. */
    protected $config;

    /**
     * Creates the PDO Singleton.
     *
     * @param Config $config  configuration variables for the current environment
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /** @implements */
    public function getPdo() {
        if (!$this->pdo) {
            $this->pdo = $this->config->createPdo();
        }
        return $this->pdo;
    }

}
