<?php

/**
 * Given a class name, finds and loads its PHP file.
 */
class Autoloader {

    /** Model class names. */
    protected $models = array('Command');

    /**
     * Given a class name, finds and loads its PHP file.
     *
     * @param string $className  the name of the class to load, e.g., Foo
     */
    public function load($className) {
        if (in_array($className, $this->models)) {
            require_once SERVER_ROOT . '/app/models/' . $className . '.php';
            return;
        }
        if (preg_match('/Controller$/', $className)) {
            require_once SERVER_ROOT . '/app/controllers/' . $className . '.php';
            return;
        }
        require_once SERVER_ROOT . '/app/helpers/' . $className . '.php';
    }

}
