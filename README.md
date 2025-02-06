# DISE Offload - WordPress Media & Assets Offloader

DISE Offload is a powerful WordPress plugin that helps you optimize your website's performance by offloading media and assets to cloud storage providers like AWS S3 and DigitalOcean Spaces.

## Features

- Support for multiple cloud storage providers:
  - Amazon S3
  - DigitalOcean Spaces
  - More providers coming soon!
- Custom CDN URL support with automatic SSL
- Automated media and assets (CSS/JS) synchronization
- Real-time file processing and CDN integration
- User-friendly configuration interface
- Automatic plugin asset updates
- Option to delete local files after successful upload
- Secure and reliable cloud storage integration

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- Composer (for installation)
- AWS account (for S3) or DigitalOcean account (for Spaces)

## Installation

1. Download the plugin and extract it to your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/your-username/dise-offload.git
   ```

2. Install dependencies using Composer:
   ```bash
   cd dise-offload
   composer install
   ```

3. Activate the plugin through the WordPress admin interface.

4. Configure your storage provider settings:
   - Go to WordPress admin panel
   - Navigate to "DISE Offload" in the main menu
   - Enter your cloud storage credentials
   - Configure additional settings as needed

## Configuration

### Amazon S3 Setup

1. Create an AWS account if you don't have one
2. Create an IAM user with appropriate S3 permissions
3. Create an S3 bucket for your media files
4. Copy your Access Key ID and Secret Access Key
5. Enter these details in the plugin settings

### DigitalOcean Spaces Setup

1. Create a DigitalOcean account if you don't have one
2. Create a new Space in your preferred region
3. Generate Spaces access keys
4. Enter these details in the plugin settings

### CDN Configuration

1. Set up CloudFront or your preferred CDN
2. Enter your CDN URL in the plugin settings
3. The plugin will automatically route all media through your CDN

## Usage

Once configured, the plugin will automatically:

1. Upload new media files to your chosen cloud storage
2. Sync CSS and JS files to the cloud
3. Serve media and assets from your CDN
4. Update file locations in your WordPress database
5. Optionally remove local copies of uploaded files

## Support

For support, feature requests, or bug reports, please:

1. Check our [documentation](https://example.com/docs)
2. Visit our [support forum](https://example.com/forum)
3. Create an issue on [GitHub](https://github.com/your-username/dise-offload/issues)

## Contributing

We welcome contributions! Please feel free to submit a Pull Request.

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.
