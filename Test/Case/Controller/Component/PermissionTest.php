<?php
App::uses('Controller', 'Controller');
App::uses('Permission', 'ZendAcl.Controller/Component');
	var_dump(APP);exit;


class TestController extends Controller{
	public $name = 'TestController';

	public $components = array('Session','Auth','ZendAcl.Permission');

	/**
     * redirect
     *
     * @param $url, $status = null, $exit = true
     * @return
     */
    public function redirect($url, $status = null, $exit = true){
        $this->redirectUrl = $url;
    }
}



class PermissionComponentTest extends CakeTestCase  {
/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Controller = new TestController();
		$this->Controller->constructClasses();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Controller);
		ClassRegistry::flush();

		parent::tearDown();
	}

	/**
	 * @test
	 */
	public function generate_resouce_id(){
//		var_dump(APP);
		//var_dump($this->Controller);

//		$this->Controller->startupProcess();
	}



}