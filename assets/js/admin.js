(function($) {
    'use strict';
    
    $(document).ready(function() {
        var $generator = $('#wp-ai-excerpt-generator');
        var $generateBtn = $('#wp_ai_excerpt_generate');
        var $status = $('#wp_ai_excerpt_status');
        var $result = $('#wp_ai_excerpt_result');
        var $excerptText = $('#wp_ai_excerpt_text');
        var $useBtn = $('#wp_ai_excerpt_use');
        var $lengthInput = $('#wp_ai_excerpt_length');
        
        // Function to create AI excerpt button
        function createAIExcerptButton() {
            var buttonHtml = '<button type="button" class="button wp-ai-excerpt-sidebar-btn" style="margin-top: 8px; width: 100%;">' +
                '<span class="dashicons dashicons-admin-generic" style="vertical-align: text-bottom;"></span> ' +
                'Generate AI Excerpt</button>';
            return buttonHtml;
        }
        
        // Function to show excerpt generation dialog
        function showExcerptDialog(callback) {
            var defaultLength = wpAiExcerpt.defaultLength || 150;
            var dialogHtml = '<div id="wp-ai-excerpt-dialog" style="display:none;">' +
                '<p>Generate an AI-powered excerpt for this post.</p>' +
                '<label>Excerpt length (words): ' +
                '<input type="number" id="wp-ai-excerpt-dialog-length" value="' + defaultLength + '" min="25" max="500" style="width: 80px; margin-left: 10px;" />' +
                '</label>' +
                '</div>';
            
            // Remove existing dialog if any
            $('#wp-ai-excerpt-dialog').remove();
            $('body').append(dialogHtml);
            
            var $dialog = $('#wp-ai-excerpt-dialog').dialog({
                title: 'Generate AI Excerpt',
                modal: true,
                width: 400,
                buttons: {
                    'Generate': function() {
                        var length = $('#wp-ai-excerpt-dialog-length').val();
                        $(this).dialog('close');
                        if (callback) callback(length);
                    },
                    'Cancel': function() {
                        $(this).dialog('close');
                    }
                },
                close: function() {
                    $(this).remove();
                }
            });
        }
        
        // Function to generate excerpt via AJAX
        function generateExcerpt(length, callback) {
            $.ajax({
                url: wpAiExcerpt.ajax_url,
                type: 'POST',
                data: {
                    action: 'generate_ai_excerpt',
                    post_id: wpAiExcerpt.post_id,
                    length: length,
                    nonce: wpAiExcerpt.nonce
                },
                success: function(response) {
                    if (response.success) {
                        callback(null, response.data.excerpt);
                    } else {
                        callback(response.data || wpAiExcerpt.error);
                    }
                },
                error: function() {
                    callback(wpAiExcerpt.error);
                }
            });
        }
        
        // Classic Editor enhancement
        if ($('#postexcerpt').length) {
            // Add button to classic editor excerpt box
            var $excerptBox = $('#postexcerpt .inside');
            if ($excerptBox.length) {
                $excerptBox.append(createAIExcerptButton());
                
                $excerptBox.on('click', '.wp-ai-excerpt-sidebar-btn', function() {
                    var $btn = $(this);
                    showExcerptDialog(function(length) {
                        $btn.prop('disabled', true).html('<span class="spinner is-active" style="float: none; margin: 0;"></span> Generating...');
                        
                        generateExcerpt(length, function(error, excerpt) {
                            if (error) {
                                alert('Error: ' + error);
                            } else {
                                $('#excerpt').val(excerpt);
                            }
                            $btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-generic"></span> Generate AI Excerpt');
                        });
                    });
                });
            }
        }
        
        // Block Editor enhancement
        if (wp && wp.data && wp.data.subscribe) {
            var intercepted = false;
            
            // Function to enhance excerpt panel
            function enhanceExcerptPanel() {
                // Check if excerpt panel is open
                var $excerptTextarea = $('.editor-post-excerpt textarea, .edit-post-post-excerpt textarea');
                if ($excerptTextarea.length && !$excerptTextarea.parent().find('.wp-ai-excerpt-sidebar-btn').length) {
                    $excerptTextarea.after(createAIExcerptButton());
                    
                    $excerptTextarea.parent().on('click', '.wp-ai-excerpt-sidebar-btn', function() {
                        var $btn = $(this);
                        showExcerptDialog(function(length) {
                            $btn.prop('disabled', true).html('<span class="spinner is-active" style="float: none; margin: 0;"></span> Generating...');
                            
                            generateExcerpt(length, function(error, excerpt) {
                                if (error) {
                                    alert('Error: ' + error);
                                } else {
                                    // Update excerpt in Block Editor
                                    wp.data.dispatch('core/editor').editPost({ excerpt: excerpt });
                                }
                                $btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-generic"></span> Generate AI Excerpt');
                            });
                        });
                    });
                }
            }
            
            // Function to intercept "Add an excerpt..." link
            function interceptExcerptLink() {
                var $excerptButton = $('.editor-post-excerpt__dropdown button, .edit-post-post-excerpt button').filter(function() {
                    return $(this).text().indexOf('Add an excerpt') > -1 || $(this).text().indexOf('excerpt') > -1;
                });
                
                if ($excerptButton.length && !intercepted) {
                    intercepted = true;
                    
                    // Clone the button to remove existing event handlers
                    var $newButton = $excerptButton.clone();
                    $excerptButton.replaceWith($newButton);
                    
                    $newButton.on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Show enhanced dialog
                        var dialogHtml = '<div id="wp-ai-excerpt-enhanced-dialog" style="display:none;">' +
                            '<div style="margin-bottom: 20px;">' +
                            '<h3 style="margin-top: 0;">Add Excerpt</h3>' +
                            '<textarea id="wp-ai-excerpt-manual" placeholder="Write an excerpt (optional)" style="width: 100%; min-height: 100px; margin-bottom: 15px;"></textarea>' +
                            '</div>' +
                            '<div style="border-top: 1px solid #ddd; padding-top: 20px;">' +
                            '<h3 style="margin-top: 0;">Or Generate with AI</h3>' +
                            '<label>Excerpt length (words): ' +
                            '<input type="number" id="wp-ai-excerpt-dialog-length" value="' + (wpAiExcerpt.defaultLength || 150) + '" min="25" max="500" style="width: 80px; margin-left: 10px;" />' +
                            '</label>' +
                            '<button type="button" id="wp-ai-excerpt-generate-btn" class="button button-primary" style="margin-left: 15px;">Generate AI Excerpt</button>' +
                            '<div id="wp-ai-excerpt-status" style="margin-top: 10px;"></div>' +
                            '</div>' +
                            '</div>';
                        
                        // Remove existing dialog if any
                        $('#wp-ai-excerpt-enhanced-dialog').remove();
                        $('body').append(dialogHtml);
                        
                        var currentExcerpt = wp.data.select('core/editor').getEditedPostAttribute('excerpt');
                        $('#wp-ai-excerpt-manual').val(currentExcerpt || '');
                        
                        var $dialog = $('#wp-ai-excerpt-enhanced-dialog').dialog({
                            title: 'Excerpt',
                            modal: true,
                            width: 500,
                            height: 'auto',
                            buttons: {
                                'Save': function() {
                                    var manualExcerpt = $('#wp-ai-excerpt-manual').val();
                                    wp.data.dispatch('core/editor').editPost({ excerpt: manualExcerpt });
                                    $(this).dialog('close');
                                },
                                'Cancel': function() {
                                    $(this).dialog('close');
                                }
                            },
                            close: function() {
                                $(this).remove();
                            }
                        });
                        
                        // Handle AI generation within dialog
                        $('#wp-ai-excerpt-generate-btn').on('click', function() {
                            var $btn = $(this);
                            var $status = $('#wp-ai-excerpt-status');
                            var length = $('#wp-ai-excerpt-dialog-length').val();
                            
                            $btn.prop('disabled', true).text('Generating...');
                            $status.html('<span class="spinner is-active" style="float: left;"></span> Generating excerpt...');
                            
                            generateExcerpt(length, function(error, excerpt) {
                                if (error) {
                                    $status.html('<span style="color: #d63638;">Error: ' + error + '</span>');
                                } else {
                                    $('#wp-ai-excerpt-manual').val(excerpt);
                                    $status.html('<span style="color: #00a32a;">Excerpt generated successfully!</span>');
                                }
                                $btn.prop('disabled', false).text('Generate AI Excerpt');
                            });
                        });
                    });
                }
            }
            
            // Subscribe to editor changes
            wp.data.subscribe(function() {
                // Try to intercept the excerpt link
                interceptExcerptLink();
                
                // Also enhance panel if it's already open
                enhanceExcerptPanel();
            });
            
            // Also check on initial load with a delay
            setTimeout(function() {
                interceptExcerptLink();
                enhanceExcerptPanel();
            }, 1000);
        }
        
        // Generate excerpt button click
        $generateBtn.on('click', function() {
            var $btn = $(this);
            var originalText = $btn.text();
            
            // Disable button and show loading
            $btn.prop('disabled', true).text(wpAiExcerpt.generating);
            $status.removeClass('error success').html('');
            $result.hide();
            
            // Make AJAX request
            $.ajax({
                url: wpAiExcerpt.ajax_url,
                type: 'POST',
                data: {
                    action: 'generate_ai_excerpt',
                    post_id: wpAiExcerpt.post_id,
                    length: $lengthInput.val(),
                    nonce: wpAiExcerpt.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $excerptText.val(response.data.excerpt);
                        $result.fadeIn();
                        $status.addClass('success').html('Excerpt generated successfully!');
                    } else {
                        $status.addClass('error').html(response.data || wpAiExcerpt.error);
                    }
                },
                error: function() {
                    $status.addClass('error').html(wpAiExcerpt.error);
                },
                complete: function() {
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        });
        
        // Use excerpt button click
        $useBtn.on('click', function() {
            var excerptValue = $excerptText.val();
            
            // Check if we're in the block editor or classic editor
            if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
                // Block editor
                wp.data.dispatch('core/editor').editPost({ excerpt: excerptValue });
                $status.addClass('success').html('Excerpt updated in the editor!');
            } else {
                // Classic editor
                $('#excerpt').val(excerptValue);
                $status.addClass('success').html('Excerpt updated! Remember to save your post.');
            }
            
            // Scroll to excerpt field
            var $excerptField = $('#excerpt, .editor-post-excerpt');
            if ($excerptField.length) {
                $('html, body').animate({
                    scrollTop: $excerptField.offset().top - 100
                }, 500);
            }
        });
        
        // Clear status messages after a delay
        $(document).on('click', '#wp_ai_excerpt_generate, #wp_ai_excerpt_use', function() {
            setTimeout(function() {
                $status.fadeOut(function() {
                    $(this).removeClass('error success').html('').show();
                });
            }, 5000);
        });
        
        // Validate length input
        $lengthInput.on('change', function() {
            var val = parseInt($(this).val());
            if (val < 25) $(this).val(25);
            if (val > 500) $(this).val(500);
        });
    });
    
})(jQuery);