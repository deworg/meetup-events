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
}
