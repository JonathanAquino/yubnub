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
        $commandStore = new CommandStore($this->config->getPdo());
        $q = isset($_GET['args']) ? $_GET['args'] : '';
        $this->render('ls', array(
            'pageTitle' => 'Command List (ls)',
            'showGoldenEggLabels' => true,
            'searching' => strlen($q) > 0,
            'commands' => $commandStore->findCommands(array(
                'start' => $start,
                'count' => self::PAGE_SIZE,
                'q' => $q,
                'orderBy' => strlen($q) > 0 ? 'uses DESC' : 'creation_date DESC'))
        ));
    }

}
