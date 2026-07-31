(function ($) {
    const initSimpleForm = function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/emha-simple-form.default', function ($scope) {
            const $form = $scope.find('.emha-ajax-form');
            const $msg = $scope.find('.emha-form-response-msg');
            const $btn = $scope.find('.emha-form-submit-btn');

            $form.on('submit', function (e) {
                e.preventDefault();

                // Prevent double submissions
                if ($form.hasClass('emha-form-loading')) {
                    return;
                }

                $form.addClass('emha-form-loading');
                $msg.removeClass('emha-success emha-error').hide().text('');

                const formData = new FormData(this);
                formData.append('_wpnonce', emha_ajax.nonce);
                
                // Try to find the post ID from WordPress frontend or Elementor settings
                let postId = 0;
                if (typeof elementorFrontendConfig !== 'undefined' && elementorFrontendConfig.post && elementorFrontendConfig.post.id) {
                    postId = elementorFrontendConfig.post.id;
                } else if ($('body').hasClass('wp-admin')) {
                    // Inside editor
                    if (typeof elementor !== 'undefined' && elementor.config && elementor.config.document && elementor.config.document.id) {
                        postId = elementor.config.document.id;
                    }
                }
                
                if (postId) {
                    formData.append('post_id', postId);
                }

                $.ajax({
                    url: emha_ajax.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        $form.removeClass('emha-form-loading');
                        if (response.success) {
                            $msg.addClass('emha-success').text(response.data.message).fadeIn();
                            $form[0].reset();
                        } else {
                            $msg.addClass('emha-error').text(response.data.message || 'An error occurred.').fadeIn();
                        }
                    },
                    error: function () {
                        $form.removeClass('emha-form-loading');
                        $msg.addClass('emha-error').text('Network error. Please try again.').fadeIn();
                    }
                });
            });
        });
    };

    if (window.elementorFrontend) {
        initSimpleForm();
    } else {
        $(window).on('elementor/frontend/init', initSimpleForm);
    }
})(jQuery);
