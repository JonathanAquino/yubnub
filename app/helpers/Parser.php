<?php

/**
 * Transforms a command into a URL to redirect to.
 */
class Parser {

    /**
     * Returns a URL corresponding to the given command.
     *
     * @param string $commandString  the command plus arguments, e.g., gim porsche
     * @param string $defaultCommand  command to use if the first word is not
     *                                a recognized command
     */
    public function parse($commandString, $defaultCommand) {
        // Great idea from Michele Trimarchi: if the user types in something that looks like
        // a URL, just go to it. [Jon Aquino 2005-06-23]
        if ($this->looksLikeUrl($commandString)) {
            return $this->prefixWithHttp($commandString);
        }
        $parts = preg_split('/\s+/', $commandString);
        $name = $parts[0];
        $args = implode(' ', array_slice($parts, 1));
        $commandStore = new CommandStore();
        $command = $commandStore->findCommand($name);
        if (!$command) {
            $command = $commandStore->findCommand($defaultCommand);
            $args = $commandString;
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

}
