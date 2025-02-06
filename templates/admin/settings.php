<?php
if (!defined('ABSPATH')) exit;

// Get current settings
$provider = get_option('dise_offload_provider', '');
$cdn_provider = get_option('dise_offload_cdn_provider', 'none');
$copy_to_cloud = get_option('dise_offload_copy_to_cloud', '1');
$remove_local = get_option('dise_offload_remove_local', '0');

// Get statistics
$total_files = (int)get_option('dise_offload_total_files', 0);
$total_size = (int)get_option('dise_offload_total_size', 0);
$bandwidth_saved = (int)get_option('dise_offload_bandwidth_saved', 0);
?>

<div class="wrap dise-settings-wrap">
    <div class="dise-header">
        <img src="<?php echo DISE_OFFLOAD_PLUGIN_URL; ?>assets/images/logo.svg" alt="DISE Offload" class="dise-logo">
        <h1><?php _e('DISE Offload Settings', 'dise-offload'); ?></h1>
        <span class="dise-version">v<?php echo DISE_OFFLOAD_VERSION; ?></span>
    </div>

    <div class="dise-stats-dashboard">
        <div class="dise-stat-card">
            <span class="dashicons dashicons-media-document"></span>
            <div class="dise-stat-content">
                <h3><?php _e('Total Files', 'dise-offload'); ?></h3>
                <p><?php echo number_format($total_files); ?></p>
            </div>
        </div>
        <div class="dise-stat-card">
            <span class="dashicons dashicons-cloud"></span>
            <div class="dise-stat-content">
                <h3><?php _e('Total Size', 'dise-offload'); ?></h3>
                <p><?php echo size_format($total_size); ?></p>
            </div>
        </div>
        <div class="dise-stat-card">
            <span class="dashicons dashicons-performance"></span>
            <div class="dise-stat-content">
                <h3><?php _e('Bandwidth Saved', 'dise-offload'); ?></h3>
                <p><?php echo size_format($bandwidth_saved); ?></p>
            </div>
        </div>
    </div>

    <h2 class="nav-tab-wrapper dise-nav-wrapper">
        <a href="#general" class="dise-nav-tab nav-tab"><?php _e('General', 'dise-offload'); ?></a>
        <a href="#providers" class="dise-nav-tab nav-tab"><?php _e('Storage Providers', 'dise-offload'); ?></a>
        <a href="#cdn" class="dise-nav-tab nav-tab"><?php _e('CDN', 'dise-offload'); ?></a>
        <a href="#tools" class="dise-nav-tab nav-tab"><?php _e('Tools', 'dise-offload'); ?></a>
    </h2>

    <form method="post" action="options.php" class="dise-settings-form">
        <?php settings_fields('dise_offload_settings'); ?>

        <div id="general" class="dise-settings-section">
            <table class="form-table dise-form-table">
                <tr>
                    <th scope="row"><?php _e('Copy Files to Cloud', 'dise-offload'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="dise_offload_copy_to_cloud" value="1" <?php checked($copy_to_cloud, '1'); ?>>
                            <?php _e('Automatically copy new media files to cloud storage', 'dise-offload'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Remove Local Files', 'dise-offload'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="dise_offload_remove_local" value="1" <?php checked($remove_local, '1'); ?>>
                            <?php _e('Remove local files after successful upload to cloud', 'dise-offload'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <div id="providers" class="dise-settings-section">
            <input type="hidden" name="dise_offload_provider" value="<?php echo esc_attr($provider); ?>">
            
            <div class="dise-provider-grid">
                <!-- AWS S3 -->
                <div class="dise-provider-card<?php echo $provider === 's3' ? ' active' : ''; ?>" data-provider="s3">
                    <img src="<?php echo DISE_OFFLOAD_PLUGIN_URL; ?>assets/images/providers/aws.svg" alt="AWS S3">
                    <h3><?php _e('Amazon S3', 'dise-offload'); ?></h3>
                    <p><?php _e('Store your media files on Amazon Simple Storage Service (S3).', 'dise-offload'); ?></p>
                </div>

                <!-- Google Cloud Storage -->
                <div class="dise-provider-card<?php echo $provider === 'gcs' ? ' active' : ''; ?>" data-provider="gcs">
                    <img src="<?php echo DISE_OFFLOAD_PLUGIN_URL; ?>assets/images/providers/gcp.svg" alt="Google Cloud Storage">
                    <h3><?php _e('Google Cloud Storage', 'dise-offload'); ?></h3>
                    <p><?php _e('Use Google Cloud Storage for your media files.', 'dise-offload'); ?></p>
                </div>

                <!-- Azure Blob Storage -->
                <div class="dise-provider-card<?php echo $provider === 'azure' ? ' active' : ''; ?>" data-provider="azure">
                    <img src="<?php echo DISE_OFFLOAD_PLUGIN_URL; ?>assets/images/providers/azure.svg" alt="Azure Blob Storage">
                    <h3><?php _e('Azure Blob Storage', 'dise-offload'); ?></h3>
                    <p><?php _e('Store your media files on Microsoft Azure Blob Storage.', 'dise-offload'); ?></p>
                </div>

                <!-- Alibaba Cloud OSS -->
                <div class="dise-provider-card<?php echo $provider === 'alibaba' ? ' active' : ''; ?>" data-provider="alibaba">
                    <img src="<?php echo DISE_OFFLOAD_PLUGIN_URL; ?>assets/images/providers/alibaba.svg" alt="Alibaba Cloud OSS">
                    <h3><?php _e('Alibaba Cloud OSS', 'dise-offload'); ?></h3>
                    <p><?php _e('Use Alibaba Cloud Object Storage Service for your media.', 'dise-offload'); ?></p>
                </div>
            </div>

            <!-- AWS S3 Settings -->
            <div id="s3-settings" class="dise-provider-settings-panel" <?php echo $provider === 's3' ? '' : 'style="display: none;"'; ?>>
                <h3><?php _e('Amazon S3 Settings', 'dise-offload'); ?></h3>
                <table class="form-table dise-form-table">
                    <tr>
                        <th scope="row"><?php _e('Access Key ID', 'dise-offload'); ?></th>
                        <td>
                            <input type="text" id="dise_offload_aws_key" name="dise_offload_aws_key" 
                                value="<?php echo esc_attr(get_option('dise_offload_aws_key')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Secret Access Key', 'dise-offload'); ?></th>
                        <td>
                            <input type="password" id="dise_offload_aws_secret" name="dise_offload_aws_secret" 
                                value="<?php echo esc_attr(get_option('dise_offload_aws_secret')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Bucket', 'dise-offload'); ?></th>
                        <td>
                            <input type="text" id="dise_offload_aws_bucket" name="dise_offload_aws_bucket" 
                                value="<?php echo esc_attr(get_option('dise_offload_aws_bucket')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Region', 'dise-offload'); ?></th>
                        <td>
                            <input type="text" id="dise_offload_aws_region" name="dise_offload_aws_region" 
                                value="<?php echo esc_attr(get_option('dise_offload_aws_region', 'us-east-1')); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="button" class="button dise-test-connection"><?php _e('Test Connection', 'dise-offload'); ?></button>
                </p>
            </div>

            <!-- Google Cloud Storage Settings -->
            <div id="gcs-settings" class="dise-provider-settings-panel" <?php echo $provider === 'gcs' ? '' : 'style="display: none;"'; ?>>
                <h3><?php _e('Google Cloud Storage Settings', 'dise-offload'); ?></h3>
                <table class="form-table dise-form-table">
                    <tr>
                        <th scope="row"><?php _e('Key File JSON', 'dise-offload'); ?></th>
                        <td>
                            <textarea id="dise_offload_gcs_key_file" name="dise_offload_gcs_key_file" 
                                class="large-text code" rows="10"><?php echo esc_textarea(get_option('dise_offload_gcs_key_file')); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Bucket', 'dise-offload'); ?></th>
                        <td>
                            <input type="text" id="dise_offload_gcs_bucket" name="dise_offload_gcs_bucket" 
                                value="<?php echo esc_attr(get_option('dise_offload_gcs_bucket')); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="button" class="button dise-test-connection"><?php _e('Test Connection', 'dise-offload'); ?></button>
                </p>
            </div>

            <!-- Azure Blob Storage Settings -->
            <div id="azure-settings" class="dise-provider-settings-panel" <?php echo $provider === 'azure' ? '' : 'style="display: none;"'; ?>>
                <h3><?php _e('Azure Blob Storage Settings', 'dise-offload'); ?></h3>
                <table class="form-table dise-form-table">
                    <tr>
                        <th scope="row"><?php _e('Connection String', 'dise-offload'); ?></th>
                        <td>
                            <input type="password" id="dise_offload_azure_connection_string" name="dise_offload_azure_connection_string" 
                                value="<?php echo esc_attr(get_option('dise_offload_azure_connection_string')); ?>" class="regular-text">
                            <p class="description"><?php _e('Your Azure Storage account connection string', 'dise-offload'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Container Name', 'dise-offload'); ?></th>
                        <td>
                            <input type="text" id="dise_offload_azure_container" name="dise_offload_azure_container" 
                                value="<?php echo esc_attr(get_option('dise_offload_azure_container')); ?>" class="regular-text">
                            <p class="description"><?php _e('The name of your Azure Storage container', 'dise-offload'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Account Name', 'dise-offload'); ?></th>
                        <td>
                            <input type="text" id="dise_offload_azure_account_name" name="dise_offload_azure_account_name" 
                                value="<?php echo esc_attr(get_option('dise_offload_azure_account_name')); ?>" class="regular-text">
                            <p class="description"><?php _e('Your Azure Storage account name', 'dise-offload'); ?></p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="button" class="button dise-test-connection"><?php _e('Test Connection', 'dise-offload'); ?></button>
                </p>
            </div>

            <!-- Alibaba Cloud OSS Settings -->
            <div id="alibaba-settings" class="dise-provider-settings-panel" <?php echo $provider === 'alibaba' ? '' : 'style="display: none;"'; ?>>
                <h3><?php _e('Alibaba Cloud OSS Settings', 'dise-offload'); ?></h3>
                <table class="form-table dise-form-table">
                    <tr>
                        <th scope="row"><?php _e('Access Key ID', 'dise-offload'); ?></th>
                        <td>
                            <input type="text" id="dise_offload_alibaba_key" name="dise_offload_alibaba_key" 
                                value="<?php echo esc_attr(get_option('dise_offload_alibaba_key')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Access Key Secret', 'dise-offload'); ?></th>
                        <td>
                            <input type="password" id="dise_offload_alibaba_secret" name="dise_offload_alibaba_secret" 
                                value="<?php echo esc_attr(get_option('dise_offload_alibaba_secret')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Endpoint', 'dise-offload'); ?></th>
                        <td>
                            <input type="text" id="dise_offload_alibaba_endpoint" name="dise_offload_alibaba_endpoint" 
                                value="<?php echo esc_attr(get_option('dise_offload_alibaba_endpoint')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Bucket', 'dise-offload'); ?></th>
                        <td>
                            <input type="text" id="dise_offload_alibaba_bucket" name="dise_offload_alibaba_bucket" 
                                value="<?php echo esc_attr(get_option('dise_offload_alibaba_bucket')); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="button" class="button dise-test-connection"><?php _e('Test Connection', 'dise-offload'); ?></button>
                </p>
            </div>
        </div>

        <div id="cdn" class="dise-settings-section">
            <table class="form-table dise-form-table">
                <tr>
                    <th scope="row"><?php _e('CDN Provider', 'dise-offload'); ?></th>
                    <td>
                        <select id="dise_offload_cdn_provider" name="dise_offload_cdn_provider">
                            <option value="none" <?php selected($cdn_provider, 'none'); ?>><?php _e('None', 'dise-offload'); ?></option>
                            <option value="cloudfront" <?php selected($cdn_provider, 'cloudfront'); ?>><?php _e('Amazon CloudFront', 'dise-offload'); ?></option>
                            <option value="bunnycdn" <?php selected($cdn_provider, 'bunnycdn'); ?>><?php _e('Bunny CDN', 'dise-offload'); ?></option>
                            <option value="azure_cdn" <?php selected($cdn_provider, 'azure_cdn'); ?>><?php _e('Azure CDN', 'dise-offload'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>

            <!-- CloudFront Settings -->
            <div id="cloudfront-settings" class="dise-cdn-settings-panel" <?php echo $cdn_provider === 'cloudfront' ? '' : 'style="display: none;"'; ?>>
                <h3><?php _e('CloudFront Settings', 'dise-offload'); ?></h3>
                <table class="form-table dise-form-table">
                    <tr>
                        <th scope="row"><?php _e('Domain Name', 'dise-offload'); ?></th>
                        <td>
                            <input type="text" name="dise_offload_cloudfront_domain" 
                                value="<?php echo esc_attr(get_option('dise_offload_cloudfront_domain')); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Bunny CDN Settings -->
            <div id="bunnycdn-settings" class="dise-cdn-settings-panel" <?php echo $cdn_provider === 'bunnycdn' ? '' : 'style="display: none;"'; ?>>
                <h3><?php _e('Bunny CDN Settings', 'dise-offload'); ?></h3>
                <table class="form-table dise-form-table">
                    <tr>
                        <th scope="row"><?php _e('Pull Zone', 'dise-offload'); ?></th>
                        <td>
                            <input type="text" name="dise_offload_bunny_cdn_pull_zone" 
                                value="<?php echo esc_attr(get_option('dise_offload_bunny_cdn_pull_zone')); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Azure CDN Settings -->
            <div id="azure_cdn-settings" class="dise-cdn-settings-panel" <?php echo $cdn_provider === 'azure_cdn' ? '' : 'style="display: none;"'; ?>>
                <h3><?php _e('Azure CDN Settings', 'dise-offload'); ?></h3>
                <table class="form-table dise-form-table">
                    <tr>
                        <th scope="row"><?php _e('Profile Name', 'dise-offload'); ?></th>
                        <td>
                            <input type="text" name="dise_offload_azure_cdn_profile" 
                                value="<?php echo esc_attr(get_option('dise_offload_azure_cdn_profile')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Endpoint', 'dise-offload'); ?></th>
                        <td>
                            <input type="text" name="dise_offload_azure_cdn_endpoint" 
                                value="<?php echo esc_attr(get_option('dise_offload_azure_cdn_endpoint')); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div id="tools" class="dise-settings-section">
            <div class="dise-tools-grid">
                <div class="dise-tool-card">
                    <h3><?php _e('Bulk Upload', 'dise-offload'); ?></h3>
                    <p><?php _e('Upload all media files that are not yet in cloud storage.', 'dise-offload'); ?></p>
                    <button type="button" id="dise-bulk-upload" class="button dise-button"><?php _e('Start Upload', 'dise-offload'); ?></button>
                </div>

                <div class="dise-tool-card">
                    <h3><?php _e('Sync Library', 'dise-offload'); ?></h3>
                    <p><?php _e('Synchronize your media library with cloud storage.', 'dise-offload'); ?></p>
                    <button type="button" id="dise-sync-library" class="button dise-button"><?php _e('Start Sync', 'dise-offload'); ?></button>
                </div>

                <div class="dise-tool-card">
                    <h3><?php _e('Remove Local Files', 'dise-offload'); ?></h3>
                    <p><?php _e('Remove local copies of files that are stored in the cloud.', 'dise-offload'); ?></p>
                    <button type="button" id="dise-bulk-delete" class="button dise-button"><?php _e('Remove Files', 'dise-offload'); ?></button>
                </div>
            </div>

            <div class="dise-progress-wrapper" style="display: none;">
                <div class="dise-progress">
                    <div class="dise-progress-bar"></div>
                </div>
                <div class="dise-progress-text">0%</div>
            </div>
        </div>

        <?php submit_button(); ?>
    </form>
</div>
