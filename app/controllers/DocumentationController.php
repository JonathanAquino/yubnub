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

    /**
     * Describes alternative ways to use Yubnub.
     */
    public function action_describe_installation() {
        $this->render('describe_installation', array(
            'pageTitle' => 'Installing Yubnub'
        ));
    }

    /**
     * Documents advanced syntax for creating commands.
     */
    public function action_describe_advanced_syntax() {
        $this->render('describe_advanced_syntax', array(
            'pageTitle' => 'Advanced Syntax for Creating Commands'
        ));
    }

    /**
     * Displays a list of people who were instrumental in the creation of Yubnub.
     */
    public function action_display_acknowledgements() {
        $this->render('display_acknowledgements', array(
            'pageTitle' => 'Acknowledgements'
        ));
    }

    /**
     * Displays a list of Yubnub's best commands, as selected by Jeremy Hussell.
     */
    public function action_jeremys_picks() {
        $this->render('jeremys_picks', array(
            'pageTitle' => 'Jeremy’s Picks'
        ));
    }

    /**
     * Displays a tip jar.
     */
    public function action_tip_jar() {
        $this->render('tip_jar', array(
            'pageTitle' => 'Tip Jar'
        ));
    }

}
