<?php

App::import('Component', 'Auth');
App::import('Lib', 'DemographicMask');

/**
 * TransitionalAuthComponent
 *
 * @package     app
 * @subpackage  app.controllers.components
 * @uses        AuthComponent
 * @author      Joshua McNeese <joshua.mcneese@houseparty.com>
 * @since       0.2
 */
class TransitionalAuthComponent extends AuthComponent {

	/**
	 * @var Controller
	 */
	public $controller;

	/**
	 * @var array
	 */
	public $components = array(
		'Cookie',
		'Session',
		'RequestHandler'
	);

	/**
	 * Additional query contain to use when looking up and authenticating users
	 *
	 * @var	array
	 */
	public $userContain = array();

	/**
	 * @var array
	 */
	public $logoutAction = array();

	/**
	 * @var array
	 */
	public $loginConfirmAction = array();

	/**
	 * Settings to allow for email login
	 *
	 * @var	array
	 */
	public $emailLogin = array();

	/**
	 * @var	array
	 */
	public $hashConfig = array(
		'default' => array(
			'algo'	=> 'sha1',
			'salt'	=> true
		),
		'legacy' => array(
			'algo'	=> 'md5',
			'salt'	=> true
		)
	);

	/**
	 * Hash any passwords found in $data using $userModel and $fields['password']
	 *
	 * @author	Joshua McNeese <joshua.mcneese@houseparty.com>
	 * @since	0.2
	 * @param	array	$data Set of data to look for passwords
	 * @return	array	Data with passwords hashed
	 */
	public function hashPasswords($data) {

		if (
			is_object($this->authenticate) &&
			method_exists($this->authenticate, 'hashPasswords')
		) {

			return $this->authenticate->hashPasswords($data);

		}

		$model = $this->getModel();

		if (is_array($data) && isset($data[$model->alias])) {

			if (
				isset($data[$model->alias][$this->fields['username']]) &&
				isset($data[$model->alias][$this->fields['password']])
			) {

                $data[$model->alias][$this->fields['password'].'_plain'] = $data[$model->alias][$this->fields['password']];

				foreach($this->hashConfig as $name=>$config) {

                    $password = $this->password(
                        $data[$model->alias][$this->fields['password']],
                        $config['algo'],
                        $config['salt']
                    );

                    if($name == 'default') {
                        $data[$model->alias][$this->fields['password']] = $password;
                    }

					$data[$model->alias][$this->fields['password'].'_'.$name] = $password;

				}

			}

		}

		return $data;

	}

	/**
	 * Determines if a user has any role, or a particular role
	 *
	 * @author  Anthony Putignano <anthonyp@xonatek.com>
	 * @author	Joshua McNeese <joshua.mcneese@houseparty.com>
	 * @since	0.3
	 * @param	mixed	$id Either blank to determine if a user has *any* role,
	 *						or an id/slug
	 * @return  boolean
	 */
	public function hasRole($id = null) {

		$roles = $this->user('Person.Role');

		if(empty($roles)) {

			return false;

		}

		if(empty($id)) {

			return count($roles) > 0;

		}

		$roles = (Validation::uuid($id) || Validation::numeric($id))
			? Set::extract('/id', $roles)
			: Set::extract('/slug', $roles);

		return in_array($id, $roles);

	}

