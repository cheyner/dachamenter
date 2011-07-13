<?php
/**
 * User model
 *
 * @package		app
 * @subpackage	app.models
 * @since		0.1
 * @author		Cheyne Rood <cheyne.rood@gmail.com>
 */
class User extends AppModel {

	/**
	 * @var string
	 */
	public $name = 'User';

	/**
	 * @var array
	 */
	public $validate = array(
		'username' => array(
			'notEmpty' => array(
				'message' => 'You must provide a username',
				'rule' => 'notEmpty'
			),
			'maxLength' => array(
				'message' => 'Username must be less than 64 characters',
				'rule' => array('maxLength', 64)
			),
			'isUnique' => array(
				'message' => 'The username you selected is already being used',
				'rule' => array('isUnique')
 			)
		),
		'password' => array(
			'between' => array(
				'message' => 'Password must be between 4 and 64 characters long',
				'rule' => array('between', 4, 64)
			)
		)
	);

	public $hasMany = array(
		'TaskOwned' => array(
			'className' => 'Task',
			'foreignKey' => 'owner_id'
		),
		'Task' => array(
			'className' => 'Task',
			'foreignKey' => 'assignee_id'
		)
	);

	public $hasAndBelongsToMany = array(
		'House' => array(
			'className' => 'House',
			'with' => 'HousesUser'
		)
	);

}
?>
