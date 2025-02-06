<?php
/**
 * Plugin Name: DISE Offload
 * Plugin URI: https://example.com/dise-offload
 * Description: Offload your media to various cloud storage providers
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: dise-offload
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DISE_OFFLOAD_VERSION', '1.0.0');
define('DISE_OFFLOAD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DISE_OFFLOAD_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'DiseOffload\\';
    $base_dir = __DIR__ . '/includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize plugin
function dise_offload_init() {
    // Initialize settings
    $settings = new DiseOffload\Admin\Settings();
    $settings->init();

    // Initialize storage manager
    $storage = new DiseOffload\Storage\StorageManager();
    $storage->init();
}
add_action('plugins_loaded', 'dise_offload_init');

// Enqueue admin scripts and styles
function dise_offload_admin_enqueue_scripts($hook) {
    // Admin scripts for all admin pages
    wp_enqueue_script(
        'dise-offload-admin',
        DISE_OFFLOAD_PLUGIN_URL . 'assets/js/admin.js',
        array('jquery'),
        DISE_OFFLOAD_VERSION,
        true
    );

    // Settings page specific scripts and styles
    if ('toplevel_page_dise-offload-settings' === $hook) {
        // Enqueue settings styles
        wp_enqueue_style(
            'dise-offload-settings',
            DISE_OFFLOAD_PLUGIN_URL . 'assets/css/settings.css',
            array('dashicons'),
            DISE_OFFLOAD_VERSION
        );

        // Enqueue settings scripts
        wp_enqueue_script(
            'dise-offload-settings',
            DISE_OFFLOAD_PLUGIN_URL . 'assets/js/settings.js',
            array('jquery', 'jquery-ui-tooltip'),
            DISE_OFFLOAD_VERSION,
            true
        );

        // Localize script
        wp_localize_script('dise-offload-settings', 'dise_settings', array(
            'nonce' => wp_create_nonce('dise_offload_settings'),
            'confirm_bulk_upload' => __('Are you sure you want to upload all media files to cloud storage?', 'dise-offload'),
            'confirm_sync' => __('Are you sure you want to sync the media library with cloud storage?', 'dise-offload'),
            'confirm_bulk_delete' => __('Warning: This will remove all local files that have been uploaded to cloud storage. Are you sure you want to continue?', 'dise-offload'),
            'error_message' => __('An error occurred. Please try again.', 'dise-offload'),
            'required_fields_message' => __('Please fill in all required fields.', 'dise-offload')
        ));
    }
}
add_action('admin_enqueue_scripts', 'dise_offload_admin_enqueue_scripts');

// Register AJAX handlers
function dise_offload_register_ajax_handlers() {
    // Test connection
    add_action('wp_ajax_dise_test_connection', function() {
        check_ajax_referer('dise_offload_settings', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'dise-offload')));
        }

        $provider = isset($_POST['provider']) ? sanitize_text_field($_POST['provider']) : '';
        
        try {
            $storage = new DiseOffload\Storage\StorageManager();
            $result = $storage->test_connection($provider);
            
            if ($result === true) {
                wp_send_json_success(array('message' => __('Connection successful!', 'dise-offload')));
            } else {
                wp_send_json_error(array('message' => __('Connection failed. Please check your settings.', 'dise-offload')));
            }
        } catch (\Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    });

    // Bulk operations
    add_action('wp_ajax_dise_bulk_operation', function() {
        check_ajax_referer('dise_offload_settings', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'dise-offload')));
        }

        $operation = isset($_POST['operation']) ? sanitize_text_field($_POST['operation']) : '';
        
        try {
            $storage = new DiseOffload\Storage\StorageManager();
            
            switch ($operation) {
                case 'upload':
                    $total = $storage->count_unsynced_files();
                    wp_send_json_success(array('total' => $total));
                    break;
                    
                case 'sync':
                    $total = $storage->count_all_files();
                    wp_send_json_success(array('total' => $total));
                    break;
                    
                case 'delete':
                    $total = $storage->count_synced_files();
                    wp_send_json_success(array('total' => $total));
                    break;
                    
                default:
                    wp_send_json_error(array('message' => __('Invalid operation.', 'dise-offload')));
            }
        } catch (\Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    });

    // Process batch
    add_action('wp_ajax_dise_process_batch', function() {
        check_ajax_referer('dise_offload_settings', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'dise-offload')));
        }

        $operation = isset($_POST['operation']) ? sanitize_text_field($_POST['operation']) : '';
        $batch = isset($_POST['batch']) ? intval($_POST['batch']) : 1;
        
        try {
            $storage = new DiseOffload\Storage\StorageManager();
            
            switch ($operation) {
                case 'upload':
                    $result = $storage->upload_batch($batch);
                    break;
                    
                case 'sync':
                    $result = $storage->sync_batch($batch);
                    break;
                    
                case 'delete':
                    $result = $storage->delete_batch($batch);
                    break;
                    
                default:
                    wp_send_json_error(array('message' => __('Invalid operation.', 'dise-offload')));
            }
            
            if ($result === true) {
                wp_send_json_success(array('message' => __('Batch processed successfully.', 'dise-offload')));
            } else {
                wp_send_json_error(array('message' => __('Failed to process batch.', 'dise-offload')));
            }
        } catch (\Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    });
}
add_action('init', 'dise_offload_register_ajax_handlers');
