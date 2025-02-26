<?php
/**
 * File delegated to register all the hooks for the plugin.
 *
 * @package    SimpleForm Akismet
 * @subpackage SimpleForm Akismet/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with all actions and filters for the plugin.
 */
class SimpleForm_Akismet_Loader {

	/**
	 *
	 * The array of actions registered with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    mixed[]  $actions The actions registered with WordPress to fire when the plugin loads.
	 */

	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    mixed[]  $filters The filters registered with WordPress to fire when the plugin loads.
	 */

	protected $filters;

	/**
	 * Initialize the collections used to maintain the actions, filters and shortcodes.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->actions = array();
		$this->filters = array();
	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook          The name of the WordPress action that is being registered.
	 * @param object $component     A reference to the instance of the object on which the action is defined.
	 * @param string $callback      The name of the function definition on the $component.
	 * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.
	 * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 *
	 * @return void
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook          The name of the WordPress filter that is being registered.
	 * @param object $component     A reference to the instance of the object on which the filter is defined.
	 * @param string $callback      The name of the function definition on the $component.
	 * @param int    $priority      Optional. The priority at which the function should be fired. Default is 10.
	 * @param int    $accepted_args Optional. The number of arguments that should be passed to the $callback. Default is 1.
	 *
	 * @return void
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single collection.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param  mixed[] $hooks         The collection of hooks that is being registered (that is, actions, filters or shortcodes).
	 * @param  string  $hook          The name of the WordPress filter that is being registered.
	 * @param  object  $component     A reference to the instance of the object on which the filter is defined.
	 * @param  string  $callback      The name of the function definition on the $component.
	 * @param  int     $priority      The priority at which the function should be fired.
	 * @param  int     $accepted_args The number of arguments that should be passed to the $callback.
	 *
	 * @return mixed[] The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $hooks;
	}

	/**
	 * Register the actions, filters and shortcodes with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run() {

		foreach ( $this->filters as $hook ) {

			// @phpstan-ignore-next-line
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			// @phpstan-ignore-next-line
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}
	}
}
