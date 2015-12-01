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
        $commandStore = new CommandStore($this->pdoSingleton->getPdo());
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
        $this->render('new', array(
            'pageTitle' => 'Create A New Command',
            'name' => isset($_POST['command']['name']) ? $_POST['command']['name'] : ifseta($_GET, 'name'),
            'url' => isset($_POST['command']['url']) ? $_POST['command']['url'] : null,
            'description' => isset($_POST['command']['description']) ? $_POST['command']['description'] : null,
            'captchaHtml' => '<script src="https://www.google.com/recaptcha/api.js" async defer></script>
                              <div class="g-recaptcha" data-sitekey="' . self::RECAPTCHA_PUBLIC_KEY . '"></div>',
            'errorMessage' => $errorMessage,
        ));
    }

    /**
     * Processes the form for creating a new command.
     */
    public function action_add_command() {
        $url = $_POST['command']['url'];
        $commandStore = new CommandStore($this->pdoSingleton->getPdo());
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
        $bannedUrlPatternStore = new BannedUrlPatternStore($this->pdoSingleton->getPdo());
        if ($bannedUrlPatternStore->matches($url)) {
            $this->redirectTo('/');
            return;
        }
        $recaptchaMatches = $this->recaptchaMatches(self::RECAPTCHA_PRIVATE_KEY,
                                $_SERVER['REMOTE_ADDR'],
                                $_POST['g-recaptcha-response']);
        if (!$recaptchaMatches) {
            $this->forwardTo('new', array('Please click "I am not a robot."'));
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

    /**
     * Returns whether the captcha matches.
     *
     * @param string $privateKey The ReCaptcha private key.
     * @param string $ipAddress  The user's IP address.
     * @param string $recaptchaResponse The value of g-recaptcha-response.
     */
    protected function recaptchaMatches($privateKey, $ipAddress, $recaptchaResponse) {
        $result = $this->post('https://www.google.com/recaptcha/api/siteverify', array(
            'secret' => $privateKey,
            'response' => $recaptchaResponse,
            'remoteip' => $ipAddress
        ));
        $result = json_decode($result, true);
        return $result['success'];
    }

    /**
     * Posts to the given URL.
     *
     * @param string $url  The URL to post to.
     * @param array  $data The key-value pairs to post.
     */
    protected function post ($url, $data) {
        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context  = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }
}
