<?php
namespace DiseOffload\Admin;

class Settings {
    private $options_page = 'dise-offload-settings';
    private $option_group = 'dise_offload_settings';

    public function init() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_admin_menu() {
        add_menu_page(
            __('DISE Offload', 'dise-offload'),
            __('DISE Offload', 'dise-offload'),
            'manage_options',
            $this->options_page,
            [$this, 'render_settings_page'],
            'dashicons-cloud'
        );
    }

    public function register_settings() {
        // General Settings
        register_setting($this->option_group, 'dise_offload_copy_to_cloud');
        register_setting($this->option_group, 'dise_offload_remove_local');

        // Provider Settings
        register_setting($this->option_group, 'dise_offload_provider');
        
        // AWS S3 Settings
        register_setting($this->option_group, 'dise_offload_aws_key');
        register_setting($this->option_group, 'dise_offload_aws_secret');
        register_setting($this->option_group, 'dise_offload_aws_bucket');
        register_setting($this->option_group, 'dise_offload_aws_region');

        // Google Cloud Storage Settings
        register_setting($this->option_group, 'dise_offload_gcs_key_file');
        register_setting($this->option_group, 'dise_offload_gcs_bucket');

        // Azure Blob Storage Settings
        register_setting($this->option_group, 'dise_offload_azure_connection_string');
        register_setting($this->option_group, 'dise_offload_azure_container');
        register_setting($this->option_group, 'dise_offload_azure_account_name');

        // Alibaba Cloud OSS Settings
        register_setting($this->option_group, 'dise_offload_alibaba_key');
        register_setting($this->option_group, 'dise_offload_alibaba_secret');
        register_setting($this->option_group, 'dise_offload_alibaba_endpoint');
        register_setting($this->option_group, 'dise_offload_alibaba_bucket');

        // CDN Settings
        register_setting($this->option_group, 'dise_offload_cdn_provider');
        register_setting($this->option_group, 'dise_offload_cloudfront_domain');
        register_setting($this->option_group, 'dise_offload_bunny_cdn_pull_zone');
        register_setting($this->option_group, 'dise_offload_azure_cdn_profile');
        register_setting($this->option_group, 'dise_offload_azure_cdn_endpoint');

        // Statistics
        register_setting($this->option_group, 'dise_offload_total_files');
        register_setting($this->option_group, 'dise_offload_total_size');
        register_setting($this->option_group, 'dise_offload_bandwidth_saved');
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'dise-offload'));
        }

        // Include the settings template
        include DISE_OFFLOAD_PLUGIN_DIR . 'templates/admin/settings.php';
    }

    private function save_settings() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Verify nonce
        check_admin_referer('dise_offload_settings');

        // Save provider
        if (isset($_POST['dise_offload_provider'])) {
            update_option('dise_offload_provider', sanitize_text_field($_POST['dise_offload_provider']));
        }

        // Save AWS settings
        if (isset($_POST['dise_offload_aws_key'])) {
            update_option('dise_offload_aws_key', sanitize_text_field($_POST['dise_offload_aws_key']));
        }
        if (isset($_POST['dise_offload_aws_secret'])) {
            update_option('dise_offload_aws_secret', sanitize_text_field($_POST['dise_offload_aws_secret']));
        }
        if (isset($_POST['dise_offload_aws_bucket'])) {
            update_option('dise_offload_aws_bucket', sanitize_text_field($_POST['dise_offload_aws_bucket']));
        }
        if (isset($_POST['dise_offload_aws_region'])) {
            update_option('dise_offload_aws_region', sanitize_text_field($_POST['dise_offload_aws_region']));
        }

        // Save general settings
        if (isset($_POST['dise_offload_copy_to_cloud'])) {
            update_option('dise_offload_copy_to_cloud', '1');
        } else {
            update_option('dise_offload_copy_to_cloud', '0');
        }

        if (isset($_POST['dise_offload_remove_local'])) {
            update_option('dise_offload_remove_local', '1');
        } else {
            update_option('dise_offload_remove_local', '0');
        }

        // Save CDN settings
        if (isset($_POST['dise_offload_cdn_provider'])) {
            update_option('dise_offload_cdn_provider', sanitize_text_field($_POST['dise_offload_cdn_provider']));
        }
        if (isset($_POST['dise_offload_cloudfront_domain'])) {
            update_option('dise_offload_cloudfront_domain', sanitize_text_field($_POST['dise_offload_cloudfront_domain']));
        }

        // Add settings updated message
        add_settings_error(
            'dise_offload_messages',
            'dise_offload_message',
            __('Settings Saved', 'dise-offload'),
            'updated'
        );
    }
}
