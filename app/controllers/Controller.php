<?php

/**
 * Dispatches a request.
 */
class Controller {

    /** Configuration variables for the current environment. */
    protected $config;

    /** Provides access to a single PDO instance that is created only when needed. */
    protected $pdoSingleton;

    /**
     * Creates the controller.
     *
     * @param Config $config  configuration variables for the current environment
     */
    public function __construct($config, $pdoSingleton) {
        $this->config = $config;
        $this->pdoSingleton = $pdoSingleton;
    }

    /**
     * Outputs the specified template.
     *
     * @param string $templateName  the template name, e.g., 'show'
     * @param array $args  key-value pairs to pass to the template
     */
    public function render($templateName, $args = array()) {
        $templatePath = SERVER_ROOT . '/app/views/' . $this->getName() . '/' . $templateName . '.mustache';
        $template = file_get_contents($templatePath);
        $options = array();
        $options['partials_loader'] = new Mustache_Loader_FilesystemLoader(SERVER_ROOT);
        $options['strict_callables'] = true;
        $mustacheEngine = new Mustache_Engine($options);
        echo $mustacheEngine->render($template, $args);
        exit;
    }

    /**
     * 302s to the given URL.
     *
     * @param string $url  the relative URL to redirect to
     */
    public function redirectTo($url) {
        if ($url[0] == '/') {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
        }
        header('Location: ' . $url);
        exit;
    }

    /**
     * Executes the specified action.
     *
     * @param string $action  the name of the action, e.g., 'new'
     * @param array $args  parameters to pass to the action
     */
    public function forwardTo($action, $args = array()) {
        call_user_func_array(array($this, "action_$action"), $args);
    }

    /**
     * Returns the name of the controller.
     *
     * @return string  the part of the class name before "Test", with first
     *                 letter lowercased
     */
    protected function getName() {
        return lcfirst(preg_replace('/Controller$/', '', get_class($this)));
    }

}
