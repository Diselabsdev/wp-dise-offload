<?php
namespace DiseOffload\Media;

class MediaHandler {
    private $storage_manager;

    public function __construct($storage_manager) {
        $this->storage_manager = $storage_manager;
    }

    public function init() {
        // Hook into WordPress media upload process
        add_filter('wp_handle_upload', [$this, 'handle_media_upload']);
        add_filter('wp_get_attachment_url', [$this, 'get_attachment_url'], 10, 2);
        add_filter('wp_delete_file', [$this, 'handle_media_delete']);
        add_action('add_attachment', [$this, 'handle_attachment_metadata']);
    }

    public function handle_media_upload($upload) {
        if (!$upload || !isset($upload['file']) || !file_exists($upload['file'])) {
            return $upload;
        }

        // Get the file path relative to the uploads directory
        $upload_dir = wp_upload_dir();
        $relative_path = str_replace($upload_dir['basedir'] . '/', '', $upload['file']);

        // Upload to cloud storage
        $cloud_url = $this->storage_manager->upload_file($upload['file'], $relative_path);

        if ($cloud_url) {
            // Store the cloud URL in post meta
            update_post_meta(get_the_ID(), '_dise_offload_cloud_url', $cloud_url);

            // Delete local file if option is enabled
            if (get_option('dise_offload_delete_local', '0')) {
                unlink($upload['file']);
            }
        }

        return $upload;
    }

    public function get_attachment_url($url, $post_id) {
        $cloud_url = get_post_meta($post_id, '_dise_offload_cloud_url', true);
        
        if ($cloud_url) {
            // Check if custom CDN URL is set
            $cdn_url = get_option('dise_offload_cdn_url', '');
            if (!empty($cdn_url)) {
                // Replace the storage URL with CDN URL
                $cloud_url = str_replace(
                    parse_url($cloud_url, PHP_URL_HOST),
                    rtrim($cdn_url, '/'),
                    $cloud_url
                );
            }
            return $cloud_url;
        }

        return $url;
    }

    public function handle_media_delete($file) {
        if ($file) {
            // Get the file path relative to the uploads directory
            $upload_dir = wp_upload_dir();
            $relative_path = str_replace($upload_dir['basedir'] . '/', '', $file);

            // Delete from cloud storage
            $this->storage_manager->delete_file($relative_path);
        }

        return $file;
    }

    public function handle_attachment_metadata($attachment_id) {
        $metadata = wp_get_attachment_metadata($attachment_id);
        
        if (!$metadata) {
            return;
        }

        // Handle thumbnail images
        if (isset($metadata['sizes'])) {
            $upload_dir = wp_upload_dir();
            $attachment_path = get_attached_file($attachment_id);
            $base_dir = dirname($attachment_path);

            foreach ($metadata['sizes'] as $size => $size_info) {
                $size_file = $base_dir . '/' . $size_info['file'];
                
                if (file_exists($size_file)) {
                    $relative_path = str_replace($upload_dir['basedir'] . '/', '', $size_file);
                    $this->storage_manager->upload_file($size_file, $relative_path);

                    if (get_option('dise_offload_delete_local', '0')) {
                        unlink($size_file);
                    }
                }
            }
        }
    }
}
