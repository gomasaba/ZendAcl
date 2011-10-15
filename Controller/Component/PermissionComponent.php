<?php
/**
 * INI based Simple Permissions Component for CakePHP 2.0
 *
 * @author 	ohta takayuki
 * @version	1.0
 * @requires Zend Framework ACL component v.1.11
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * ALL Role Default / Allow
 *
 * ALL Rsourse Denied
 *
 *
 * Automatic Current URI Resource Attachement
 *
 * eg). request['controller']=>'users',request['action']=>'login'
 *
 *      resource_id = users_login
 *
 * You should setting role.ini allow
 *
 * But admin Roll ALL Request allow...
 */

App::uses('IniReader', 'Configure');
require_once 'Zend/Acl.php';
class PermissionComponent extends Component  {
/**
 * Ini file name
 *
 * @var array
 */
	const  roleFileName = 'role';
	public $AdminRole ='admin';

	public $acl;
	public $resources;
	public $roles;
	public $debug = 0;


	/**
	 * __construct
	 *
	 * @param ComponentCollection $collection instance for the ComponentCollection
	 * @param array $settings Settings to set to the component
	 * @return void
	 */
	public function __construct(ComponentCollection $collection, $settings  =  array()) {
        $this->controller = $collection->getController();
        parent::__construct($collection, $settings);
    }
	/**
	 * Loads resources, roles and permissions from .ini files, adds resources to Zend ACL
	 *
	 * @return void
	 * @author Richard Milns
	 */
	public function initialize()
	{
		$Reader  = new IniReader(APP.'Config'.DS);
		$permission_resource = $Reader->read(self::roleFileName);

		$this->acl = new Zend_Acl();
		$this->attach_role($permission_resource);
		$this->attach_resourse();
		$this->attach_permission($permission_resource);
	}
	/**
	 * Get Resourse ID
	 *
	 */
	private function getResourse(){
		$id = $this->controller->request->params['controller'].'_'.$this->controller->request->params['action'];
		return $id;
	}


	/**
	 * Attach ACL Role
	 *
	 * @param (array) permissions.ini
	 * @return void
	 */
	private function attach_role($ini_array){
		// Role
		foreach(array_keys($ini_array) as $role) {
			$role = explode(':', $role);
			$this->roles[] = $role[0];
			$roleParents = null;
			if(count($role) > 1) {
				$roleParents = $role[1];
			}
			try {
				$this->acl->addRole(new Zend_Acl_Role($role[0]), $roleParents);
			} catch(Zend_Acl_Exception $e) {
				throw  new CakeException($e->getMessage());
			}
		}
		return;
	}
	/**
	 * Attach ACL Resource
	 */
	private function attach_resourse(){
		try {
			$this->acl->addResource(new Zend_Acl_Resource($this->getResourse()));
		}catch( Zend_Acl_Exception $e) {
			throw new CakeException($e);
		}
	}

	/**
	 * Attach Permission
	 *
	 * @param string $data
	 * @param string $controller
	 * @param string $roleName
	 * @return void
	 * @author Richard Milns
	 */
	private function attach_permission($ini_array)
	{
		foreach($ini_array as $group=>$permissions) {
			$roleData = explode(':', $group);
			$roleName = $roleData[0];
			$roleParents = null;
			if(count($roleData) > 1) {
				unset($roleData[0]);
				$roleParents = $roleData;
			}
			foreach($permissions as $controller=>$actions) {
				foreach($actions as $action=>$decision){
					$resoucename = $controller.'_'.$action;
					if(!$this->acl->has($resoucename)){
						$this->acl->addResource(new Zend_Acl_Resource($resoucename));
					}
					if($decision == 'allow') {
						try {
							$this->acl->allow($roleName, $resoucename);
						}catch( Zend_Acl_Exception $e) {
							throw new CakeException($e);
						}
					}elseif($decision == 'deny'){
						try {
							$this->acl->deny($roleName, $resoucename);
						}catch( Zend_Acl_Exception $e) {
							throw new CakeException($e);
						}
					}
				}
			}
		}
	}

	/**
	 * Called after the Controller::beforeFilter() and before the controller action
	 *
	 * @param Controller $controller Controller with components to startup
	 * @return void
	 * @link http://book.cakephp.org/view/998/MVC-Class-Access-Within-Components
	 */
	public function startup($controller){
		if(!isset($controller->Auth)) {
			throw new CakeException(_('CakePHP Auth Component was not found in your controller'));
		}
		if(!isset($controller->Session)) {
			throw new CakeException(_('CakePHP Session Component was not found in your controller'));
		}
	}

	/**
	 * Main function to query the ACL
	 *
	 */
	public function isAllowed($role='admin')
	{
		if($role == $this->AdminRole){
			return true;
		}
		try {
			$result =  $this->acl->isAllowed($role,$this->getResourse());
		}catch( Zend_Acl_Exception $e) {
			throw new CakeException($e);
		}
		if($result == true) {
			return true;
		} else {
			throw new AccessDeniedException('access denied');
		}
	}



}

/**
 * Represents an HTTP 404 error.
 *
 * @package       Cake.Error
 */
class AccessDeniedException extends CakeException {
/**
 * Constructor
 *
 * @param string $message If no message is given 'Access denieds' will be the message
 * @param string $code Status code, defaults to 404
 */

	protected $_messageTemplate = 'dont permission';
	public function __construct($message = null, $code = 404) {
		if (empty($message)) {
			$message = 'Access Deneied';
		}
		$renderer = Configure::read('Exception.renderer');
		if($renderer === 'ExceptionRenderer'){
			Configure::write('Exception.renderer', 'AccessDeniedRenderer');
		}
		parent::__construct($message, $code);
	}
}

class AccessDeniedRenderer extends ExceptionRenderer {
	public function __construct(Exception $exception) {
		parent::__construct($exception);
	}

	public function AccessDenied(){
		$this->controller->plugin = 'ZendAcl';
		App::path('View', 'ZendAcl');
		$this->_outputMessage('access_denied');
	}

}
