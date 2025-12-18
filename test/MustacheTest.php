<?php

use PHPUnit\Framework\TestCase;

require_once 'lib/Mustache/Autoloader.php';
Mustache_Autoloader::register();

class MustacheTest extends TestCase {

    public function test() {
        $mustacheEngine = new Mustache_Engine(array('strict_callables' => true));
        $strings = array('DateTime', 'getLastErrors');
        $this->assertEquals('DateTimegetLastErrors',
                $mustacheEngine->render('{{#strings}}{{.}}{{/strings}}',
                    array('strings' => $strings)));
    }

}

