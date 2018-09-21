<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 21.09.2018
 * Time: 11:52
 */

namespace deworg\MeetupEvents;


class Plugin {
	/**
	 * @var		string The URL to the Meetup.com API
	 */
	private static $api_url = 'https://api.meetup.com/';
	
	/**
	 * @var		string The absolute location to the main plugin file
	 */
	protected $plugin_file = '';
	
	/**
	 * @var		array The slugs of meetups at Meetup.com
	 */
	private static $meetup_slugs = [
		'aachen-wordpress-meetup',
		'wpmeetup-stuttgart',
	];
	
	public function __construct() {
	}

	public function init() {
		\register_activation_hook( $this->plugin_file, 'daily_cron_activation' );
		\register_deactivation_hook( $this->plugin_file, 'daily_cron_deactivation' );
	}
	
	public function daily_cron_activation() {
		if ( ! \wp_next_scheduled( [ $this, 'meetup_daily_cron' ] ) ) {
			\wp_schedule_event( time(), 'daily', [ $this, 'meetup_daily_cron' ] );
		}
	}
	
	public function daily_cron_deactivation() {
		if ( \wp_next_scheduled( [ $this, 'meetup_daily_cron' ] ) ) {
			\wp_clear_scheduled_hook( [ $this, 'meetup_daily_cron' ] );
		}
	}
	
	/**
	 * Get all events from a meetup at Meetup.com.
	 * 
	 * @param	string		$urlname The slug of the meetup at Meetup.com
	 * @return	array|bool
	 */
	public function get_meetup_events( $urlname ) {
		// return if the parameter is no valid string for the request URL
		if ( ! \is_string( $urlname ) ) return false;
		
		// get the events
		$url = self::$api_url . $urlname . '/events';
		$request = \wp_remote_get( $url );
		$response = \wp_remote_retrieve_body( $request );
		
		// return if response is no valid JSON
		if ( ! self::is_json( $response ) ) return false;
		
		$json = \json_decode( $response );
		
		// return if response contains errors
		if ( ! empty( $json->errors ) ) {
			\error_log( 'Request: ' . $url . \PHP_EOL );
			\error_log( print_r( $json, true ) );
			
			return false;
		}
		
		return $json;
	}
	
	/**
	 * Check if given string is a valid JSON.
	 * 
	 * @param	string		$string
	 * @return	bool
	 */
	protected static function is_json( string $string ) {
		if ( ! \is_string( $string ) ) return false;
		
		\json_decode( $string );
		
		return ( \json_last_error() === JSON_ERROR_NONE );
	}
	
	/**
	 * Run the daily cron.
	 */
	public function meetup_daily_cron() {
		foreach ( self::$meetup_slugs as $slug ) {
			$events = $this->get_meetup_events( $slug );
			
			// prevent rate limit
			\sleep( 1 );
		}
	}
	
	/**
	 * Set the plugin file.
	 * 
	 * @param	string		$plugin_file
	 */
	public function set_plugin_file( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}
}
