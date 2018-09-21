<?php
/**
 * Meetup events importer plugin.
 *
 * @package   deworg\MeetupEvents
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name: Meetup Events
 * Description: Description.
 * Version:     0.1.0
 * Author:      Matthias Kittsteiner, Florian Brinkmann
 * Author URI:  https://florianbrinkmann.com/en/
 * License:     GPL v2 http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: meetup-events
 */
namespace deworg\MeetupEvents;

// Load Composer autoloader. From https://github.com/brightnucleus/jasper-client/blob/master/tests/bootstrap.php#L55-L59
$autoloader = dirname( __FILE__ ) . '/vendor/autoload.php';
if ( is_readable( $autoloader ) ) {
	require_once $autoloader;
}
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Create object.
$plugin = new Plugin();
// Init the plugin.
$plugin->init();
