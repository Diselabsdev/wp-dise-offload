<?php
namespace DiseOffload\Assets;

class AssetsHandler {
    private $storage_manager;
    private $cached_assets = [];

    public function __construct($storage_manager) {
        $this->storage_manager = $storage_manager;
    }

    public function init() {
        // Hook into WordPress asset processing
        add_action('wp_enqueue_scripts', [$this, 'process_assets'], 999);
        add_action('admin_enqueue_scripts', [$this, 'process_assets'], 999);
        add_action('upgrader_process_complete', [$this, 'handle_plugin_update'], 10, 2);
    }

    public function process_assets() {
        global $wp_scripts, $wp_styles;

        // Process scripts
        if (!empty($wp_scripts)) {
            $this->process_asset_type($wp_scripts, 'js');
        }

        // Process styles
        if (!empty($wp_styles)) {
            $this->process_asset_type($wp_styles, 'css');
        }
    }

    private function process_asset_type($wp_dependency, $type) {
        foreach ($wp_dependency->registered as $handle => $dependency) {
            if (empty($dependency->src)) {
                continue;
            }

            // Skip external assets
            if ($this->is_external_url($dependency->src)) {
                continue;
            }

            // Get the absolute path of the asset
            $asset_path = $this->get_asset_path($dependency->src);
            if (!$asset_path || !file_exists($asset_path)) {
                continue;
            }

            // Check if we already processed this asset
            if (isset($this->cached_assets[$asset_path])) {
                $dependency->src = $this->cached_assets[$asset_path];
                continue;
            }

            // Upload to cloud storage
            $relative_path = 'assets/' . $type . '/' . basename($asset_path);
            $cloud_url = $this->storage_manager->upload_file($asset_path, $relative_path);

            if ($cloud_url) {
                // Cache the result
                $this->cached_assets[$asset_path] = $cloud_url;
                
                // Update the asset URL
                $dependency->src = $cloud_url;

                // Apply CDN URL if configured
                $cdn_url = get_option('dise_offload_cdn_url', '');
                if (!empty($cdn_url)) {
                    $dependency->src = str_replace(
                        parse_url($cloud_url, PHP_URL_HOST),
                        rtrim($cdn_url, '/'),
                        $cloud_url
                    );
                }
            }
        }
    }

    public function handle_plugin_update($upgrader, $options) {
        if ($options['action'] === 'update' && $options['type'] === 'plugin') {
            // Clear cached assets to force re-processing
            $this->cached_assets = [];
            
            // Trigger asset processing
            $this->process_assets();
        }
    }

    private function is_external_url($url) {
        return strpos($url, '//') === 0 || strpos($url, 'http') === 0;
    }

    private function get_asset_path($src) {
        // Convert URL to file path
        if (strpos($src, content_url()) === 0) {
            return WP_CONTENT_DIR . substr($src, strlen(content_url()));
        } elseif (strpos($src, includes_url()) === 0) {
            return ABSPATH . WPINC . substr($src, strlen(includes_url()));
        } elseif (strpos($src, site_url()) === 0) {
            return ABSPATH . substr($src, strlen(site_url()));
        }
        
        return false;
    }
}
