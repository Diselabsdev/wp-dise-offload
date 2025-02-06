<?php
namespace DiseOffload\Storage;

class StorageManager {
    private $provider;
    private $client;
    private $cdn_client;

    public function init() {
        $this->setup_provider();
        $this->setup_cdn();
    }

    private function setup_provider() {
        $provider = get_option('dise_offload_provider', 's3');
        
        switch ($provider) {
            case 's3':
                $this->setup_s3();
                break;
            case 'do':
                $this->setup_digitalocean();
                break;
            case 'gcs':
                $this->setup_google_cloud();
                break;
            case 'azure':
                $this->setup_azure();
                break;
            case 'b2':
                $this->setup_backblaze();
                break;
            case 'alibaba':
                $this->setup_alibaba();
                break;
            case 'ibm':
                $this->setup_ibm();
                break;
            case 'bunny':
                $this->setup_bunny();
                break;
        }
    }

    private function setup_cdn() {
        $cdn_provider = get_option('dise_offload_cdn_provider', 'none');
        
        switch ($cdn_provider) {
            case 'bunny':
                $this->setup_bunny_cdn();
                break;
            case 'azure_cdn':
                $this->setup_azure_cdn();
                break;
            case 'cloudfront':
                $this->setup_cloudfront();
                break;
        }
    }

