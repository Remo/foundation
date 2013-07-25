<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation;

/**
 * Application Base class
 *
 * Wraps an application package into an object to work with.
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Application
{
	/**
	 * @var  string  name of this application
	 *
	 * @since  2.0.0
	 */
	protected $appName;

	/**
	 * @var  string  application root path
	 *
	 * @since  2.0.0
	 */
	protected $appPath;

	/**
	 * @var  string  base namespace for this application
	 *
	 * @since  2.0.0
	 */
	protected $appNamespace;

	/**
	 * @var  Fuel\Config  this applications config container
	 *
	 * @since  2.0.0
	 */
	protected $config;

	/**
	 * @var  Environment  this applications environment
	 *
	 * @since  2.0.0
	 */
	protected $environment;

	/**
	 * @var  Security  this applications security container
	 *
	 * @since  2.0.0
	 */
	protected $security;

	/**
	 * @var  Router  this applications router object
	 *
	 * @since  2.0.0
	 */
	protected $router;

	/**
	 * @var  Request  contains the app main request object
	 *
	 * @since  2.0.0
	 */
	protected $request;

	/**
	 * @var  array  current active request stack
	 *
	 * @since  2.0.0
	 */
	protected $requests = array();

	/**
	 * @var  Fuel\Display\ViewManager  this applications view manager
	 *
	 * @since  2.0.0
	 */
	protected $view;

	/**
	 * Constructor
	 *
	 * @since  2.0.0
	 */
	public function __construct($appName, $appPath, $namespace, $environment)
	{
		// store the application name
		$this->appName = $appName;

		// and it's base namespace
		$this->appNamespace = $namespace;

		// check if the path is valid, and if so, store it
		if ( ! is_dir($appPath))
		{
			throw new \InvalidArgumentException('Application path "'.$appPath.'" does not exist.');
		}
		$this->appPath = realpath($appPath).DS;

		// and setup the configuration container
		$this->config = \Dependency::resolve('config');
		$this->config->addPath($this->appPath);
		$this->config->setParent(\Config::getInstance());

		// create the environment for this application
		$this->environment = \Dependency::resolve('environment', array($this, $environment, $this->config));

		// create the security container for this application
		$this->security = \Dependency::resolve('security', array($this));

		// create the view manager instance for this application
		$this->view = \Dependency::resolve('view', array(
			\Dependency::resolve('finder', array(
				array($this->appPath),
			)),
			array(
				'cache' => $this->appPath.'cache',
			)
		));

		// and enable the default view parser
		$this->view->registerParser('php', \Dependency::resolve('parser.php'));

		// TODO: create a router object
		$this->router = \Dependency::resolve('Fuel\Foundation\Router', array($this));
	}

	/**
	 * Get a property that is available through a getter
	 *
	 * @param   string  $property
	 * @return  mixed
	 * @throws  \OutOfBoundsException
	 *
	 * @since  2.0.0
	 */
	public function __get($property)
	{
		if (method_exists($this, $method = 'get'.ucfirst($property)))
		{
			return $this->{$method}();
		}

		throw new \OutOfBoundsException('Property "'.$property.'" not available on the application.');
	}

	/**
	 * Returns the applications config object
	 *
	 * @return  Fuel\Config\Datacontainer
	 *
	 * @since  2.0.0
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Returns the applications environment object
	 *
	 * @return  Fuel\Config\Datacontainer
	 *
	 * @since  2.0.0
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}


	/**
	 * Construct an application request
	 *
	 * @param   string  $uri
	 * @param   array|Input  $input
	 *
	 * @return  Request
	 *
	 * @since  2.0.0
	 */
	public function getRequest($uri = null, Array $input = array())
	{
		// if no uri is given, fetch the global one
		$uri === null and $uri = \Input::getInstance()->getPathInfo($this->environment->baseUrl);

		return \Dependency::resolve('request', array($this, $this->security->cleanUri($uri), $input));
	}

	/**
	 * Return the router object
	 *
	 * @return  Router
	 *
	 * @since  2.0.0
	 */
	public function getRouter()
	{
		return $this->router;
	}

	/**
	 * Return the application name
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getName()
	{
		return $this->appName;
	}

	/**
	 * Return the application base namespace
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getNamespace()
	{
		return $this->appNamespace;
	}

	/**
	 * Return the application root path
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getPath()
	{
		return $this->appPath;
	}

	/**
	 * Return the applications View manager
	 *
	 * @return  Fuel\Display\ViewManager
	 *
	 * @since  2.0.0
	 */
	public function getViewManager()
	{
		return $this->view;
	}

	/**
	 * Sets the current active request
	 *
	 * @param   Request  $request
	 *
	 * @return  Application
	 *
	 * @since  2.0.0
	 */
	public function setActiveRequest(Request $request = null)
	{
		$this->requests[] = $request;
		return $this;
	}

	/**
	 * Returns current active Request
	 *
	 * @return  Request
	 *
	 * @since  2.0.0
	 */
	public function getActiveRequest()
	{
		return empty($this->requests) ? null : end($this->requests);
	}

	/**
	 * Resets the current active request
	 *
	 * @return  Application
	 *
	 * @since  2.0.0
	 */
	public function resetActiveRequest()
	{
		if ( ! empty($this->requests))
		{
			array_pop($this->requests);
		}
		return $this;
	}
}
