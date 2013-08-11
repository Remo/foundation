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

use Fuel\Foundation\Request\Local as LocalRequest;

use Fuel\Foundation\Response\Html as HtmlResponse;
use Fuel\Foundation\Response\Json as JsonResponse;

use Fuel\Dependency\ServiceProvider;

/**
 * ServicesProvider class
 *
 * Defines the services published by this namespace to the DiC
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class ServicesProvider extends ServiceProvider
{
	/**
	 * @var  array  list of service names provided by this provider
	 */
	public $provides = array(
		'application', 'environment', 'input', 'log',
		'response', 'response.html', 'response.json',
		'request', 'request.local',
	);

	/**
	 * Service provider definitions
	 */
	public function provide()
	{
		// \Fuel\Foundation\Application
		$this->register('application', function ($dic, $appName, $appPath, $namespace, $environment)
		{
			return new Application($appName, $appPath, $namespace, $environment);
		});

		// \Fuel\Foundation\Environment
		$this->register('environment', function ($dic, $app, $environment, $config)
		{
			return new Environment($app, $environment, $config);
		});

		// \Fuel\Foundation\Input
		$this->register('input', function ($dic, $app, array $inputVars = array(), $parent = null)
		{
			return new Input($app, $inputVars, $parent);
		});

		// \Fuel\Foundation\Response\Html
		$this->register('response', function ($dic, $app, $content = '', $status = 200, array $headers = array())
		{
			return new HtmlResponse($app, $content, $status, $headers);
		});

		// \Fuel\Foundation\Response\Html
		$this->register('response.html', function ($dic, $app, $content = '', $status = 200, array $headers = array())
		{
			return new HtmlResponse($app, $content, $status, $headers);
		});

		// \Fuel\Foundation\Response\Json
		$this->register('response.json', function ($dic, $app, $content = '', $status = 200, array $headers = array())
		{
			return new JsonResponse($app, $content, $status, $headers);
		});

		// \Fuel\Foundation\Request\Local
		$this->register('request', function ($dic, $app, $resource = '', $input = null)
		{
			return new LocalRequest($app, $resource, $input);
		});

		// \Fuel\Foundation\Request\Local
		$this->register('request.local', function ($dic, $app, $resource = '', $input = null)
		{
			return new LocalRequest($app, $resource, $input);
		});

		/**
		 * Service definitions for required non-Fuel classes
		 */

		// \Monolog\Logger
		$this->register('log', function ($dic, $name)
		{
			return new \Monolog\Logger($name);
		});
	}
}
