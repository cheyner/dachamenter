<?php
/**
 * AppController
 *
 * @package app
 * @since   0.1
 * @author  Cheyne Rood <cheyne.rood@gmail.com>
 */
class AppController extends Controller {

	/* TODO:
		 -Setup automatic JSON parsing
		 -Establish $this->_response viewVars standard
		 -Auth and isAuthorized
	 */

	public $helpers = array('Html', 'Form', 'Session');

	public $components = array(
		'Session',
		'RequestHandler',
		'Cookie',
		//Referee?
	);

}
?>
