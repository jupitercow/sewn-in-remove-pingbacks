<?php
/*
Plugin Name: Sewn In Remove WordPress Pingbacks
Plugin URI: https://github.com/jupitercow/sewn-in-remove-pingbacks
Description: Disable pingbacks.
Version: 1.0.0
Author: Jake Snyder
Author URI: http://Jupitercow.com/
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

------------------------------------------------------------------------
Copyright 2014 Jupitercow, Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

if (! class_exists('sewn_remove_pingbacks') ) :

add_action( 'init', array('sewn_remove_pingbacks', 'init') );

class sewn_remove_pingbacks
{
	/**
	 * Initialize the Class
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public static function init()
	{
		// remove RSD link
		remove_action( 'wp_head', 'rsd_link' );

		// actions
		add_action( 'xmlrpc_call',                        array(__CLASS__, 'disable_xmlrpc') );

		// filters
		add_filter( 'wp_headers',                         array(__CLASS__, 'remove_ping_headers'), 10, 1 );
		add_filter( 'rewrite_rules_array',                array(__CLASS__, 'remove_ping_rewrites') );
		add_filter( 'bloginfo_url',                       array(__CLASS__, 'remove_ping_pingback_url'), 10, 2 );
		add_filter( 'pre_update_option_enable_xmlrpc',    '__return_false' );
		add_filter( 'pre_option_enable_xmlrpc',           '__return_zero' );
	}

	/**
	 * Disable XMLRPC call
	 */
	public static function disable_xmlrpc( $action )
	{
		if ( 'pingback.ping' === $action )
		{
			wp_die( 
				'Pingbacks are not supported',
				'Not Allowed!',
				array( 'response' => 403 ),
			);
		}
	}

	/**
	 * DISABLING PINGBACKS AND TRACKBACKS
	 * Intercepts header and rewrites X-Pingback
	 * Does not modify $post['ping_status'] - could read 'open'
	 * Does not modify $default_ping_status - could read 'open'
	 *
	 * @param	arr		$headers	Array of header items
	 * @return	arr		$headers	Modified header array with pingbacks removed
	 */
	public static function remove_ping_headers( $headers )
	{
		if ( isset($headers['X-Pingback']) ) {
			unset($headers['X-Pingback']);
		}
		return $headers;
	}

	/**
	 * Kill the rewrite rule
	 *
	 * @param arr $rules Array of rewrite rules
	 * @return arr $rules Modified rewrite rules with pingbacks removed
	 */
	public static function remove_ping_rewrites( $rules )
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
	 * @param mixed $output The URL returned by bloginfo().
	 * @param mixed $show   Type of information requested.
	 * @return arr $rules Modified rewrite rules with pingbacks removed
	 */
	public static function remove_ping_pingback_url( $output, $show )
	{
		if ( 'pingback_url' == $show ) {
			$output = '';
		}
		return $output;
	}
}

endif;