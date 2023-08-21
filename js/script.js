(function ($) {
    jQuery(document).ready(function () {
        $('.ar_button').click(function () {
            var ar_loading = '<div class="armodal"></div>';
            $('body').append(ar_loading);
            $('body').addClass('arloading');
            console.log('loading');
            var data = {
                action: 'pl_ar_current_option',
                security: pl_ar_ajax_params.pl_ar_nonce,
                selector: event.target.id,
                options: $(this).data(),
            };

            jQuery.post(pl_ar_ajax_params.ajaxurl, data, function (response) {
                if (response) {
                    $(location).attr('href', response);
                }
            });
        });
    });
})(jQuery);

document.addEventListener('DOMContentLoaded', function () {
    const sceneEl = document.querySelector('a-scene');

    if (sceneEl == null) return;

    sceneEl.addEventListener('loaded', function () {
        const arSystem = sceneEl.systems['mindar-image-system'];
        console.log('Scene loaded.');
        switchCameraButton('#switch-camera-button', arSystem);
    });

    function switchCameraButton(selector, arSystem) {
        if (!arSystem) return;
        const el = document.querySelector(selector);
        el?.addEventListener('click', function () {
            arSystem.switchCamera();
            console.log(arSystem, 111);
        });
    }
});
