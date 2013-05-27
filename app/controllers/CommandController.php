<?php

/**
 * Dispatches requests pertaining to Yubnub commands.
 */
class CommandController extends Controller {

    /** The Recaptcha public key. */
    const RECAPTCHA_PUBLIC_KEY = '6Ldd_OESAAAAALWIBOnwbLLVPorSYP0nT5aM_V1g';

    /** The Recaptcha private key. */
    const RECAPTCHA_PRIVATE_KEY = '6Ldd_OESAAAAADfHpAwNKubLB4inurAXd1-1zYIt';

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
     *
     * @param string $errorMessage  an error message if the submission failed
     */
    public function action_new($errorMessage = null) {
        require_once SERVER_ROOT . '/lib/Recaptcha/recaptchalib.php';
        $this->render('new', array(
            'pageTitle' => 'Create A New Command',
            'name' => isset($_POST['command']['name']) ? $_POST['command']['name'] : ifseta($_GET, 'name'),
            'url' => isset($_POST['command']['url']) ? $_POST['command']['url'] : null,
            'description' => isset($_POST['command']['description']) ? $_POST['command']['description'] : null,
            'captchaHtml' => recaptcha_get_html(self::RECAPTCHA_PUBLIC_KEY),
            'errorMessage' => $errorMessage,
        ));
    }

    /**
     * Processes the form for creating a new command.
     */
    public function action_add_command() {
        require_once SERVER_ROOT . '/lib/Recaptcha/recaptchalib.php';
        $url = $_POST['command']['url'];
        $commandStore = new CommandStore($this->config->getPdo());
        $commandService = new CommandService();
        $url = $commandService->surroundWithUrlCommandIfNecessary($url, $commandStore);
        $url = $commandService->prefixWithHttpIfNecessary($url);
        if (isset($_POST['test_button'])) {
            $this->redirectTo($commandService->run($url, $_POST['test_command'],
                    new Parser($commandStore)));
            return;
        }
        if (isset($_POST['view_url_button'])) {
            header('Content-type: text/plain');
            echo $commandService->run($url, $_POST['test_command'],
                    new Parser($commandStore));
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectTo('/');
            return;
        }
        $bannedUrlPatternStore = new BannedUrlPatternStore($this->config->getPdo());
        if ($bannedUrlPatternStore->matches($url)) {
            $this->redirectTo('/');
            return;
        }
        $recaptchaResult = recaptcha_check_answer(self::RECAPTCHA_PRIVATE_KEY,
                                $_SERVER['REMOTE_ADDR'],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);
        if (!$recaptchaResult->is_valid) {
            $this->forwardTo('new', array('The reCAPTCHA wasn’t entered correctly. Please try again.'));
            return;
        }
        $command = new Command();
        $command->name = $_POST['command']['name'];
        $command->url = $url;
        $command->description = $_POST['command']['description'];
        $command->creationDate = $commandService->getDate();
        $command->uses = 0;
        $commandStore->save($command);
        $this->redirectTo('/');
    }

}
