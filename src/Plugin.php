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
	 * Plugin constructor.
	 */
	public function __construct() {
	}

	/**
	 * Init function.
	 */
	public function init() {
		// Register the post type.
		add_action( 'init', [ $this, 'register_post_type' ] );

		// Register the post meta.
		add_action( 'init', [ $this, 'register_post_meta' ], 900 );

		// Add meta values to the REST API response.
		add_filter( 'rest_prepare_meetup-events', [ $this, 'rest_prepare_meetup_events' ], 10, 2 );
	}

	/**
	 * Register the post type.
	 */
	public function register_post_type() {
		$post_type_args = [
			'labels'       => [
				'name'          => esc_html__( 'Meetup events', 'meetup-events' ),
				'singular_name' => esc_html__( 'Meetup event', 'meetup-events' ),
				'menu_name'     => esc_html__( 'Meetup events', 'meetup-events' ),
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

		register_post_type( 'meetup-events', $post_type_args );
	}

	/**
	 * Register the needed post meta.
	 */
	public function register_post_meta() {
		register_post_meta(
			'meetup-events',
			'meetup_events_date',
			[
				'type' => 'string',
				'description' => esc_html__( 'The date of the meetup.', 'meetup-events' ),
				'single' => true,
				'show_in_rest' => true,
			]
		);

		register_post_meta(
			'meetup-events',
			'meetup_events_time',
			[
				'type' => 'string',
				'description' => esc_html__( 'Start time of the meetup.', 'meetup-events' ),
				'single' => true,
				'show_in_rest' => true,
			]
		);
	}

	/**
	 * Add post meta data to REST API response.
	 *
	 * @param \WP_REST_Response $data Response data object.
	 * @param \WP_Post $post Post object.
	 *
	 * @return \WP_REST_Response
	 */
	public function rest_prepare_meetup_events( $data, $post ) {
		$date = get_post_meta( $post->ID, 'meetup_events_date', true );
		$time = get_post_meta( $post->ID, 'meetup_events_time', true );

		if ( $date ) {
			$data->data['meetup_date'] = $date;
		}

		if ( $time ) {
			$data->data['meetup_time'] = $time;
		}

		return $data;
	}
}
