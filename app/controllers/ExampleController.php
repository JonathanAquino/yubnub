<?php

/**
 * Implementations of some example commands.
 */
class ExampleController extends Controller {

    /**
     * SYNOPSIS
     *         tr FROM TO TEXT
     *
     * EXAMPLES
     *         tr en fr hello
     *
     * DESCRIPTION
     *         Translates the given text from the first language to the
     *         second language, courtesy of Google Language Tools.
     *         The above example translates "hello" from English (en) to
     *         French (fr). Not all language combinations are available.
     *
     *         Chinese (Simplified) zh-CN
     *         English ............ en
     *         French ............. fr
     *         German ............. de
     *         Italian ............ it
     *         Japanese ........... ja
     *         Korean ............. ko
     *         Portuguese ......... pt
     *         Spanish ............ es
     */
    public function action_tr() {
        $args = preg_split('/\s+/', trim($_GET['args']));
        $this->render('tr', array(
            'from' => $args[0],
            'to' => $args[1],
            'text' => implode(' ', array_slice($args, 2)),
        ));
    }

}
