<?php
/**
 * House model
 *
 * @package		app
 * @subpackage	app.models
 * @since		0.1
 * @author		Amir Tahvildaran <amirdt22@gmail.com>
 */
class House extends AppModel {

	/**
	 * @var string
	 */
	public $name = 'House';

	/**
	 * @var array
	 */
	public $validate = array(
		'name' => array(
			'required' => true,
			'notEmpty' => array(
				'message' => 'You must provide a name',
				'rule' => 'notEmpty'
			),
			'maxLength' => array(
				'message' => 'Name must be less than 64 characters',
				'rule' => array('maxLength', 64)
 			)
		)
	);

	public $hasAndBelongsToMany = array(
		'User' => array(
			'className' => 'User',
			'with' => 'HousesUser'
		)
	);

}
?>
