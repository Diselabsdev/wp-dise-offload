# DISE Offload - WordPress Media & Assets Offloader

DISE Offload is a powerful WordPress plugin that helps you optimize your website's performance by offloading media and assets to various cloud storage providers.

## Features

- Support for multiple cloud storage providers:
  - Amazon S3
  - Google Cloud Storage
  - Microsoft Azure Blob Storage
  - Alibaba Cloud OSS
- CDN Integration:
  - Amazon CloudFront
  - Bunny CDN
  - Azure CDN
- Automated media synchronization
- Real-time file processing
- User-friendly configuration interface
- Automatic plugin asset updates
- Option to delete local files after successful upload
- Secure and reliable cloud storage integration

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Composer (for installation)
- Account with any supported cloud storage provider

## Installation

1. Download the plugin and extract it to your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/Diselabsdev/wp-dise-offload.git
   ```

2. Install dependencies using Composer:
   ```bash
   cd wp-dise-offload
   composer install
   ```
   This will install all required dependencies:
   - AWS SDK for PHP (for Amazon S3)
   - Google Cloud Storage Client Library
   - Azure Storage Blob SDK
   - Alibaba Cloud OSS SDK
   - Other development dependencies

3. Activate the plugin through the WordPress admin interface.

4. Configure your storage provider settings:
   - Go to WordPress admin panel
   - Navigate to "DISE Offload" in the main menu
   - Select your preferred cloud storage provider
   - Enter your cloud storage credentials
   - Configure additional settings as needed

## Configuration

### Storage Provider Setup

1. **Amazon S3**
   - Access Key ID
   - Secret Access Key
   - Bucket Name
   - Region

2. **Google Cloud Storage**
   - Service Account Key (JSON)
   - Bucket Name

3. **Azure Blob Storage**
   - Connection String
   - Container Name
   - Account Name

4. **Alibaba Cloud OSS**
   - Access Key ID
   - Access Key Secret
   - Endpoint
   - Bucket Name

### CDN Configuration

1. **Amazon CloudFront**
   - Distribution Domain Name

2. **Bunny CDN**
   - Pull Zone URL

3. **Azure CDN**
   - Profile Name
   - Endpoint Name

## Support

For support, please create an issue in the GitHub repository or contact our support team.
