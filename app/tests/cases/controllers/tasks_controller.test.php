<?php
/* Tasks Test cases generated on: 2011-07-13 18:57:27 : 1310597847*/
App::import('Controller', 'Tasks');

class TestTasksController extends TasksController {
	var $autoRender = false;

	function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

class TasksControllerTestCase extends CakeTestCase {
	var $fixtures = array('app.task');

	function startTest() {
		$this->Tasks =& new TestTasksController();
		$this->Tasks->constructClasses();
	}

	function endTest() {
		unset($this->Tasks);
		ClassRegistry::flush();
	}

	function testIndex() {

	}

	function testView() {

	}

	function testAdd() {

	}

	function testEdit() {

	}

	function testDelete() {

	}

}
