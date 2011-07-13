<?php

/**
 * AppModel
 *
 * @package app
 * @since   0.1
 * @author	Cheyne Rood <cheyne.rood@gmail.com>
 */
class AppModel extends Model {

	public $actsAs = array('Containable');

	//TODO: Set global recursive to avoid extraneous joins.  All joins should be explicit.
	//public $recursive = -1;

}

?>