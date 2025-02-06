jQuery(document).ready(function($) {
    // Initialize active tab
    const hash = window.location.hash || '#general';
    $(hash).show();
    $(`a[href="${hash}"]`).addClass('active');

    // Tab Navigation
    $('.dise-nav-tab').on('click', function(e) {
        e.preventDefault();
        const target = $(this).attr('href');
        
        // Update active tab
        $('.dise-nav-tab').removeClass('active');
        $(this).addClass('active');
        
        // Show target section
        $('.dise-settings-section').hide();
        $(target).show();

        // Update URL hash without scrolling
        history.pushState(null, null, target);
    });

    // Provider Selection
    $('.dise-provider-card').on('click', function() {
        const provider = $(this).data('provider');
        
        // Update active state
        $('.dise-provider-card').removeClass('active');
        $(this).addClass('active');
        
        // Update hidden input
        $('input[name="dise_offload_provider"]').val(provider);
        
        // Hide all provider settings panels
        $('.dise-provider-settings-panel').hide();

        // Show selected provider settings
        $(`#${provider}-settings`).fadeIn();

        // Scroll to settings
        $('html, body').animate({
            scrollTop: $(`#${provider}-settings`).offset().top - 50
        }, 500);
    });

    // Initialize provider settings visibility
    const currentProvider = $('input[name="dise_offload_provider"]').val();
    if (currentProvider) {
        $(`.dise-provider-card[data-provider="${currentProvider}"]`).addClass('active');
        $(`#${currentProvider}-settings`).show();
    }

    // CDN Provider Change
    $('#dise_offload_cdn_provider').on('change', function() {
        const provider = $(this).val();
        
        // Hide all CDN settings panels
        $('.dise-cdn-settings-panel').hide();

        // Show selected CDN settings if not 'none'
        if (provider !== 'none') {
            $(`#${provider}-settings`).fadeIn();
        }
    });

    // Initialize CDN settings visibility
    const currentCdn = $('#dise_offload_cdn_provider').val();
    if (currentCdn && currentCdn !== 'none') {
        $(`#${currentCdn}-settings`).show();
    }

    // Test Connection Button
    $('.dise-test-connection').on('click', function(e) {
        e.preventDefault();
        const provider = $('input[name="dise_offload_provider"]').val();
        const button = $(this);
        const originalText = button.text();

        // Disable button and show loading state
        button.prop('disabled', true).text('Testing...');

        // Make the AJAX call
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'dise_test_connection',
                provider: provider,
                nonce: dise_settings.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSuccess(response.data.message);
                } else {
                    showError(response.data.message);
                }
            },
            error: function() {
                showError(dise_settings.error_message);
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    });

    // Bulk Actions
    $('#dise-bulk-upload').on('click', function() {
        if (confirm(dise_settings.confirm_bulk_upload)) {
            startBulkOperation('upload');
        }
    });

    $('#dise-sync-library').on('click', function() {
        if (confirm(dise_settings.confirm_sync)) {
            startBulkOperation('sync');
        }
    });

    $('#dise-bulk-delete').on('click', function() {
        if (confirm(dise_settings.confirm_bulk_delete)) {
            startBulkOperation('delete');
        }
    });

    function startBulkOperation(operation) {
        const progressWrapper = $('.dise-progress-wrapper');
        const progressBar = $('.dise-progress-bar');
        const progressText = $('.dise-progress-text');
        
        progressWrapper.show();
        progressBar.width('0%');
        progressText.text('0%');

        // Disable buttons during operation
        $('.dise-button').prop('disabled', true);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'dise_bulk_operation',
                operation: operation,
                nonce: dise_settings.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateProgress(0, response.data.total);
                    processBatch(operation, 1, response.data.total);
                } else {
                    showError(response.data.message);
                    resetProgress();
                }
            },
            error: function() {
                showError(dise_settings.error_message);
                resetProgress();
            }
        });
    }

    function processBatch(operation, batch, total) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'dise_process_batch',
                operation: operation,
                batch: batch,
                nonce: dise_settings.nonce
            },
            success: function(response) {
                if (response.success) {
                    const progress = (batch * 100) / total;
                    updateProgress(progress);

                    if (batch < total) {
                        processBatch(operation, batch + 1, total);
                    } else {
                        operationComplete(response.data.message);
                    }
                } else {
                    showError(response.data.message);
                    resetProgress();
                }
            },
            error: function() {
                showError(dise_settings.error_message);
                resetProgress();
            }
        });
    }

    function updateProgress(progress) {
        $('.dise-progress-bar').width(progress + '%');
        $('.dise-progress-text').text(Math.round(progress) + '%');
    }

    function operationComplete(message) {
        resetProgress();
        showSuccess(message);
    }

    function resetProgress() {
        $('.dise-progress-wrapper').hide();
        $('.dise-button').prop('disabled', false);
    }

    function showError(message) {
        const notice = $('<div class="notice notice-error is-dismissible"><p></p></div>')
            .find('p')
            .text(message)
            .end()
            .insertBefore('.dise-settings-form');

        setTimeout(() => {
            notice.fadeOut(() => notice.remove());
        }, 5000);
    }

    function showSuccess(message) {
        const notice = $('<div class="notice notice-success is-dismissible"><p></p></div>')
            .find('p')
            .text(message)
            .end()
            .insertBefore('.dise-settings-form');

        setTimeout(() => {
            notice.fadeOut(() => notice.remove());
        }, 5000);
    }

    // Form validation
    $('.dise-settings-form').on('submit', function(e) {
        const provider = $('input[name="dise_offload_provider"]').val();
        let valid = true;
        
        // Remove previous error states
        $('.dise-form-table input').removeClass('error');
        
        // Validate based on selected provider
        switch (provider) {
            case 's3':
                valid = validateFields([
                    'aws_key',
                    'aws_secret',
                    'aws_bucket',
                    'aws_region'
                ]);
                break;
                
            case 'gcs':
                valid = validateFields([
                    'gcs_key_file',
                    'gcs_bucket'
                ]);
                break;
                
            case 'azure':
                valid = validateFields([
                    'azure_connection_string',
                    'azure_container',
                    'azure_account_name'
                ]);
                break;
                
            case 'alibaba':
                valid = validateFields([
                    'alibaba_key',
                    'alibaba_secret',
                    'alibaba_endpoint',
                    'alibaba_bucket'
                ]);
                break;
        }
        
        if (!valid) {
            e.preventDefault();
            showError(dise_settings.required_fields_message);
        }
    });

    function validateFields(fields) {
        let valid = true;
        
        fields.forEach(field => {
            const input = $(`#dise_offload_${field}`);
            if (!input.val()) {
                valid = false;
                input.addClass('error');
            }
        });
        
        return valid;
    }

    // Add hidden provider input if not exists
    if ($('input[name="dise_offload_provider"]').length === 0) {
        $('.dise-settings-form').append('<input type="hidden" name="dise_offload_provider" value="">');
    }
});
