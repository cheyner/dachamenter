<?php
class HousesController extends AppController {

	var $name = 'Houses';

	function index() {
		$this->House->recursive = 0;
		$this->paginate['House'] = array('limit' => 25);
		$this->set('houses', $this->paginate('House'));
	}

	function view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid house', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->set('house', $this->House->read(null, $id));
	}

	function add() {
		if (!empty($this->data)) {
			$this->House->create();
			if ($this->House->save($this->data)) {
				$this->Session->setFlash(__('The house has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The house could not be saved. Please, try again.', true));
			}
		}
		$users = $this->House->User->find('list',
			array('fields' => array('id', 'username'))
		);
		$this->set(compact('users'));
	}

	function edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid house', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->House->save($this->data)) {
				$this->Session->setFlash(__('The house has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The house could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->House->read(null, $id);
		}
		$users = $this->House->User->find('list');
		$this->set(compact('users'));
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for house', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->House->delete($id)) {
			$this->Session->setFlash(__('House deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('House was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}
}
