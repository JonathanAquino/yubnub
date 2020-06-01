<?php

/**
 * Transforms a command into a URL.
 */
class Parser {

    /** Max number of bytes allowed for the response body of a subcommand. */
    const MAX_SUBCOMMAND_RESPONSE_SIZE = 200;

    /**
     * The number of commands executed. A command can contain one or more
     * subcommands, each of which can contain subcommands of their own.
     * We limit the total number of commands executed in a command request.
     */
    protected $commandCount;

    /**
     * The maximum number of commands that can be triggered in a request.
     */
    protected $maxCommandCount = 10;

    /** * Data-access object for Command objects. */
    protected $commandStore;

    /**
     * Creates the Parser.
     *
     * @param CommandStore $commandStore  data-access object for Command objects
     */
    public function __construct($commandStore) {
        $this->commandStore = $commandStore;
    }

    /**
     * Returns a URL corresponding to the given command, performing any
     * required initialization.
     *
     * @param string $commandString  the command plus arguments, e.g., gim porsche
     * @param string $defaultCommand  command to use if the first word is not
     *                                a recognized command
     * @return string  the resulting URL
     */
    public function parse($commandString, $defaultCommand) {
        $commandString = trim($commandString);
        // Great idea from Michele Trimarchi: if the user types in something that looks like
        // a URL, just go to it. [Jon Aquino 2005-06-23]
        if ($this->looksLikeUrl($commandString)) {
            return $this->prefixWithHttp($commandString);
        }
        $this->commandCount = 0;
        $url = $this->parseProper($commandString, $defaultCommand, $command);
        $commandService = new CommandService();
        $command->uses++;
        $command->lastUseDate = $commandService->getDate();
        $this->commandStore->save($command);
        if (ifseta($_GET, 'debug')) {
            echo 'Result: ' . $url;
            exit;
        } 
       return $url;
    }

    /**
     * Returns whether the given command appears to be a URL.
     *
     * @param string $commandString  the command entered by the user
     * @return boolean  whether the command looks like a URL
     */
    protected function looksLikeUrl($commandString) {
        return preg_match('/^[^ ]+\.[a-z]{2,4}(\/[^ ]*)?$/', $commandString) ? true : false;
    }

    /**
     * Prefixes the given URL with http:// if needed.
     *
     * @param string $url  a URL or partial URL, such as google.com
     * @return string  the URL with http:// prefixed: http://google.com
     */
    protected function prefixWithHttp($url) {
        if (mb_strpos($url, '://') == false) {
            return 'http://' . $url;
        }
        return $url;
    }

    /**
     * Returns a URL corresponding to the given command or subcommand.
     *
     * @param string $commandString  the command plus arguments, e.g., gim porsche
     * @param string $defaultCommand  command to use if the first word is not
     *                                a recognized command
     * @param Command $command  (output) the Command used
     * @return string  the resulting URL
     */
    protected function parseProper($commandString, $defaultCommand, &$command = null) {
        $commandString = $this->applySubcommands($commandString);
        $parts = preg_split('/\s+/', $commandString);
        $name = $parts[0];
        $args = implode(' ', array_slice($parts, 1));
        $command = $this->commandStore->findCommand($name);
        if ($command && !$command->hasArgs() && $args) {
            $command = null;
        }
        if (!$command && !$defaultCommand) {
            throw new Exception('Could not find command ' . $name);
        }
        if (!$command) {
            $command = $this->commandStore->findCommand($defaultCommand);
            $args = $commandString;
        }
        if (ifseta($_GET, 'debug')) {
            header('Content-Type: text/plain');
        }
        return $this->run($command, $args);
    }

    /**
     * Returns a URL corresponding to the given command object, after applying
     * the arguments.
     *
     * @param Command $command  the Command to run
     * @param string $args  the arguments (string of switches) to pass to the command
     * @return string  the resulting URL
     */
    public function run($command, $args) {
        if (ifseta($_GET, 'debug')) {
            echo "--------------------------------------------------------------------------------\n";
            echo 'RUN ' . $command->name . ' WITH ARGS ' . $args . ":\n";
            echo $command->url . "\n";
        }
        $url = $this->applyArgs($command, $args);
        $url = $this->applySubcommands($url);
        return $url;
    }

    /**
     * Applies the arguments to the Command's URL.
     *
     * @param Command $command  the command to run
     * @param string $args  the arguments (string of switches) to pass to the command
     * @return string  the Command's URL with the arguments substituted in
     */
    protected function applyArgs($command, $args) {
        $switches = $command->getSwitches();
        $parts = preg_split('/\s+/', $args);
        $currentSwitch = '%s';
        foreach ($parts as $part) {
            if (array_key_exists($part, $switches)) {
                $currentSwitch = $part;
                $switches[$currentSwitch] = null;
            } else {
                $switches[$currentSwitch] = trim($switches[$currentSwitch] . ' ' . $part);
            }
        }
        return $command->applySwitches($switches);
    }

    /**
     * Expands any subcommands in the URL. For example, in http://foo.com?{random 100},
     * {random 100} will be expanded.
     *
     * @param string $url  a URL which may contain subcommands
     */
    protected function applySubcommands($url) {
        $pattern = '/\{([^{}]+)\}/';
        while (preg_match($pattern, $url)) {
            $url = preg_replace_callback($pattern, array($this, 'parseSubcommand'), $url, 1);
        }
        return $url;
    }

    /**
     * Returns a URL corresponding to the given  subcommand.
     *
     * @param array $matches  regular-expression matches from #applySubcommands
     */
    protected function parseSubcommand($matches) {
        $subcommandString = trim($matches[1]);
        $this->commandCount += 1;
        if ($this->commandCount > $this->maxCommandCount) {
            throw new Exception('Too many subcommands: ' . $subcommandString);
        }
        // Optimization for the url command: just do it inline
        if (preg_match('/^url (.*)/', $subcommandString, $matches2)) {
            return $this->parseProper(trim($matches2[1]), null);
        }
        $url = $this->parseProper($subcommandString, null);
        $response = $this->get($url);
        if (ifseta($_GET, 'debug')) {
            echo "--------------------------------------------------------------------------------\n";
            echo "RESPONSE: \n";
            echo $response . "\n";
        }
        if (strlen($response) > self::MAX_SUBCOMMAND_RESPONSE_SIZE) {
            throw new Exception('Response body size (' . strlen($response) . ') exceeds limit (' . self::MAX_SUBCOMMAND_RESPONSE_SIZE . '): ' . $url);
        }
        return $response;
    }

    /**
     * Returns the response from the given URL.
     *
     * @param string $url  the URL to get
     * @return string  the response body
     */
    protected function get($url) {
        return file_get_contents($url);
    }

}
