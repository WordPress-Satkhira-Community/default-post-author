<?php

/**
 * Plugin Name: Default Post Author
 * Plugin URI: https://www.wpsatkhira.com/plugins/default-post-author
 * Description: The easiest way to set default post author in your WordPress Site.
 * Version: 1.0
 * Requires at least: 6.1
 * Requires PHP: 7.4
 * Author: WordPress Satkhira Community
 * Author URI: https://www.wpsatkhira.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: default-post-author
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    die; // Exit if accessed directly
}

/**
 * Load plugin textdomain.
 */
function dpa_load_textdomain() {
    load_plugin_textdomain('default-post-author', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'dpa_load_textdomain');


// Add the field to the general settings page
function dpa_add_author_setting_field() {
    add_settings_field(
        'default_post_author', // ID of the settings field
        'Default Post Author', // Title of the field
        'dpa_default_post_author_field_html', // Function to display the field
        'general' // Page to add the field to (General Settings page)
    );

    // Register the setting
    register_setting('general', 'default_post_author', array(
        'type' => 'integer',
        'description' => 'Default author ID for new posts',
        'sanitize_callback' => 'absint', // Ensure the value is an absolute integer
        'default' => 1 // Default value if not set
    ));
}
add_action('admin_init', 'dpa_add_author_setting_field');

// HTML for the settings field
function dpa_default_post_author_field_html() {
    $value = get_option('default_post_author', 1); // Get the current value, default to 1

    // Fetch users with 'Author', 'Editor', or 'Administrator' roles
    $user_query = new WP_User_Query(array(
        'role__in' => array('Author', 'Editor', 'Administrator'), // Array of roles
        'orderby' => 'display_name',
        'order' => 'ASC'
    ));

    $users = $user_query->get_results();

    // Create a dropdown list of users
    echo '<select id="default_post_author" name="default_post_author">';
    foreach ($users as $user) {
        echo '<option value="' . esc_attr($user->ID) . '"' . selected($user->ID, $value, false) . '>' . esc_html($user->display_name) . '</option>';
    }
    echo '</select>';
}

// Set the default author for new posts
function dpa_force_post_author( $data, $postarr ) {
    // Retrieve the default author ID from WordPress options, default to 1 if not set
    $default_author_id = get_option('default_post_author', 1);

    // Set the author for new posts
    if (empty($postarr['ID'])) {
        $data['post_author'] = $default_author_id;
    }
    return $data;
}
add_filter( 'wp_insert_post_data', 'dpa_force_post_author', 10, 2 );

// Set the revision author to the same author as the post
function dpa_set_revision_author($post_has_changed, $last_revision, $post) {
    global $wpdb;

    // Update the post_author of the revision to match the original post
    $result = $wpdb->update(
        $wpdb->posts,
        array('post_author' => $post->post_author),
        array('ID' => $last_revision->ID),
        array('%d'),
        array('%d')
    );

    // Basic error handling
    if (false === $result) {
        error_log('Failed to update revision author for post ID ' . $post->ID);
    }

    return $post_has_changed;
}
add_filter('wp_save_post_revision_check_for_changes', 'dpa_set_revision_author', 10, 3);
