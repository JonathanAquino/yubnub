<?php

/**
 * Parses commands entered by the user.
 */
class ParserController extends Controller {

    /**
     * Parses a command entered by the user.
     *
     * Expected GET variables:
     *     - command - the command string, e.g., g porsche
     */
    public function action_parse() {
        $command = isset($_GET['command']) ? trim($_GET['command']) : null;
        $defaultCommand = isset($_GET['default']) ? trim($_GET['default']) : null;
        if (strlen($command) == 0) {
            $this->redirectTo('/');
        }
        // According to the access log, Yahoo Pipes seems to be bringing the site down. [Jon Aquino 2009-07-03]
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Yahoo Pipes') > -1) {
            header('HTTP/1.0 403 Forbidden');
            echo 'YubNub is currently blocking Yahoo Pipes. Contact jonathan.aquino@gmail.com for more info.';
            exit;
        }
        $parser = new Parser();
        $this->redirectTo($parser->parse($command, $defaultCommand));
    }

}
