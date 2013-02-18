<?php

/**
 * Read-only pages documenting Yubnub.
 */
class DocumentationController extends Controller {

    /**
     * Displays the Yubnub homepage.
     */
    public function action_describe_upcoming_features() {
        $this->render('describe_upcoming_features', array(
            'pageTitle' => 'Upcoming Features'
        ));
    }

}
