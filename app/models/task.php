<?php
/**
 * Task model
 *
 * @package		app
 * @subpackage	app.models
 * @since		0.1
 * @author		Cheyne Rood <cheyne.rood@gmail.com>
 */
class Task extends AppModel {

	/**
	 * @var string
	 */
	public $name = 'Task';

	/**
	 * @var array
	 */
	public $validate = array(
		'title' => array(
			'required' => true,
			'notEmpty' => array(
				'message' => 'You must provide a title',
				'rule' => 'notEmpty'
			),
			'maxLength' => array(
				'message' => 'Title must be less than 128 characters',
				'rule' => array('maxLength', 128)
			)
		)
	);

}
?>