    private function setup_s3() {
        // Check for vendor directory
        $autoload_path = DISE_OFFLOAD_PLUGIN_DIR . 'vendor/autoload.php';
        
        if (!file_exists($autoload_path)) {
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-error">
                    <p><?php _e('DISE Offload: Dependencies not installed. Please run "composer install" in the plugin directory.', 'dise-offload'); ?></p>
                </div>
                <?php
            });
            return;
        }

        // Load AWS SDK via Composer autoload
        require_once $autoload_path;

        $aws_key = get_option('dise_offload_aws_key', '');
        $aws_secret = get_option('dise_offload_aws_secret', '');
        $aws_region = get_option('dise_offload_aws_region', 'us-east-1');

        if (empty($aws_key) || empty($aws_secret)) {
            return;
        }

        try {
            $this->client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => $aws_region,
                'credentials' => [
                    'key'    => $aws_key,
                    'secret' => $aws_secret,
                ],
            ]);
        } catch (\Exception $e) {
            // Log error
            error_log('DISE Offload S3 Setup Error: ' . $e->getMessage());
        }
    }

    private function setup_digitalocean() {
        if (!class_exists('Aws\S3\S3Client')) {
            require_once DISE_OFFLOAD_PLUGIN_DIR . 'vendor/autoload.php';
        }

        $do_key = get_option('dise_offload_do_key', '');
        $do_secret = get_option('dise_offload_do_secret', '');
        $do_region = get_option('dise_offload_do_region', 'nyc3');

        if (empty($do_key) || empty($do_secret)) {
            return;
        }

        try {
            $this->client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => $do_region,
                'endpoint' => 'https://' . $do_region . '.digitaloceanspaces.com',
                'credentials' => [
                    'key'    => $do_key,
                    'secret' => $do_secret,
                ],
            ]);
        } catch (\Exception $e) {
            // Log error
            error_log('DISE Offload DigitalOcean Setup Error: ' . $e->getMessage());
        }
    }

    private function setup_google_cloud() {
        if (!class_exists('Google\Cloud\Storage\StorageClient')) {
            require_once DISE_OFFLOAD_PLUGIN_DIR . 'vendor/autoload.php';
        }

        $gcs_key_file = get_option('dise_offload_gcs_key_file', '');
        
        if (empty($gcs_key_file)) {
            return;
        }

        try {
            $this->client = new \Google\Cloud\Storage\StorageClient([
                'keyFilePath' => $gcs_key_file
            ]);
        } catch (\Exception $e) {
            error_log('DISE Offload Google Cloud Setup Error: ' . $e->getMessage());
        }
    }

    private function setup_azure() {
        if (!class_exists('MicrosoftAzure\Storage\Blob\BlobRestProxy')) {
            require_once DISE_OFFLOAD_PLUGIN_DIR . 'vendor/autoload.php';
        }

        $azure_connection_string = get_option('dise_offload_azure_connection_string', '');
        
        if (empty($azure_connection_string)) {
            return;
        }

        try {
            $this->client = \MicrosoftAzure\Storage\Blob\BlobRestProxy::createBlobService($azure_connection_string);
        } catch (\Exception $e) {
            error_log('DISE Offload Azure Setup Error: ' . $e->getMessage());
        }
    }

    private function setup_backblaze() {
        if (!class_exists('BackblazeB2\Client')) {
            require_once DISE_OFFLOAD_PLUGIN_DIR . 'vendor/autoload.php';
        }

        $b2_key_id = get_option('dise_offload_b2_key_id', '');
        $b2_application_key = get_option('dise_offload_b2_application_key', '');
        
        if (empty($b2_key_id) || empty($b2_application_key)) {
            return;
        }

        try {
            $this->client = new \BackblazeB2\Client($b2_key_id, $b2_application_key);
        } catch (\Exception $e) {
            error_log('DISE Offload Backblaze Setup Error: ' . $e->getMessage());
        }
    }

    private function setup_alibaba() {
        if (!class_exists('OSS\OssClient')) {
            require_once DISE_OFFLOAD_PLUGIN_DIR . 'vendor/autoload.php';
        }

        $access_key = get_option('dise_offload_alibaba_key', '');
        $secret = get_option('dise_offload_alibaba_secret', '');
        $endpoint = get_option('dise_offload_alibaba_endpoint', '');
        
        if (empty($access_key) || empty($secret) || empty($endpoint)) {
            return;
        }

        try {
            $this->client = new \OSS\OssClient($access_key, $secret, $endpoint);
        } catch (\Exception $e) {
            error_log('DISE Offload Alibaba Setup Error: ' . $e->getMessage());
        }
    }

    private function setup_ibm() {
        if (!class_exists('Aws\S3\S3Client')) {
            require_once DISE_OFFLOAD_PLUGIN_DIR . 'vendor/autoload.php';
        }

        $api_key = get_option('dise_offload_ibm_api_key', '');
        $resource_instance_id = get_option('dise_offload_ibm_resource_id', '');
        $endpoint = get_option('dise_offload_ibm_endpoint', '');
        
        if (empty($api_key) || empty($resource_instance_id) || empty($endpoint)) {
            return;
        }

        try {
            $this->client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region'  => 'us-geo',
                'endpoint' => $endpoint,
                'credentials' => [
                    'key'    => $api_key,
                    'secret' => $resource_instance_id,
                ],
            ]);
        } catch (\Exception $e) {
            error_log('DISE Offload IBM Setup Error: ' . $e->getMessage());
        }
    }

    private function setup_bunny() {
        $api_key = get_option('dise_offload_bunny_api_key', '');
        $storage_zone = get_option('dise_offload_bunny_storage_zone', '');
        $region = get_option('dise_offload_bunny_region', '');
        
        if (empty($api_key) || empty($storage_zone)) {
            return;
        }

        try {
            $this->client = new \BunnyCDN\Storage\BunnyCDNStorage($api_key, $storage_zone, $region);
        } catch (\Exception $e) {
            error_log('DISE Offload Bunny Setup Error: ' . $e->getMessage());
        }
    }

    private function setup_bunny_cdn() {
        $pull_zone = get_option('dise_offload_bunny_cdn_pull_zone', '');
        $api_key = get_option('dise_offload_bunny_cdn_api_key', '');
        
        if (empty($pull_zone) || empty($api_key)) {
            return;
        }

        $this->cdn_client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.bunny.net/',
            'headers' => [
                'AccessKey' => $api_key
            ]
        ]);
    }

    private function setup_azure_cdn() {
        $profile_name = get_option('dise_offload_azure_cdn_profile', '');
        $endpoint_name = get_option('dise_offload_azure_cdn_endpoint', '');
        
        if (empty($profile_name) || empty($endpoint_name)) {
            return;
        }

        // Azure CDN setup using Azure Management API
        $this->cdn_client = new \GuzzleHttp\Client([
            'base_uri' => 'https://management.azure.com/',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->get_azure_token()
            ]
        ]);
    }

    private function get_azure_token() {
        // Implement Azure token acquisition logic
        return '';
    }

    public function upload_file($source_path, $destination_path) {
        if (!$this->client) {
            return false;
        }

        $provider = get_option('dise_offload_provider', 's3');

        try {
            switch ($provider) {
                case 's3':
                case 'do':
                    return $this->upload_s3_style($source_path, $destination_path);
                case 'gcs':
                    return $this->upload_google_cloud($source_path, $destination_path);
                case 'azure':
                    return $this->upload_azure($source_path, $destination_path);
                case 'b2':
                    return $this->upload_backblaze($source_path, $destination_path);
                case 'alibaba':
                    return $this->upload_alibaba($source_path, $destination_path);
                case 'ibm':
                    return $this->upload_ibm($source_path, $destination_path);
                case 'bunny':
                    return $this->upload_bunny($source_path, $destination_path);
                default:
                    return false;
            }
        } catch (\Exception $e) {
            error_log('DISE Offload Upload Error: ' . $e->getMessage());
            return false;
        }
    }

    private function upload_s3_style($source_path, $destination_path) {
        $bucket = $this->get_bucket();
        $result = $this->client->putObject([
            'Bucket' => $bucket,
            'Key'    => $destination_path,
            'SourceFile' => $source_path,
            'ACL'    => 'public-read',
        ]);
        return $result['ObjectURL'];
    }

    private function upload_google_cloud($source_path, $destination_path) {
        $bucket_name = get_option('dise_offload_gcs_bucket', '');
        $bucket = $this->client->bucket($bucket_name);
        $object = $bucket->upload(
            fopen($source_path, 'r'),
            ['name' => $destination_path]
        );
        return 'https://storage.googleapis.com/' . $bucket_name . '/' . $destination_path;
    }

    private function upload_azure($source_path, $destination_path) {
        $container_name = get_option('dise_offload_azure_container', '');
        $content = fopen($source_path, 'r');
        $this->client->createBlockBlob($container_name, $destination_path, $content);
        $account_name = get_option('dise_offload_azure_account_name', '');
        return "https://{$account_name}.blob.core.windows.net/{$container_name}/{$destination_path}";
    }

    private function upload_backblaze($source_path, $destination_path) {
        $bucket_name = get_option('dise_offload_b2_bucket', '');
        $file = $this->client->upload([
            'BucketName' => $bucket_name,
            'FileName'   => $destination_path,
            'Body'       => fopen($source_path, 'r')
        ]);
        return $file->getDownloadUrl();
    }

    private function upload_alibaba($source_path, $destination_path) {
        $bucket = get_option('dise_offload_alibaba_bucket', '');
        $this->client->uploadFile($bucket, $destination_path, $source_path);
        return $this->client->signUrl($bucket, $destination_path, 3600);
    }

    private function upload_ibm($source_path, $destination_path) {
        $bucket = get_option('dise_offload_ibm_bucket', '');
        $result = $this->client->putObject([
            'Bucket' => $bucket,
            'Key'    => $destination_path,
            'SourceFile' => $source_path,
            'ACL'    => 'public-read',
        ]);
        return $result['ObjectURL'];
    }

    private function upload_bunny($source_path, $destination_path) {
        $content = file_get_contents($source_path);
        $this->client->upload("/{$destination_path}", $content);
        
        $storage_zone = get_option('dise_offload_bunny_storage_zone', '');
        $region = get_option('dise_offload_bunny_region', '');
        return "https://{$storage_zone}.b-cdn.net/{$destination_path}";
    }

    public function delete_file($file_path) {
        if (!$this->client) {
            return false;
        }

        $provider = get_option('dise_offload_provider', 's3');

        try {
            switch ($provider) {
                case 's3':
                case 'do':
                    return $this->delete_s3_style($file_path);
                case 'gcs':
                    return $this->delete_google_cloud($file_path);
                case 'azure':
                    return $this->delete_azure($file_path);
                case 'b2':
                    return $this->delete_backblaze($file_path);
                case 'alibaba':
                    return $this->delete_alibaba($file_path);
                case 'ibm':
                    return $this->delete_ibm($file_path);
                case 'bunny':
                    return $this->delete_bunny($file_path);
                default:
                    return false;
            }
        } catch (\Exception $e) {
            error_log('DISE Offload Delete Error: ' . $e->getMessage());
            return false;
        }
    }

    private function delete_s3_style($file_path) {
        $bucket = $this->get_bucket();
        $this->client->deleteObject([
            'Bucket' => $bucket,
            'Key'    => $file_path,
        ]);
        return true;
    }

    private function delete_google_cloud($file_path) {
        $bucket_name = get_option('dise_offload_gcs_bucket', '');
        $bucket = $this->client->bucket($bucket_name);
        $object = $bucket->object($file_path);
        $object->delete();
        return true;
    }

    private function delete_azure($file_path) {
        $container_name = get_option('dise_offload_azure_container', '');
        $this->client->deleteBlob($container_name, $file_path);
        return true;
    }

    private function delete_backblaze($file_path) {
        $bucket_name = get_option('dise_offload_b2_bucket', '');
        $file = $this->client->getFile([
            'BucketName' => $bucket_name,
            'FileName'   => $file_path
        ]);
        $this->client->deleteFile([
            'FileId' => $file->getId()
        ]);
        return true;
    }

    private function delete_alibaba($file_path) {
        $bucket = get_option('dise_offload_alibaba_bucket', '');
        $this->client->deleteObject($bucket, $file_path);
        return true;
    }

    private function delete_ibm($file_path) {
        $bucket = get_option('dise_offload_ibm_bucket', '');
        $this->client->deleteObject([
            'Bucket' => $bucket,
            'Key'    => $file_path,
        ]);
        return true;
    }

    private function delete_bunny($file_path) {
        $storage_zone = get_option('dise_offload_bunny_storage_zone', '');
        $this->client->delete("/{$storage_zone}/{$file_path}");
        return true;
    }

    private function get_bucket() {
        $provider = get_option('dise_offload_provider', 's3');
        switch ($provider) {
            case 's3':
                return get_option('dise_offload_aws_bucket', '');
            case 'do':
                return get_option('dise_offload_do_space', '');
            case 'alibaba':
                return get_option('dise_offload_alibaba_bucket', '');
            case 'ibm':
                return get_option('dise_offload_ibm_bucket', '');
            default:
                return '';
        }
    }

    public function purge_cdn_cache($url) {
        if (!$this->cdn_client) {
            return false;
        }

        $cdn_provider = get_option('dise_offload_cdn_provider', 'none');
        
        try {
            switch ($cdn_provider) {
                case 'bunny':
                    return $this->purge_bunny_cdn($url);
                case 'azure_cdn':
                    return $this->purge_azure_cdn($url);
                case 'cloudfront':
                    return $this->purge_cloudfront($url);
                default:
                    return false;
            }
        } catch (\Exception $e) {
            error_log('DISE Offload CDN Purge Error: ' . $e->getMessage());
            return false;
        }
    }

    private function purge_bunny_cdn($url) {
        $pull_zone = get_option('dise_offload_bunny_cdn_pull_zone', '');
        $response = $this->cdn_client->post("purge?url={$url}");
        return $response->getStatusCode() === 200;
    }

    private function purge_azure_cdn($url) {
        $profile_name = get_option('dise_offload_azure_cdn_profile', '');
        $endpoint_name = get_option('dise_offload_azure_cdn_endpoint', '');
        
        $response = $this->cdn_client->post(
            "subscriptions/{subscription_id}/resourceGroups/{resource_group}/providers/Microsoft.Cdn/profiles/{$profile_name}/endpoints/{$endpoint_name}/purge",
            [
                'json' => [
                    'contentPaths' => [$url]
                ]
            ]
        );
        return $response->getStatusCode() === 202;
    }

    private function purge_cloudfront($url) {
        if (!class_exists('Aws\CloudFront\CloudFrontClient')) {
            require_once DISE_OFFLOAD_PLUGIN_DIR . 'vendor/autoload.php';
        }

        $distribution_id = get_option('dise_offload_cloudfront_distribution_id', '');
        $cloudfront = new \Aws\CloudFront\CloudFrontClient([
            'version' => 'latest',
            'region'  => 'us-east-1',
            'credentials' => [
                'key'    => get_option('dise_offload_aws_key', ''),
                'secret' => get_option('dise_offload_aws_secret', ''),
            ],
        ]);

        $result = $cloudfront->createInvalidation([
            'DistributionId' => $distribution_id,
            'InvalidationBatch' => [
                'Paths' => [
                    'Quantity' => 1,
                    'Items' => [$url],
                ],
                'CallerReference' => time(),
            ],
        ]);

        return $result['Status'] === 'InProgress';
    }
}
