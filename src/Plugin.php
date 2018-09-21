<?php
/**
 * Main class file.
 */

namespace deworg\MeetupEvents;

/**
 * Class Plugin
 * @package deworg\MeetupEvents
 */
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
		'berlin-wordpress-meetup',
		'wordpress-bern',
		'wordpress-meetup-bonn-wpbn',
		'bremen-wordpress-meetup-group',
		'dusseldorf-wordpress-meetup',
		'eifel-wordpress-meetup',
		'wpmeetup-frankfurt',
		'freiburg-im-breisgau-wordpress-meetup',
		'hamburg-wordpress-meetup',
		'hannover-wordpress-meetup',
		'wordpress-meetup-koblenz',
		'leipzig-wordpress-meetup',
		'munchen-wordpress-meetup',
		'nurnberg-wordpress-meetup',
		'wpmeetup-osnabrueck',
		'wp-meetup-paderborn',
		'wpmeetup-potsdam',
		'wordpress-meetup-saarland',
		'wpmeetup-stuttgart',
		'wordpress-meetup-region-38',
	];
	
	/**
	 * Plugin constructor.
	 */
	public function __construct() {
	}

	/**
	 * Init function.
	 */
	public function init() {
		\register_activation_hook( $this->plugin_file, 'daily_cron_activation' );
		\register_deactivation_hook( $this->plugin_file, 'daily_cron_deactivation' );
		
		// Register the post type.
		\add_action( 'init', [ $this, 'register_post_type' ] );

		// Register the post meta.
		\add_action( 'init', [ $this, 'register_post_meta' ] );

		// Register the post meta.
		\add_action( 'init', [ $this, 'register_taxonomy' ] );

		// Add meta values to the REST API response.
		\add_filter( 'rest_prepare_meetup-events', [ $this, 'rest_prepare_meetup_events' ], 10, 2 );
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
	 * Register the post type.
	 */
	public function register_post_type() {
		$post_type_args = [
			'labels'       => [
				'name'          => \esc_html__( 'Meetup events', 'meetup-events' ),
				'singular_name' => \esc_html__( 'Meetup event', 'meetup-events' ),
				'menu_name'     => \esc_html__( 'Meetup events', 'meetup-events' ),
			],
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => [
				'slug' => 'event',
			],
			'supports'     => [
				'title',
			],
		];

		\register_post_type( 'meetup-events', $post_type_args );
	}

	/**
	 * Register the needed post meta.
	 */
	public function register_post_meta() {
		\register_post_meta(
			'meetup-events',
			'meetup_events_date',
			[
				'type' => 'string',
				'description' => \esc_html__( 'The date of the meetup.', 'meetup-events' ),
				'single' => true,
				'show_in_rest' => true,
			]
		);

		\register_post_meta(
			'meetup-events',
			'meetup_events_time',
			[
				'type' => 'string',
				'description' => \esc_html__( 'Start time of the meetup.', 'meetup-events' ),
				'single' => true,
				'show_in_rest' => true,
			]
		);
	}

	/**
	 * Register custom taxonomy.
	 */
	public function register_taxonomy() {
		$taxonomy_args = [
			'labels' => [
				'name' => esc_html__( 'Meetup groups', 'meetup-events' ),
				'singular_name' => esc_html__( 'Meetup group', 'meetup-events' ),
			],
			'public' => true,
			'rewrite' => [
				'slug' => 'group',
			],
			'show_in_rest' => true,
		];

		\register_taxonomy( 'meetup-group', 'meetup-events', $taxonomy_args );
	}

	/**
	 * Add post meta data to REST API response.
	 *
	 * @param \WP_REST_Response $data Response data object.
	 * @param \WP_Post $post Post object.
	 * @param \WP_Post          $post Post object.
	 *
	 * @return \WP_REST_Response
	 */
	public function rest_prepare_meetup_events( $data, $post ) {
		$date = \get_post_meta( $post->ID, 'meetup_event_date', true );
		$time = \get_post_meta( $post->ID, 'meetup_event_time', true );
		$url = \get_post_meta( $post->ID, 'meetup_event_url', true );

		if ( $date ) {
			$data->data['meetup_date'] = $date;
		}

		if ( $time ) {
			$data->data['meetup_time'] = $time;
		}

		if ( $url ) {
			$data->data['meetup_url'] = $url;
		}

		return $data;
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
