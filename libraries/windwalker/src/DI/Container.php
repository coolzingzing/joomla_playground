<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\DI;

use Joomla\DI\Container as JoomlaContainer;

/**
 * Windwalker DI Container.
 *
 * Based on Joomla DI Container.
 *
 * @since  2.0
 */
class Container extends JoomlaContainer
{
	/**
	 * Force new instance.
	 *
	 * @const boolean
	 */
	const FORCE_NEW = true;

	/**
	 * The main container instance storage.
	 *
	 * @var Container
	 */
	static protected $instance = null;

	/**
	 * The children containers storage.
	 *
	 * @var array
	 */
	static protected $children = array();

	/**
	 * Get the container instance by name.
	 *
	 * @param   string  $name  Container name, if is null, get the main container.
	 *
	 * @return Container
	 */
	public static function getInstance($name = null)
	{
		if ($name == 'windwalker')
		{
			$name = null;
		}

		// No name, return root container.
		if (!$name)
		{
			if (!(self::$instance instanceof JoomlaContainer))
			{
				self::$instance = new static;
			}

			return self::$instance;
		}

		// Has name, we return children container.
		if (empty(self::$children[$name]) || !(self::$children[$name] instanceof JoomlaContainer))
		{
			self::$children[$name] = new static(self::getInstance());
		}

		return self::$children[$name];
	}

	/**
	 * Method to set the key and callback to the dataStore array.
	 *
	 * @param   string   $key        Name of dataStore key to set.
	 * @param   mixed    $value      Callable function to run or string to retrive when requesting the specified $key.
	 * @param   boolean  $shared     True to create and store a shared instance.
	 * @param   boolean  $protected  True to protect this item from being overwritten. Useful for services.
	 *
	 * @return  Container  This object for chaining.
	 *
	 * @throws  \OutOfBoundsException  Thrown if the provided key is already set and is protected.
	 */
	public function set($key, $value, $shared = false, $protected = false)
	{
		if (isset($this->dataStore[$key]) && $this->dataStore[$key]['protected'] === true)
		{
			throw new \OutOfBoundsException(sprintf('Key %s is protected and can\'t be overwritten.', $key));
		}

		// If the provided $value is not a closure, make it one now for easy resolution.
		if (!is_callable($value) && !($value instanceof \Closure))
		{
			$value = function () use ($value) {
				return $value;
			};
		}

		$this->dataStore[$key] = array(
			'callback' => $value,
			'shared' => $shared,
			'protected' => $protected
		);

		return $this;
	}

	/**
	 * Method to retrieve the results of running the $callback for the specified $key;
	 *
	 * @param   string   $key       Name of the dataStore key to get.
	 * @param   boolean  $forceNew  True to force creation and return of a new instance.
	 *
	 * @throws  \InvalidArgumentException
	 * @return  mixed   Results of running the $callback for the specified $key.
	 */
	public function get($key, $forceNew = false)
	{
		$key = $this->resolveAlias($key);
		$raw = $this->getRaw($key);

		if (is_null($raw))
		{
			throw new \InvalidArgumentException(sprintf('Key %s has not been registered with the container.', $key));
		}

		if ($raw['shared'])
		{
			if (!isset($this->instances[$key]) || $forceNew)
			{
				$this->instances[$key] = call_user_func($raw['callback'], $this);
			}

			return $this->instances[$key];
		}

		return call_user_func($raw['callback'], $this);
	}

	/**
	 * Get the raw data assigned to a key.
	 * Override to prevent joomla-framework bug not resolving parent aliases
	 *
	 * @param   string  $key  The key for which to get the stored item.
	 *
	 * @return  mixed
	 *
	 * @since   2.0.11
	 */
	protected function getRaw($key)
	{
		if (isset($this->dataStore[$key]))
		{
			return $this->dataStore[$key];
		}

		$aliasKey = $this->resolveAlias($key);

		if ($aliasKey != $key && isset($this->dataStore[$aliasKey]))
		{
			return $this->dataStore[$aliasKey];
		}

		if ($this->parent instanceof JoomlaContainer)
		{
			return $this->parent->getRaw($key);
		}

		return null;
	}

	/**
	 * Dump all dataStores for debugging.
	 *
	 * @return  array Data stores and aliases.
	 */
	public function dump()
	{
		$storage = array();

		foreach ($this->instances as $key => $data)
		{
			if (is_object($data))
			{
				$storage[$key] = get_class($data);
			}
		}

		return array(
			'aliases' => $this->aliases,
			'data'    => $storage
		);
	}

	/**
	 * Method to check if specified dataStore key exists.
	 *
	 * A placeholder because older Joomla has no this method.
	 *
	 * @param   string  $key  Name of the dataStore key to check.
	 *
	 * @return  boolean  True for success
	 *
	 * @since   2.1
	 */
	public function exists($key)
	{
		$key = $this->resolveAlias($key);

		return (bool) $this->getRaw($key);
	}

	/**
	 * getParent
	 *
	 * @return  JoomlaContainer
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * setParent
	 *
	 * @param JoomlaContainer $parent
	 *
	 * @return  static
	 */
	public function setParent(JoomlaContainer $parent)
	{
		$this->parent = $parent;

		return $this;
	}
}
