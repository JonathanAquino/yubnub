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
        $defaultCommand = isset($_GET['default']) ? trim($_GET['default']) : 'g';
        if (strlen($command) == 0) {
            $this->redirectTo('/');
        }
        // According to the access log, Yahoo Pipes seems to be bringing the site down. [Jon Aquino 2009-07-03]
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Yahoo Pipes') > -1) {
            header('HTTP/1.0 403 Forbidden');
            echo 'YubNub is currently blocking Yahoo Pipes. Contact jonathan.aquino@gmail.com for more info.';
            exit;
        }
        $parser = new Parser(new CommandStore($this->pdoSingleton->getPdo()));
        $url = $parser->parse($command, $defaultCommand);
        if (strpos($url, '[post]') !== false) {
            $url = str_replace('[post]', '', $url);
            $this->render('get2post', array(
                'url' => new Url($url)
            ));
        }
        $this->redirectTo($url);
    }

    /**
     * Outputs the URL that the given command resolves to.
     */
    public function action_url() {
        $command = isset($_GET['command']) ? trim($_GET['command']) : null;
        $parser = new Parser(new CommandStore($this->pdoSingleton->getPdo()));
        header('Content-Type: text/plain');
        echo $parser->parse($command, null);
    }

    /**
     * Outputs 'success' if Yubnub seems to be healthy.
     */
    public function action_uptime() {
        $commandStore = new CommandStore($this->pdoSingleton->getPdo());
        $commands = $commandStore->findCommands(array('start' => 0, 'count' => 1,
                'orderBy' => 'creation_date DESC'));
        if (count($commands) == 1) {
            header('Content-Type: text/plain');
            echo 'success';
        }
    }

}
