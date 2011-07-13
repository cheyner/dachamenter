<?php
class UsersController extends AppController {

	public $name = 'Users';

	/**
	 * @author	Cheyne Rood <cheyne.rood@gmail.com>
	 * @since	0.1
	 * @return	void
	 */
	public function index() {

		$this->set('users', $this->User->find('all'));

	}

	public function view($id = null) {

		$user = $this->User->find('first', array(
			'conditions' => array(
				'User.id' => $id
			),
			'contain' => array(
				'Task'
			)
		));

		$this->set('user', $user);

	}

	function add() {

		if (!empty($this->data)) {
			if ($this->User->save($this->data)) {
				$this->Session->setFlash('user created!');
				$this->redirect(array('action' => 'index'));
			}
		}
		
	}
}
?>
