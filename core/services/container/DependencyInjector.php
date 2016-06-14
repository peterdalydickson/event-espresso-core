<?php
namespace EventEspresso\core\services\container;

use ReflectionClass;
use ReflectionMethod;
use UnexpectedValueException;

if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
	exit( 'No direct script access allowed' );
}



/**
 * Class DependencyInjector
 * Responsible for recursively resolving and injecting dependencies
 * into the arguments array passed to a class upon instantiation.
 * Caches all Reflection objects generated so that this work isn't duplicated.
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * @since         4.9.1
 */
class DependencyInjector
{

	/**
	 * @var CoffeePotInterface $coffee_pot
	 */
	private $coffee_pot;

	/**
	 * @var \ReflectionClass[] $reflectors
	 */
	private $reflectors;

	/**
	 * @var \ReflectionMethod[] $constructors
	 */
	private $constructors;

	/**
	 * @var \ReflectionParameter[] $parameters
	 */
	private $parameters;


	/**
	 * DependencyInjector constructor
	 *
	 * @param CoffeePotInterface $coffee_pot
	 */
	public function __construct( CoffeePotInterface $coffee_pot )
	{
		$this->coffee_pot = $coffee_pot;
	}



	/**
	 * getReflectionClass
	 * checks if a ReflectionClass object has already been generated for a class
	 * and returns that instead of creating a new one
	 *
	 * @access public
	 * @param string $class_name
	 * @return \ReflectionClass
	 */
	public function getReflectionClass( $class_name )
	{
		if (
			! isset( $this->reflectors[ $class_name ] )
			|| ! $this->reflectors[ $class_name ] instanceof \ReflectionClass
		) {
			$this->reflectors[ $class_name ] = new \ReflectionClass( $class_name );
		}
		return $this->reflectors[ $class_name ];
	}



	/**
	 * getConstructor
	 * checks if a ReflectionMethod object has already been generated for the class constructor
	 * and returns that instead of creating a new one
	 *
	 * @access protected
	 * @param ReflectionClass $reflector
	 * @return \ReflectionMethod
	 */
	protected function getConstructor( ReflectionClass $reflector )
	{
		if (
			! isset( $this->constructors[ $reflector->getName() ] )
			|| ! $this->constructors[ $reflector->getName() ] instanceof \ReflectionMethod
		) {
			$this->constructors[ $reflector->getName() ] = $reflector->getConstructor();
		}
		return $this->constructors[ $reflector->getName() ];
	}



	/**
	 * getParameters
	 * checks if an array of ReflectionParameter objects has already been generated for the class constructor
	 * and returns that instead of creating a new one
	 *
	 * @access protected
	 * @param ReflectionMethod $constructor
	 * @return \ReflectionParameter[]
	 */
	protected function getParameters( ReflectionMethod $constructor )
	{
		if ( ! isset( $this->parameters[ $constructor->class ] ) ) {
			$this->parameters[ $constructor->class ] = $constructor->getParameters();
		}
		return $this->parameters[ $constructor->class ];
	}



	/**
	 * resolveDependencies
	 * examines the constructor for the requested class to determine
	 * if any dependencies exist, and if they can be injected.
	 * If so, then those classes will be added to the array of arguments passed to the constructor
	 * PLZ NOTE: this is achieved by type hinting the constructor params
	 * For example:
	 *        if attempting to load a class "Foo" with the following constructor:
	 *        __construct( Bar $bar_class, Fighter $grohl_class )
	 *        then $bar_class and $grohl_class will be added to the $arguments array,
	 *        but only IF they are NOT already present in the incoming arguments array,
	 *        and the correct classes can be loaded
	 *
	 * @access public
	 * @param ReflectionClass $reflector
	 * @param array           $arguments
	 * @return array
	 */
	public function resolveDependencies( ReflectionClass $reflector, $arguments = array() )
	{
		$resolved_parameters = array();
		// let's examine the constructor
		// let's examine the constructor
		$constructor = $this->getConstructor( $reflector );
		// whu? huh? nothing?
		if ( ! $constructor ) {
			return $arguments;
		}
		// get constructor parameters
		$params = $this->getParameters( $constructor );
		if ( empty( $params ) ) {
			return $resolved_parameters;
		}
		// and the keys for the incoming arguments array so that we can compare existing arguments with what is expected
		$argument_keys = array_keys( $arguments );
		// now loop thru all of the constructors expected parameters
		foreach ( $params as $index => $param ) {
			if ( ! $param instanceof \ReflectionParameter ) {
				continue;
			}
			// is this a dependency for a specific class ?
			$param_class = $param->getClass() ? $param->getClass()->name : null;
			if (
				// param is not even a class
				empty( $param_class )
				// and something already exists in the incoming arguments for this param
				&& isset( $argument_keys[ $index ], $arguments[ $argument_keys[ $index ] ] )
			) {
				// add parameter from incoming arguments
				$resolved_parameters[ $index ] = $arguments[ $argument_keys[ $index ] ];
			} else if (
				// parameter is type hinted as a class, exists as an incoming argument, AND it's the correct class
				! empty( $param_class )
				&& isset( $argument_keys[ $index ], $arguments[ $argument_keys[ $index ] ] )
				&& $arguments[ $argument_keys[ $index ] ] instanceof $param_class
			) {
				// add parameter from incoming arguments
				$resolved_parameters[ $index ] = $arguments[ $argument_keys[ $index ] ];
			} else if (
				// parameter is type hinted as a class, and should be injected
				! empty( $param_class )
			) {
				// attempt to inject the dependency
				$dependency = $this->coffee_pot->brew( $param_class, $arguments );
				if ( ! $dependency === $param_class ) {
					throw new UnexpectedValueException(
						sprintf(
							__(
								'Could not resolve dependency for "%1$s" for the "%2$s" class constructor.',
								'event_espresso'
							),
							$param_class
						)
					);
				}
				$resolved_parameters[ $index ] = $dependency;
			} else if ( $param->isOptional() ) {
				$resolved_parameters[ $index ] = $param->getDefaultValue();
			} else {
				$resolved_parameters[ $index ] = null;
			}
		}
		return $resolved_parameters;
	}



}
// End of file DependencyInjector.php
// Location: /DependencyInjector.php