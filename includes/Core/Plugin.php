<?php
namespace DiseOffload\Core;

class Plugin {
    private static $instance = null;
    private $settings;
    private $storage_manager;
    private $media_handler;
    private $assets_handler;

    private function __construct() {
        // Private constructor to prevent direct creation
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init() {
        // Load dependencies
        $this->load_dependencies();
        
        // Initialize components
        $this->init_hooks();
        
        // Admin initialization
        if (is_admin()) {
            $this->init_admin();
        }
    }

    private function load_dependencies() {
        // Load required files
        require_once DISE_OFFLOAD_PLUGIN_DIR . 'includes/Admin/Settings.php';
        require_once DISE_OFFLOAD_PLUGIN_DIR . 'includes/Storage/StorageManager.php';
        require_once DISE_OFFLOAD_PLUGIN_DIR . 'includes/Media/MediaHandler.php';
        require_once DISE_OFFLOAD_PLUGIN_DIR . 'includes/Assets/AssetsHandler.php';

        // Initialize main components
        $this->settings = new \DiseOffload\Admin\Settings();
        $this->storage_manager = new \DiseOffload\Storage\StorageManager();
        $this->media_handler = new \DiseOffload\Media\MediaHandler($this->storage_manager);
        $this->assets_handler = new \DiseOffload\Assets\AssetsHandler($this->storage_manager);
    }

    private function init_hooks() {
        // Add activation and deactivation hooks
        register_activation_hook(DISE_OFFLOAD_PLUGIN_DIR . 'dise-offload.php', [$this, 'activate']);
        register_deactivation_hook(DISE_OFFLOAD_PLUGIN_DIR . 'dise-offload.php', [$this, 'deactivate']);
        
        // Initialize storage provider
        add_action('init', [$this->storage_manager, 'init']);
        
        // Initialize media handling
        add_action('init', [$this->media_handler, 'init']);
        
        // Initialize assets handling
        add_action('init', [$this->assets_handler, 'init']);
    }

    private function init_admin() {
        // Initialize admin settings
        $this->settings->init();
    }

    public function activate() {
        // Activation tasks
        flush_rewrite_rules();
        $this->create_required_directories();
    }

    public function deactivate() {
        // Deactivation tasks
        flush_rewrite_rules();
    }

    private function create_required_directories() {
        // Create necessary directories for the plugin
        $upload_dir = wp_upload_dir();
        $dise_offload_dir = $upload_dir['basedir'] . '/dise-offload';
        
        if (!file_exists($dise_offload_dir)) {
            wp_mkdir_p($dise_offload_dir);
        }
    }
}
