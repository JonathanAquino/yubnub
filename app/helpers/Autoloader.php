<?php

/**
 * Given a class name, finds and loads its PHP file.
 */
class Autoloader {

    /**
     * Given a class name, finds and loads its PHP file.
     *
     * @param string $className  the name of the class to load, e.g., Foo
     */
    public function load($className) {
        require_once $className . '.php';
    }

}
