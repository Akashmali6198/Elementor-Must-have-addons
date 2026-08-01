(function ($) {
    'use strict';

    /**
     * Document-level delegation so submit always works even if Elementor's
     * frontend/element_ready hook already fired before this script loaded.
     */
    function getAjaxConfig() {
        if (typeof emha_ajax !== 'undefined' && emha_ajax.ajax_url) {
            return emha_ajax;
        }
        return null;
    }

    function resolvePostId($form) {
        var fromForm = $form.find('input[name="post_id"]').val();
        if (fromForm) {
            return fromForm;
        }

        if (
            typeof elementorFrontendConfig !== 'undefined' &&
            elementorFrontendConfig.post &&
            elementorFrontendConfig.post.id
        ) {
            return elementorFrontendConfig.post.id;
        }

        if (
            typeof elementor !== 'undefined' &&
            elementor.config &&
            elementor.config.document &&
            elementor.config.document.id
        ) {
            return elementor.config.document.id;
        }

        return 0;
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        e.stopPropagation();

        var $form = $(this);
        var $msg = $form.find('.emha-form-response-msg');
        var ajax = getAjaxConfig();

        if (!ajax) {
            $msg
                .removeClass('emha-success')
                .addClass('emha-error')
                .text('Form script is not configured. Please refresh the page.')
                .show();
            return false;
        }

        // Prevent double submissions
        if ($form.hasClass('emha-form-loading')) {
            return false;
        }

        // HTML5 validation
        if (typeof this.checkValidity === 'function' && !this.checkValidity()) {
            if (typeof this.reportValidity === 'function') {
                this.reportValidity();
            }
            return false;
        }

        $form.addClass('emha-form-loading');
        $msg.removeClass('emha-success emha-error').hide().text('');

        var formData = new FormData(this);

        // Ensure WordPress AJAX action
        if (!formData.get('action')) {
            formData.set('action', 'emha_submit_form');
        }

        // Prefer form-embedded nonce; fall back to localized script nonce
        if (!formData.get('_wpnonce') && ajax.nonce) {
            formData.append('_wpnonce', ajax.nonce);
        }

        var postId = resolvePostId($form);
        if (postId && !formData.get('post_id')) {
            formData.append('post_id', postId);
        }

        $.ajax({
            url: ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                $form.removeClass('emha-form-loading');

                if (response && response.success) {
                    var message =
                        (response.data && response.data.message) ||
                        'Your submission was sent successfully!';
                    $msg.addClass('emha-success').text(message).fadeIn();
                    $form[0].reset();
                } else {
                    var errorMessage =
                        (response && response.data && response.data.message) ||
                        'An error occurred. Please try again.';
                    $msg.addClass('emha-error').text(errorMessage).fadeIn();
                }
            },
            error: function (xhr) {
                $form.removeClass('emha-form-loading');

                var errorMessage = 'Network error. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMessage = xhr.responseJSON.data.message;
                } else if (xhr.status === 403) {
                    errorMessage = 'Security check failed. Please refresh the page and try again.';
                } else if (xhr.status === 0) {
                    errorMessage = 'Could not reach the server. Check your connection.';
                }

                $msg.addClass('emha-error').text(errorMessage).fadeIn();
            }
        });

        return false;
    }

    /**
     * Multisite signup: show/hide site fields when visitor chooses
     * "Gimme a site!" vs "Just a username".
     */
    function toggleBlogFields($form) {
        var $blogFields = $form.find('[data-emha-blog-fields="1"]');
        if (!$blogFields.length) {
            return;
        }

        var $radios = $form.find('.emha-signup-for-radio');
        if (!$radios.length) {
            // Forced blog or user mode — keep fields as rendered.
            $blogFields.find('input').prop('required', true);
            return;
        }

        var signupFor = $form.find('.emha-signup-for-radio:checked').val() || 'user';
        if (signupFor === 'blog') {
            $blogFields.show();
            $blogFields.find('input').prop('required', true);
        } else {
            $blogFields.hide();
            $blogFields.find('input').prop('required', false).val('');
        }
    }

    // Bind once (covers all current + future form instances)
    $(document)
        .off('submit.emhaForm', '.emha-ajax-form')
        .on('submit.emhaForm', '.emha-ajax-form', handleFormSubmit);

    $(document)
        .off('change.emhaFormSignup', '.emha-signup-for-radio')
        .on('change.emhaFormSignup', '.emha-signup-for-radio', function () {
            toggleBlogFields($(this).closest('form'));
        });

    // Init blog field visibility for forms already on the page
    function initRegistrationForms() {
        $('.emha-ajax-form[data-form-mode="register"]').each(function () {
            toggleBlogFields($(this));
        });
    }

    $(initRegistrationForms);
    // Elementor frontend re-renders widgets without full page reload
    $(window).on('elementor/frontend/init', function () {
        if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
            elementorFrontend.hooks.addAction('frontend/element_ready/emha-simple-form.default', function ($scope) {
                var $form = $scope.find('.emha-ajax-form');
                if ($form.length) {
                    toggleBlogFields($form);
                }
            });
        }
    });

    // Also stop button click from bubbling oddly in some themes
    $(document)
        .off('click.emhaForm', '.emha-ajax-form .emha-form-submit-btn')
        .on('click.emhaForm', '.emha-ajax-form .emha-form-submit-btn', function (e) {
            // Let the submit event handle it; just ensure type=submit forms work.
            var $form = $(this).closest('form');
            if ($form.hasClass('emha-form-loading')) {
                e.preventDefault();
                return false;
            }
        });
})(jQuery);
