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
		'aachen'      => [
			'name' => 'WP Meetup Aachen',
			'slug' => 'aachen-wordpress-meetup',
		],
		'berlin'      => [
			'name' => 'WP Meetup Berlin',
			'slug' => 'berlin-wordpress-meetup',
		],
		'bern'        => [
			'name' => 'WP Meetup Bern',
			'slug' => 'wordpress-bern',
		],
		'bonn'        => [
			'name' => 'WP Meetup Bonn',
			'slug' => 'wordpress-meetup-bonn-wpbn',
		],
		'bremen'      => [
			'name' => 'WP Meetup Bremen',
			'slug' => 'bremen-wordpress-meetup-group',
		],
		'dortmund' => [
			'name' => 'WP Meetup Dortmund',
			'slug' => 'wordpress-meetup-dortmund',
		],
		'duesseldorf' => [
			'name' => 'WP Meetup Düsseldorf',
			'slug' => 'dusseldorf-wordpress-meetup',
		],
		'eifel'       => [
			'name' => 'WP Meetup Eifel',
			'slug' => 'eifel-wordpress-meetup',
		],
		'frankfurt'   => [
			'name' => 'WP Meetup Frankfurt',
			'slug' => 'wpmeetup-frankfurt',
		],
		'freiburg'    => [
			'name' => 'WP Meetup Freiburg im Breisgau',
			'slug' => 'freiburg-im-breisgau-wordpress-meetup',
		],
		'hamburg'     => [
			'name' => 'WP Meetup Hamburg',
			'slug' => 'hamburg-wordpress-meetup',
		],
		'hannover'    => [
			'name' => 'WP Meetup Hannover',
			'slug' => 'hannover-wordpress-meetup',
		],
		'heidelberg'     => [
			'name' => 'WP Meetup Heidelberg',
			'slug' => 'wordpress-heidelberg',
		],
		'kassel'     => [
			'name' => 'WP Meetup Kassel',
			'slug' => 'wordpress-meetup-kassel',
		],
		'kiel'     => [
			'name' => 'WP Meetup Kiel',
			'slug' => 'wordpress-meetup-kiel',
		],
		'koblenz'     => [
			'name' => 'WP Meetup Koblenz',
			'slug' => 'wordpress-meetup-koblenz',
		],
		'leipzig'     => [
			'name' => 'WP Meetup Leipzig',
			'slug' => 'leipzig-wordpress-meetup',
		],
		'mannheim'    => [
			'name' => 'WP Meetup Mannheim',
			'slug' => 'wordpress-mannheim',
		],
		'muenchen'    => [
			'name' => 'WP Meetup München',
			'slug' => 'munchen-wordpress-meetup',
		],
		'neustadt'   => [
			'name' => 'WP Meetup Neustadt',
			'slug' => 'wordpress-meetup-neustadt',
		],
		'nuernberg'   => [
			'name' => 'WP Meetup Nürnberg',
			'slug' => 'nurnberg-wordpress-meetup',
		],
		'osnabrueck'  => [
			'name' => 'WP Meetup Osnabrück/Münster',
			'slug' => 'wpmeetup-osnabrueck',
		],
		'paderborn'   => [
			'name' => 'WP Meetup Paderborn',
			'slug' => 'wp-meetup-paderborn',
		],
		'potsdam'     => [
			'name' => 'WP Meetup Potsdam',
			'slug' => 'wpmeetup-potsdam',
		],
		'saarland'    => [
			'name' => 'WP Meetup Saarland',
			'slug' => 'wordpress-meetup-saarland',
		],
		'stuttgart'   => [
			'name' => 'WP Meetup Stuttgart',
			'slug' => 'wpmeetup-stuttgart',
		],
		'wolfsburg'   => [
			'name' => 'WP Meetup Region Braunschweig–Wolfsburg',
			'slug' => 'wordpress-meetup-region-38',
		],
		'fulda'       => [
			'name' => 'WP Meetup Fulda',
			'slug' => 'wordpress-meetup-fulda',
		],
		'rostock'     => [
			'name' => 'WP Meetup Rostock',
			'slug' => 'wordpress-meetup-rostock',
		],
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
		// Create cron hook.
		\add_action( 'meetup_daily_cron_hook', [ $this, 'meetup_daily_cron' ] );

		\register_activation_hook( $this->plugin_file, [ $this, 'daily_cron_activation' ] );
		\register_deactivation_hook( $this->plugin_file, [ $this, 'daily_cron_deactivation' ] );

		// Register the post type.
		\add_action( 'init', [ $this, 'register_post_type' ] );

		// Register the post meta.
		\add_action( 'init', [ $this, 'register_post_meta' ] );

		// Register the post meta.
		\add_action( 'init', [ $this, 'register_taxonomy' ] );

		// Add meta values to the REST API response.
		\add_filter( 'rest_prepare_events', [ $this, 'rest_prepare_meetup_events' ], 10, 2 );

		// Order events in backend by event timestamp meta.
		\add_action( 'pre_get_posts', [ $this, 'reorder_events' ] );

		// Show event date as column in the backend posts table.
		// @link https://wordpress.stackexchange.com/a/19229.
		\add_filter( 'manage_events_posts_columns', [ $this, 'manage_events_posts_columns' ] );
		\add_filter( 'manage_events_posts_custom_column', [ $this, 'manage_events_posts_custom_column' ], 10, 2 );
		\add_filter( 'manage_edit-events_sortable_columns', [ $this, 'manage_edit_events_sortable_columns' ] );
	}
	
	public function daily_cron_activation() {
		if ( ! \wp_next_scheduled( 'meetup_daily_cron_hook' ) ) {
			\wp_schedule_event( time(), 'daily', 'meetup_daily_cron_hook' );
		}
	}
	
	public function daily_cron_deactivation() {
		if ( \wp_next_scheduled( 'meetup_daily_cron_hook' ) ) {
			\wp_clear_scheduled_hook( 'meetup_daily_cron_hook' );
		}
	}
	
	/**
	 * Delete all meetup events including their post meta.
	 * 
	 * Run this only in cron mode as it may take a while.
	 */
	private function delete_meetup_events() {
		global $wpdb;
		
		// delete all posts by post type.
		$sql = "DELETE		post,
    						meta
				FROM		" . $wpdb->prefix . "posts AS post
				LEFT JOIN	" . $wpdb->prefix . "postmeta AS meta ON meta.post_id = post.ID
				WHERE		post.post_type = 'events'";
		$result = $wpdb->query( $sql );
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
		
		// remove duplicates
		$json = \array_unique( $json, \SORT_REGULAR );
		
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
		$this->delete_meetup_events();
		
		foreach ( self::$meetup_slugs as $slug => $data ) {
			// Create Meetup group term if not exists.
			if ( null === \term_exists( $slug, 'meetup-group' ) ) {
				\wp_insert_term( $data['name'], 'meetup-group', [ 'slug' => $slug ] );
			}

			$events = $this->get_meetup_events( $data['slug'] );

			// Check if there was no error with getting the events.
			if ( $events !== false ) {
				// Remove the existing posts.
				$posts_args = [
					'post_type'              => 'events',
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'fields'                 => 'ids',
					'posts_per_page'         => 500,
					'tax_query' => [
						[
							'taxonomy' => 'meetup-group',
							'field'    => 'slug',
							'terms'    => $slug,
						],
					],
				];

				$existing_events = new \WP_Query( $posts_args );

				if ( $existing_events->have_posts() ) {
					foreach ( $existing_events->posts as $existing_event ) {
						\wp_delete_post( $existing_event );
					}
				}

				// Add posts.
				foreach ( $events as $event ) {
					$post_args = [
						'post_title'  => $event->name,
						'post_status' => 'publish',
						'post_type'   => 'events',
						'meta_input'  => [
							'meetup_event_date'      => \date( 'd.m.Y', \strtotime( $event->local_date ) ),
							'meetup_event_time'      => $event->local_time,
							'meetup_event_timestamp' => $event->time,
							'meetup_event_url'       => $event->link,
						],
					];

					$post_id = \wp_insert_post( $post_args );

					// Connect post with meetup group taxonomy.
					// @link https://developer.wordpress.org/reference/functions/wp_insert_post/#comment-2434.
					if ( ! \is_wp_error( $post_id ) ) {
						\wp_set_object_terms( $post_id, $slug, 'meetup-group' );
					}
				}
			}

			// prevent rate limit
			\sleep( 1 );
		}
	}

	/**
	 * Register the post type.
	 */
	public function register_post_type() {
		$post_type_args = [
			'labels'            => [
				'name'          => \esc_html__( 'Meetup events', 'meetup-events' ),
				'singular_name' => \esc_html__( 'Meetup event', 'meetup-events' ),
				'menu_name'     => \esc_html__( 'Meetup events', 'meetup-events' ),
			],
			'public'             => false,
			'show_ui'            => true,
			'publicly_queryable' => false,
			'show_in_rest'       => true,
			'supports'           => [
				'title',
			],
			'rest_base'          => 'events',
		];

		\register_post_type( 'events', $post_type_args );
	}

	/**
	 * Register the needed post meta.
	 */
	public function register_post_meta() {
		\register_post_meta(
			'events',
			'meetup_event_date',
			[
				'type'         => 'string',
				'description'  => \esc_html__( 'The date of the meetup.', 'meetup-events' ),
				'single'       => true,
				'show_in_rest' => true,
			]
		);

		\register_post_meta(
			'events',
			'meetup_event_time',
			[
				'type'         => 'string',
				'description'  => \esc_html__( 'Start time of event.', 'meetup-events' ),
				'single'       => true,
				'show_in_rest' => true,
			]
		);

		\register_post_meta(
			'events',
			'meetup_event_timestamp',
			[
				'type'         => 'integer',
				'description'  => \esc_html__( 'Start timestamp of event.', 'meetup-events' ),
				'single'       => true,
				'show_in_rest' => true,
			]
		);

		\register_post_meta(
			'events',
			'meetup_event_url',
			[
				'type'         => 'string',
				'description'  => \esc_html__( 'URL of event.', 'meetup-events' ),
				'single'       => true,
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

		\register_taxonomy( 'meetup-group', 'events', $taxonomy_args );
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
	 * Reorder the events table in the backend so they are orderd by the event timestamp.
	 * 
	 * @link https://wordpress.stackexchange.com/a/125513
	 * @link https://wordpress.stackexchange.com/a/141359
	 * 
	 * @param \WP_Query $query The query object.
	 */
	public function reorder_events( $query ) {
		// Check if function exists (does not exist for REST-API views).
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$s = get_current_screen();

		if ( is_admin() && $s->base === 'edit' && $s->post_type === 'events' && $query->is_main_query() && 'event_date' !== $query->get( 'orderby' ) ) {
			$query->set('meta_key', 'meetup_event_timestamp');
			$query->set('orderby', 'meta_value_num');
			$query->set('order', 'ASC');
		}

		// Check if the user sorts by the custom event date column.
		// @link https://www.smashingmagazine.com/2017/12/customizing-admin-columns-wordpress/.
		if ( is_admin() && $query->is_main_query() && 'event_date' === $query->get( 'orderby' ) ) {
			$query->set('meta_key', 'meetup_event_timestamp');
			$query->set('orderby', 'meta_value_num');
		}
	}

	/**
	 * Replace the default date column with custom one.
	 * 
	 * @link https://stackoverflow.com/a/3354804
	 * 
	 * @param array $columns The post columns.
	 * 
	 * @return array $columns The modified columns array.
	 */
	public function manage_events_posts_columns( $columns ) {
		$index = 0;
		foreach ( $columns as $key => $value ) {
			if ( $key === 'date' ) {
				$columns = 
					array_slice( $columns, 0, $index, true ) + 
					[ 'event_date' => __( 'Meetup date', 'meetup-events' ) ] + 
					array_slice( $columns, $index, count( $columns ) - 1, true );
				break;
			}
			$index++;
		}
		unset( $columns['date'] );

		return $columns;
	}

	/**
	 * Display meetup date in custom column.
	 *  
	 * @param array $column_name The column name.
	 */
	public function manage_events_posts_custom_column( $column_name, $post_id ) {
		if ( $column_name === 'event_date' ) {
			echo \get_post_meta( $post_id, 'meetup_event_date', true );
		}
	}

	/**
	 * Make event date column sortable.
	 *  
	 * @param array $columns Array of sortable columns.
	 * 
	 * @return array $columns Modified sortable columns array.
	 */
	public function manage_edit_events_sortable_columns( $columns ) {
		$columns['event_date'] = 'event_date';

		return $columns;
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