	/**
	 * Identifies a user based on specific criteria.
	 *
	 * @author	Joshua McNeese <joshua.mcneese@houseparty.com>
	 * @since	0.2
	 * @param	mixed $user Optional. The identity of the user to be validated.
	 *						Uses the current user session if none specified.
	 * @param	array $conditions Optional. Additional conditions to a find.
	 * @return	array User record data, or null if the user couldn't be idented.
	 *
	 */
	public function identify($user = null, $conditions = null) {

		if ($conditions === false) {

			$conditions = null;

		} elseif (is_array($conditions)) {

			$conditions = array_merge(
				(array)$this->userScope,
				$conditions
			);

		} else {

			$conditions = $this->userScope;

		}

		$model = $this->getModel();

		if (empty($user)) {

			$user = $this->user();

			if (empty($user)) {

				return null;

			}

		} elseif (is_object($user) && is_a($user, 'Model')) {

			if (!$user->exists()) {

				return null;

			}

			if(!empty($this->userContain)) {

				$user->contain = $this->userContain;

			}

			$user = $user->read();
			$user = $user[$model->alias];

		} elseif (is_array($user) && isset($user[$model->alias])) {

			$user = $user[$model->alias];

		}

		extract($this->fields, EXTR_OVERWRITE);

		$alias		= $model->alias;
		$is_legacy	= false;

		if (
			is_array($user) &&
			isset($user[$username]) &&
			!empty($user[$username]) &&
			!empty($user[$password])
		) {

			if (
				trim($user[$username]) == '=' ||
				trim($user[$password]) == '='
			) {

				return false;

			}

			$find = array(
				'or' => array()
			);

			// Allow for logging in with email address as well as username, if setup
			if (!empty($this->emailLogin)) {

				$find[] = array(
					'or' => array(
						$this->emailLogin['model'].'.'.$this->emailLogin['field'] => $user[$username],
						$alias.'.'.$username => $user[$username]
					),
				);

			} else { // default to just using username

				$find[$alias.'.'.$username] = $user[$username];

			}

			foreach($this->hashConfig as $name=>$config) {
                if(isset($user[$password.'_'.$name])) {
                    $find['or'][] = array(
                        $alias.'.'.$password => $user[$password.'_'.$name]
                    );
                }
			}

			if (empty($find['or'])) {
				unset($find['or']);
			}

		} else {

			return null;

		}

		$data = $model->find('first', array(
			'contain'	=> $this->userContain,
			'conditions'=> array_merge($find, $conditions)
		));

		if (empty($data) || empty($data[$alias])) {

			return null;

		}

		foreach($this->hashConfig as $name=>$config) {

            if(isset($user[$password.'_'.$name])) {

                ${'is_'.$name} = ($data[$alias][$password] == $user[$password.'_'.$name]);

            }

		}

		$model->id	= $data[$alias][$model->primaryKey];
		$update		= array(
			'logins'            => $data[$alias]['logins']+1,
			'last_login'        => date('Y-m-d H:i:s')
		);

		if (Configure::read('HpLog.enabled')) {
			$visitor    = HpLog::getVisitor();
			$browser    = HpLog::getBrowser();
			$update['last_browser_id'] = $browser['id'];
			$update['last_ip_address'] = $visitor['ip_address'];
		}

		if($is_legacy) {

			$update[$password] = $this->password($user[$password]);

		}

		$updated = $model->save($update, false);

		if(!empty($updated)) {

			$data = Set::merge($data, $updated);

		}

		return $data;

	}

	/**
	 * Initializes AuthComponent for use in the controller
	 *
	 * @author	Joshua McNeese <joshua.mcneese@houseparty.com>
	 * @since	0.3
	 * @param	object $controller A reference to the instantiating controller object
	 * @return	void
	 */
	public function initialize(&$controller, $settings = array()) {

		$this->controller = $controller;

		parent::initialize($controller, $settings);

		Auth::instance($this);

	}

	/**
	 * Manually log-in a user with the given parameter data.  The $data provided can be any data
	 * structure used to identify a user in AuthComponent::identify().  If $data is empty or not
	 * specified, POST data from Controller::$data will be used automatically.
	 *
	 * After (if) login is successful, the user record is written to the session key specified in
	 * AuthComponent::$sessionKey.
	 *
	 * @author	Joshua McNeese <joshua.mcneese@houseparty.com>
	 * @since	0.2
	 * @version	1.0.24
	 * @param	mixed $data User object
	 * @return	boolean True on login success, false on failure
	 */
    public function login($data = null) {

		$this->__setDefaults();
		$this->_loggedIn = false;

		if (empty($data)) {

			$data = (array)$this->data;

		}

        $user = $this->identify($data);

		if ($user) {
			$this->Session->write($this->sessionKey, $user);
			$this->_loggedIn = true;
		}

        $this->Session->write('Auth.provider', 'HouseParty');
		$this->Session->write('Auth.verified', true);

		DemographicMask::getPersonMask(null, false, true);

		return $this->_loggedIn;
	}

