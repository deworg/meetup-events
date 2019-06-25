<?php
/**
 * Meetup events importer plugin.
 *
 * @package   deworg\MeetupEvents
 * @license   GPL-3.0
 *
 * @wordpress-plugin
 * Plugin Name: Meetup Events
 * Description: Automatically get all meetup events from Meetup.com for WordPress meetups.
 * Version:     0.1.6
 * Author:      Matthias Kittsteiner, Florian Brinkmann
 * Author URI:  https://florianbrinkmann.com/en/
 * License:     GPL v3 http://www.gnu.org/licenses/old-licenses/gpl-3.0.html
 * Text Domain: meetup-events
 * GitHub Plugin URI: https://github.com/deworg/meetup-events
 * GitHub Branch: master
 */
namespace deworg\MeetupEvents;

// Load class file.
require_once dirname( __FILE__ ) . '/src/Plugin.php';

// Create object.
$plugin = new Plugin();
$plugin->set_plugin_file( __FILE__ );
// Init the plugin.
$plugin->init();
