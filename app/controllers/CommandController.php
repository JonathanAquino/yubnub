<?php

/**
 * Dispatches requests pertaining to Yubnub commands.
 */
class CommandController extends Controller {

    /**
     * Returns whether the given command exists.
     *
     * Expected GET parameters:
     *     - name - the name of the command to look for
     */
    public function action_exists() {
        $commandStore = new CommandStore($this->config->getPdo());
        $command = $commandStore->findCommand($_GET['name']);
        $js = json_encode(array('exists' => $command ? true : false));
        header('Content-Type: text/javascript');
        header('X-JSON: ' . $js);
        echo $js;
    }

    /**
     * Displays a form for creating a new command.
     */
    public function action_new() {
        $this->render('new', array(
            'pageTitle' => 'Create A New Command',
            'name' => isset($_GET['name']) ? $_GET['name'] : ''
        ));
    }

    /**
     * Processes the form for creating a new command.
     */
    public function action_add_command() {
        $url = $_POST['command']['url'];
        $commandService = new CommandService();
        $url = $commandService->prefixWithHttpIfNecessary($url);
    }

}
