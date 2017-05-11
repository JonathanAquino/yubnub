<?php

/**
 * Core functionality
 */
class KernelController extends Controller {

    /** Number of commands per page. */
    const PAGE_SIZE = 50;

    /**
     * Displays the Yubnub homepage.
     *
     * Expected GET parameters:
     *     - page - the page number (optional)
     *     - args - search terms (optional)
     */
    public function action_ls() {
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $start = ($page-1) * self::PAGE_SIZE;
        $commandStore = new CommandStore($this->pdoSingleton->getPdo());
        $q = isset($_GET['args']) ? $_GET['args'] : '';
        $extra = 1;
        $commands = $commandStore->findCommands(array(
                'start' => $start,
                'count' => self::PAGE_SIZE + $extra,
                'q' => $q,
                'orderBy' => strlen($q) > 0 ? 'uses DESC' : 'creation_date DESC'));
        $hasNextPage = count($commands) > self::PAGE_SIZE;
        $commands = array_slice($commands, 0, self::PAGE_SIZE - $extra);
        $this->render('ls', array(
            'pageTitle' => 'Command List (ls)',
            'showGoldenEggLabels' => true,
            'searching' => strlen($q) > 0,
            'commands' => $commands,
            'previousPage' => $page > 1 ? $page - 1 : null,
            'nextPage' => $hasNextPage ? $page + 1 : null,
            'previousPageUrl' => $page > 1 ? '?page=' . ($page - 1) . '&args=' . urlencode($q) : null,
            'nextPageUrl' => $hasNextPage ? '?page=' . ($page + 1) . '&args=' . urlencode($q) : null,
        ));
    }

    /**
     * Displays featured commands.
     *
     * Expected GET parameters:
     *     - page - the page number (optional)
     *     - args - search terms (optional)
     */
    public function action_golden_eggs() {
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $start = ($page-1) * self::PAGE_SIZE;
        $commandStore = new CommandStore($this->pdoSingleton->getPdo());
        $q = isset($_GET['args']) ? $_GET['args'] : '';
        $extra = 1;
        $commands = $commandStore->findGoldenEggs(array(
                'start' => $start,
                'count' => self::PAGE_SIZE + $extra,
                'q' => $q,
                'orderBy' => strlen($q) > 0 ? 'uses DESC' : 'golden_egg_date DESC'));
        $hasNextPage = count($commands) > self::PAGE_SIZE;
        $commands = array_slice($commands, 0, self::PAGE_SIZE - $extra);
        $this->render('ls', array(
            'pageTitle' => 'Golden Eggs (ge)',
            'showGoldenEggLabels' => false,
            'searching' => strlen($q) > 0,
            'commands' => $commands,
            'previousPage' => $page > 1 ? $page - 1 : null,
            'nextPage' => $hasNextPage ? $page + 1 : null,
            'previousPageUrl' => $page > 1 ? '?page=' . ($page - 1) . '&args=' . urlencode($q) : null,
            'nextPageUrl' => $hasNextPage ? '?page=' . ($page + 1) . '&args=' . urlencode($q) : null,
        ));
    }

    /**
     * Displays a list of the most popular commands.
     *
     * Expected GET parameters:
     *     - page - the page number (optional)
     */
    public function action_most_used_commands() {
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $start = ($page-1) * self::PAGE_SIZE;
        $commandStore = new CommandStore($this->pdoSingleton->getPdo());
        $extra = 1;
        $commands = $commandStore->findCommands(array(
                'start' => $start,
                'count' => self::PAGE_SIZE + $extra,
                'orderBy' => 'uses DESC'));
        $hasNextPage = count($commands) > self::PAGE_SIZE;
        $commands = array_slice($commands, 0, self::PAGE_SIZE - $extra);
        $this->render('ls', array(
            'pageTitle' => 'The Most-Used Commands',
            'showGoldenEggLabels' => true,
            'searching' => false,
            'commands' => $commands,
            'previousPage' => $page > 1 ? $page - 1 : null,
            'nextPage' => $hasNextPage ? $page + 1 : null,
            'previousPageUrl' => $page > 1 ? '?page=' . ($page - 1) : null,
            'nextPageUrl' => $hasNextPage ? '?page=' . ($page + 1) : null,
        ));
    }

    /**
     * Displays help for the given command.
     *
     * Expected GET variables:
     *     - args - the name of the command; defaults to 'man'
     */
    public function action_man() {
        $name = isset($_GET['args']) ? $_GET['args'] : null;
        if (strlen($name) == 0) {
            $this->redirectTo('/kernel/man?args=man');
        }
        $commandStore = new CommandStore($this->pdoSingleton->getPdo());
        $command = $commandStore->findCommand($name);
        if (!$command) {
            $this->render('no_manual_entry', array(
                'pageTitle' => 'man ' . $name,
                'name' => $name,
                'lsUrl' => '/parser/parse?command=' . rawurlencode('ls ' . $name)
            ));
        }
        $this->render('man', array(
            'pageTitle' => $name,
            'command' => $command,
        ));
    }

}
