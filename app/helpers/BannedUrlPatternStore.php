<?php

/**
 * Data-access object for the banned_url_patterns table.
 */
class BannedUrlPatternStore {

    /** The MySQL database. */
    protected $pdo;

    /**
     * Creates the CommandStore.
     *
     * @param PDO $pdo  the MySQL database
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Returns whether the URL matches any banned URL patterns.
     *
     * @param string $url  the URL to look up
     * @return boolean  whether the URL is banned
     */
    public function matches($url) {
        $query = $this->pdo->prepare('SELECT * FROM yubnub.banned_url_patterns WHERE :url LIKE pattern LIMIT 1');
        $query->bindValue(':url', $url, PDO::PARAM_STR);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

}
