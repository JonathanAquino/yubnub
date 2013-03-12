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

    /**
     * SYNOPSIS
     *         dnow [YYYY-MMDD]
     *
     * EXAMPLE
     *         dnow 2005-0607
     *         dnow
     *
     * DESCRIPTION
     *         Play the Democracy Now archive from archive.org. Democracy Now
     *         is a daily radio and TV news program on over 300 stations,
     *         pioneering the largest community media collaboration in the US.
     *
     *         Specify a date in YYYY-MMDD format. Or leave out the date
     *         for today's show.
     */
    public function action_dnow() {
        $yyyy_mmdd = isset($_GET['args']) ? $_GET['args'] : '';
        if (!$yyyy_mmdd) {
            $yyyy_mmdd = date('Y-md');
        }
        $this->redirectTo("http://www.archive.org/download/dn{$yyyy_mmdd}/dn{$yyyy_mmdd}_vbr.m3u");
    }

}