	/**
	 * Hash a password
	 *
	 * @author	Joshua McNeese <joshua.mcneese@houseparty.com>
	 * @since	0.2
	 * @param	string	$password Password to hash
	 * @param	mixed	$algo Hashing method to use, null == default
	 * @param	mixed	$salt Salt to use, true == default, false == no salt,
	 *					string == salt to use
	 * @return	string	Hashed password
	 */
	public function password($password, $algo = null, $salt = true) {

		return Security::hash($password, $algo, $salt);

	}

	/**
	 * Main execution method.  Handles redirecting of invalid users, and processing
	 * of login form data.
	 *
	 * @author	Joshua McNeese <joshua.mcneese@houseparty.com>
	 * @since	0.2
	 * @param	object	$controller A reference to the instantiating controller
	 * @return	boolean
	 */
	public function startup(&$controller) {

		$methods		= array_flip($controller->methods);
		$action			= strtolower($controller->params['action']);
		$allowedActions = array_map('strtolower', $this->allowedActions);
		$isErrorOrTests = (
			strtolower($controller->name) == 'cakeerror' || (
				strtolower($controller->name) == 'tests' &&
				Configure::read() > 0
			)
		);

		if ($isErrorOrTests) {

			return true;

		}

		$isMissingAction = (
			$controller->scaffold === false &&
			!isset($methods[$action])
		);

		if ($isMissingAction) {

			return true;

		}

		if (!$this->__setDefaults()) {

			return false;

		}

		$url = '';

		if (isset($controller->params['url']['url'])) {

			$url = $controller->params['url']['url'];

		}

		// check for "remembered" logins
		$cookie = $this->Cookie->read('Auth.User');
		$url = Router::normalize($url);
		$loginAction = Router::normalize($this->loginAction);
		$logoutAction = Router::normalize($this->logoutAction);
		$loginConfirmAction = Router::normalize($this->loginConfirmAction);

		if(
			!$this->user() &&
			!in_array($url, array($loginAction, $logoutAction, $loginConfirmAction)) &&
			!empty($cookie) &&
			$this->login($cookie)
		) {

			// if we are logging in via a cookie, we are not "verified"
			$this->Session->write('Auth.verified', false);

			if($url == $loginAction) {

				$controller->redirect($this->loginRedirect);

			}

			// this is necessary to make sure session is setup properly
			$controller->redirect($controller->here);

			return true;

		}

		$this->data = $controller->data = $this->hashPasswords($controller->data);

		$isAllowed = (
			$this->allowedActions == array('*') ||
			in_array($action, $allowedActions)
		);

		if ($loginAction != $url && $isAllowed) {

			return true;

		}

		if ($loginAction == $url) {

			$model = $this->getModel();

			if (
				empty($controller->data) ||
				!isset($controller->data[$model->alias])
			) {

				if (
					!$this->Session->check('Auth.redirect') &&
					!$this->loginRedirect && env('HTTP_REFERER')
				) {

					$this->Session->write('Auth.redirect', $controller->referer(null, true));

				}

				return false;

			}

			$isValid = (
				!empty($controller->data[$model->alias][$this->fields['username']]) &&
				!empty($controller->data[$model->alias][$this->fields['password']])
			);

			if ($isValid) {

				if ($this->login($controller->data)) {

					if ($this->autoRedirect) {

						$controller->redirect($this->redirect(), null, true);

					}

					return true;

				}

			}

			$this->Session->setFlash(
				$this->loginError,
				$this->flashElement,
				array(),
				'auth'
			);

			$controller->data[$model->alias][$this->fields['password']] = null;

			return false;

		} else {

			if (!$this->user()) {

				if (!$this->RequestHandler->isAjax()) {

					$this->Session->setFlash(
						$this->authError,
						$this->flashElement,
						array(),
						'auth'
					);

					if (
						!empty($controller->params['url']) &&
						count($controller->params['url']) >= 2
					) {

						$query = $controller->params['url'];
						unset($query['url'], $query['ext']);
						$url .= Router::queryString($query, array());

					}

					$this->Session->write('Auth.redirect', $url);

					$controller->redirect($loginAction);

					return false;

				} elseif (!empty($this->ajaxLogin)) {

					$controller->viewPath = 'elements';

					echo $controller->render(
						$this->ajaxLogin,
						$this->RequestHandler->ajaxLayout
					);

					$this->_stop();

					return false;

				} else {

					$controller->redirect(null, 403);

				}

			}

		}

		if (!$this->authorize) {

			return true;

		}

		extract($this->__authType());

		switch ($type) {

			case 'controller':

				$this->object = $controller;

				break;

			case 'crud':
			case 'actions':

				if (isset($controller->Acl)) {

					$this->Acl = $controller->Acl;

				} else {

					trigger_error(__('Could not find AclComponent. Please include Acl in Controller::$components.', true), E_USER_WARNING);

				}

				break;

			case 'model':

				if (!isset($object)) {

					$hasModel	= (
						isset($controller->{$controller->modelClass}) &&
						is_object($controller->{$controller->modelClass})
					);
					$isUses		= (
						!empty($controller->uses) &&
						isset($controller->{$controller->uses[0]}) &&
						is_object($controller->{$controller->uses[0]})
					);

					if ($hasModel) {

						$object = $controller->modelClass;

					} elseif ($isUses) {

						$object = $controller->uses[0];

					}

				}

				$type = array('model' => $object);

				break;

		}

		if ($this->isAuthorized($type)) {

			return true;

		}

		$this->Session->setFlash(
			$this->authError,
			$this->flashElement,
			array(),
			'auth'
		);

		$controller->redirect($controller->referer(), null, true);

		return false;

	}

