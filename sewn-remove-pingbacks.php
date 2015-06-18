<?php

/**
 * @link              https://github.com/jupitercow/sewn-in-remove-pingbacks
 * @since             1.0.2
 * @package           Sewn_Remove_Pingbacks
 *
 * @wordpress-plugin
 * Plugin Name:       Sewn In Remove Pingbacks
 * Plugin URI:        https://wordpress.org/plugins/sewn-in-remove-pingbacks/
 * Description:       Disable pingbacks.
 * Version:           1.0.2
 * Author:            jcow
 * Author URI:        http://Jupitercow.com/
 * Contributer:       ekaj
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$class_name = 'Sewn_Remove_Pingbacks';
if (! class_exists($class_name) ) :

class Sewn_Remove_Pingbacks
{
	/**
	 * Load the plugin.
	 *
	 * @since	1.0.2
	 * @return	void
	 */
	public function run()
	{
		register_activation_hook( __FILE__,               array($this, 'activate') );
		register_deactivation_hook( __FILE__,             array($this, 'deactivate') );

		// remove RSD link
		remove_action( 'wp_head', 'rsd_link' );

		add_action( 'xmlrpc_call',                        array($this, 'disable_xmlrpc') );
		#add_filter( 'xmlrpc_methods',                     array($this, 'remove_xmlrpc_methods') );

		add_filter( 'wp_headers',                         array($this, 'remove_ping_headers'), 10, 1 );
		add_filter( 'rewrite_rules_array',                array($this, 'remove_ping_rewrites') );
		add_filter( 'bloginfo_url',                       array($this, 'remove_ping_pingback_url'), 10, 2 );
		add_filter( 'pre_update_option_enable_xmlrpc',    '__return_false' );
		add_filter( 'pre_option_enable_xmlrpc',           '__return_zero' );
		add_filter( 'mod_rewrite_rules',                  array($this, 'htaccess') );
	}

	/**
	 * Activate the plugin
	 *
	 * @author	ekaj
	 * @since	1.0.1
	 * @return	void
	 */
	public function activate()
	{
		add_filter( 'mod_rewrite_rules', array($this, 'htaccess') );
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin
	 *
	 * @author	ekaj
	 * @since	1.0.1
	 * @return	void
	 */
	public function deactivate()
	{
		remove_filter( 'mod_rewrite_rules', array($this, 'htaccess') );
		flush_rewrite_rules();
	}

	/**
	 * Disable the xmlprc
	 *
	 * @author	ekaj
	 * @since	1.0.0
	 * @param	string	$action	xmlrpc actions
	 * @return	void
	 */
	public function disable_xmlrpc( $action )
	{
		if ( 'pingback.ping' === $action )
		{
			wp_die( 
				'Pingbacks are not supported',
				'Not Allowed!',
				array( 'response' => 403 )
			);
		}
	}

	
	/**
	 * Remove the actual XMLRPC methods
	 *
	 * @author	ekaj
	 * @since	1.0.1
	 * @param	array	$methods	xmlrpc methods
	 * @return	array	The modified methods with pingback removed
	 */
	public function remove_xmlrpc_methods( $methods )
	{
		unset( $methods['pingback.ping'] );
		#unset( $methods['pingback.extensions.getPingbacks'] );
		return $methods;
	}

	/**
	 * DISABLING PINGBACKS AND TRACKBACKS
	 * Intercepts header and rewrites X-Pingback
	 * Does not modify $post['ping_status'] - could read 'open'
	 * Does not modify $default_ping_status - could read 'open'
	 *
	 * @author	ekaj
	 * @since	1.0.0
	 * @param	array	$headers	Array of header items
	 * @return	array	Modified header array with pingbacks removed
	 */
	public function remove_ping_headers( $headers )
	{
		if ( isset($headers['X-Pingback']) ) {
			unset($headers['X-Pingback']);
		}
		return $headers;
	}

	/**
	 * Kill the rewrite rule
	 *
	 * @author	ekaj
	 * @since	1.0.0
	 * @param 	array	$rules	Array of rewrite rules
	 * @return 	array	Modified rewrite rules with pingbacks removed
	 */
	public function remove_ping_rewrites( $rules )
	{
		foreach ( $rules as $rule => $rewrite )
		{
			if ( preg_match( '/trackback\/\?\$$/i', $rule ) ) {
				unset( $rules[$rule] );
			}
		}
		return $rules;
	}

	/**
	 * Kill bloginfo( 'pingback_url' )
	 *
	 * @author	ekaj
	 * @since	1.0.0
	 * @param 	mixed	$output	The URL returned by bloginfo().
	 * @param 	mixed	$show	Type of information requested.
	 * @return 	array	Modified rewrite rules with pingbacks removed
	 */
	public function remove_ping_pingback_url( $output, $show )
	{
		if ( 'pingback_url' == $show ) {
			$output = '';
		}
		return $output;
	}

	/**
	 * Remove access to the xml-rpc file completely at the server level
	 *
	 * @author	ekaj
	 * @since	1.0.0
	 * @param	array	$rules	The current rules
	 * @return	array	Modified rules
	 */
	public function htaccess( $rules )
	{
		$new_rules = "<Files xmlrpc.php>\n\tSatisfy any\n\tOrder allow,deny\n\tDeny from all\n</Files>\n\n";
		return $new_rules . $rules;
	}
}

$$class_name = new $class_name;
$$class_name->run();
unset($class_name);

endif;