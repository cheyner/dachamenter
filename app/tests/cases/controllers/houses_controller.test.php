<?php
/* Houses Test cases generated on: 2011-07-08 00:26:14 : 1310099174*/
App::import('Controller', 'Houses');

class TestHousesController extends HousesController {
	var $autoRender = false;

	function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

class HousesControllerTestCase extends CakeTestCase {
	var $fixtures = array('app.house', 'app.user', 'app.task', 'app.houses_user');

	function startTest() {
		$this->Houses =& new TestHousesController();
		$this->Houses->constructClasses();
	}

	function endTest() {
		unset($this->Houses);
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