	/**
	 * Get the current user from the session.
	 *
	 * @author	Joshua McNeese <joshua.mcneese@houseparty.com>
	 * @since	0.2
	 * @param	string	$key field to retrive.  Leave null to get primary model
	 *					record, * to get all data, or a dotted-path to get specifics
	 * @return	mixed	User record. or null if no user is logged in.
	 */
	public function user($key = null) {

		$this->__setDefaults();

		if (!$this->Session->check($this->sessionKey)) {

			return null;

		}

		if(empty($key)) {
			$key = $this->userModel;
		} elseif($key == '*') {
			$key = null;
		}

		return $this->Session->read($this->sessionKey.(!empty($key) ? '.'.$key : ''));

	}

	/**
	 * Get the current user from the session.
	 *
	 * @author	Joshua McNeese <joshua.mcneese@houseparty.com>
	 * @since	1.0.24
	 * @param	string	$key
	 * @param	mixed	$data
	 * @return	boolean
	 */
	public function setUser($key = null, $data = null) {

		if(empty($key) || empty($data)) {

			return false;

		}

		$this->__setDefaults();

		return $this->Session->write($this->sessionKey.(!empty($key) ? '.'.$key : ''), $data);

	}

	/**
	 * Check/set/delete for "remember me" login cookie
	 *
	 * @param 	boolean $keep
	 * @return 	void
	 */
	public function remember($keep = true) {

		$key = 'Auth.User';
        $user = $this->user();

        if ($keep && $user) {

			$model = $this->getModel();

            if (!empty($this->controller->data[$model->alias]['remember'])) {

				$this->Cookie->write($key, $user, true, strtotime('+2 weeks'));

            } else {

				$cookie = $this->Cookie->read($key);

				if(!empty($cookie)) {

					$this->Cookie->delete($key);

				}

            }

        } else {

			$cookie = $this->Cookie->read($key);

			if(!empty($cookie)) {

				$this->Cookie->delete($key);

			}

		}

    }

}

if(!class_exists('Auth')) {

	class Auth {

		public static function instance($setInstance = null) {

			static $instance;

			if ($setInstance) {

				$instance = $setInstance;

			}

			if (!$instance) {

				throw new Exception(
					'AuthComponent not initialized properly!'
				);

			}

			return $instance;

		}

		public static function hasRole($id = null) {

			return self::instance()->hasRole($id);

		}

		public static function setUser($key = null, $data = null) {

			return self::instance()->setUser($key, $data);

		}

		public static function user($key = null) {

			return self::instance()->user($key);

		}

	}

}

?>