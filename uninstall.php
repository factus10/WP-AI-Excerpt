<?php
/**
 * WP AI Excerpt Uninstall
 *
 * This file is executed when the plugin is uninstalled.
 * It removes all plugin data from the database.
 */

// Exit if uninstall not called from WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove plugin options
delete_option('wp_ai_excerpt_default_length');
delete_option('wp_ai_excerpt_api_key');
delete_option('wp_ai_excerpt_model');
delete_option('wp_ai_excerpt_prompt');

// Remove any transients if used
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wp_ai_excerpt_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wp_ai_excerpt_%'");