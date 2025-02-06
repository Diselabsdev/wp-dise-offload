/**
 * DISE Offload Admin JavaScript
 * Handles all admin interface interactions and dynamic functionality
 */
(function($) {
    'use strict';

    const DiseOffload = {
        init: function() {
            this.initProviderToggle();
            this.initCdnToggle();
            this.initTestConnection();
            this.initProgressBars();
            this.initBulkOperations();
            this.initMediaLibrary();
        },

        initProviderToggle: function() {
            const self = this;
            $('#dise_offload_provider').on('change', function() {
                self.toggleProviderSettings($(this).val());
            });
            this.toggleProviderSettings($('#dise_offload_provider').val());
        },

        toggleProviderSettings: function(provider) {
            const settingsSections = [
                '#aws-settings',
                '#do-settings',
                '#gcs-settings',
                '#azure-settings',
                '#b2-settings',
                '#alibaba-settings',
                '#ibm-settings',
                '#bunny-settings'
            ];

            // Hide all sections first
            $(settingsSections.join(', ')).hide();

            // Show the selected provider's settings
            $(`#${provider}-settings`).fadeIn();

            // Update help text based on provider
            this.updateProviderHelp(provider);
        },

        initCdnToggle: function() {
            const self = this;
            $('#dise_offload_cdn_provider').on('change', function() {
                self.toggleCdnSettings($(this).val());
            });
            this.toggleCdnSettings($('#dise_offload_cdn_provider').val());
        },

        toggleCdnSettings: function(provider) {
            const cdnSections = [
                '#bunny-cdn-settings',
                '#azure-cdn-settings',
                '#cloudfront-settings'
            ];

            $(cdnSections.join(', ')).hide();
            if (provider !== 'none') {
                $(`#${provider}-settings`).fadeIn();
            }
        },

        initTestConnection: function() {
            $('.dise-test-connection').on('click', function(e) {
                e.preventDefault();
                const provider = $('#dise_offload_provider').val();
                const button = $(this);
                
                button.prop('disabled', true).text('Testing...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'dise_test_connection',
                        provider: provider,
                        nonce: dise_admin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Connection successful!');
                        } else {
                            alert('Connection failed: ' + response.data.message);
                        }
                    },
                    error: function() {
                        alert('Connection test failed. Please try again.');
                    },
                    complete: function() {
                        button.prop('disabled', false).text('Test Connection');
                    }
                });
            });
        },

        initProgressBars: function() {
            $('.dise-progress').each(function() {
                const bar = $(this).find('.dise-progress-bar');
                const value = parseInt(bar.data('progress'));
                bar.css('width', value + '%');
            });
        },

        initBulkOperations: function() {
            $('#dise-bulk-upload').on('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to upload all media files to cloud storage?')) {
                    DiseOffload.startBulkUpload();
                }
            });

            $('#dise-bulk-delete').on('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete all local media files? This cannot be undone!')) {
                    DiseOffload.startBulkDelete();
                }
            });
        },

        startBulkUpload: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dise_bulk_upload',
                    nonce: dise_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        DiseOffload.processBulkOperation(response.data.batch_id);
                    } else {
                        alert('Failed to start bulk upload: ' + response.data.message);
                    }
                }
            });
        },

        startBulkDelete: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dise_bulk_delete',
                    nonce: dise_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        DiseOffload.processBulkOperation(response.data.batch_id);
                    } else {
                        alert('Failed to start bulk delete: ' + response.data.message);
                    }
                }
            });
        },

        processBulkOperation: function(batchId) {
            const progressBar = $('.dise-progress-bar');
            const progressText = $('.dise-progress-text');
            let progress = 0;

            const updateProgress = function() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'dise_check_progress',
                        batch_id: batchId,
                        nonce: dise_admin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            progress = response.data.progress;
                            progressBar.css('width', progress + '%');
                            progressText.text(progress + '%');

                            if (progress < 100) {
                                setTimeout(updateProgress, 1000);
                            } else {
                                alert('Operation completed successfully!');
                            }
                        }
                    }
                });
            };

            updateProgress();
        },

        initMediaLibrary: function() {
            // Add custom buttons to media library items
            if (wp.media) {
                wp.media.view.Attachment.Library = wp.media.view.Attachment.Library.extend({
                    render: function() {
                        const original = wp.media.view.Attachment.Library.prototype.render.apply(this, arguments);
                        if (!this.$el.find('.dise-media-actions').length) {
                            this.$el.append(
                                '<div class="dise-media-actions">' +
                                '<button class="button dise-upload-single">Upload to Cloud</button>' +
                                '<button class="button dise-delete-local">Delete Local</button>' +
                                '</div>'
                            );
                        }
                        return original;
                    }
                });
            }

            // Handle single file actions
            $(document).on('click', '.dise-upload-single', function(e) {
                e.preventDefault();
                const attachmentId = $(this).closest('.attachment').data('id');
                DiseOffload.handleSingleFileUpload(attachmentId);
            });

            $(document).on('click', '.dise-delete-local', function(e) {
                e.preventDefault();
                const attachmentId = $(this).closest('.attachment').data('id');
                if (confirm('Are you sure you want to delete the local copy of this file?')) {
                    DiseOffload.handleSingleFileDelete(attachmentId);
                }
            });
        },

        handleSingleFileUpload: function(attachmentId) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dise_upload_single',
                    attachment_id: attachmentId,
                    nonce: dise_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('File uploaded successfully!');
                    } else {
                        alert('Upload failed: ' + response.data.message);
                    }
                }
            });
        },

        handleSingleFileDelete: function(attachmentId) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dise_delete_single',
                    attachment_id: attachmentId,
                    nonce: dise_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Local file deleted successfully!');
                    } else {
                        alert('Delete failed: ' + response.data.message);
                    }
                }
            });
        },

        updateProviderHelp: function(provider) {
            const helpText = {
                's3': 'Enter your AWS credentials and bucket information. Make sure your bucket has proper CORS configuration.',
                'do': 'Configure your DigitalOcean Spaces credentials and choose your preferred region.',
                'gcs': 'Upload your Google Cloud service account key file and specify your bucket name.',
                'azure': 'Enter your Azure storage account credentials and container information.',
                'b2': 'Provide your Backblaze B2 application key and bucket details.',
                'alibaba': 'Configure your Alibaba Cloud OSS access credentials and endpoint.',
                'ibm': 'Enter your IBM Cloud Object Storage credentials and endpoint information.',
                'bunny': 'Set up your Bunny CDN storage zone and API credentials.'
            };

            $('.dise-provider-help').text(helpText[provider] || '');
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        DiseOffload.init();
    });

})(jQuery);
