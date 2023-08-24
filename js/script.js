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
        const arSystem = sceneEl.systems['mindar-face-system'];
        console.log('Scene loaded.');
        document.querySelector('#start-button')?.addEventListener('click', () => {
            arSystem.start();
        });
        document.querySelector('#stop-button')?.addEventListener('click', () => {
            arSystem.stop();
        });
        switchCameraButton('#switch-camera-button', arSystem);
        takePhotoButton('#take-photo-button');
        recordVideoButton('#record-photo-button');
    });

    function switchCameraButton(selector, arSystem) {
        console.log(arSystem);
        if (!arSystem) return;

        const el = document.querySelector(selector);
        el?.addEventListener('click', function () {
            arSystem.switchCamera();
        });
    }

    function takePhotoButton(selector) {
        const el = document.querySelector(selector);
        el?.addEventListener('click', takePhoto);
    }

    function recordVideoButton(selector) {
        const el = document.querySelector(selector + '');
        if (!el) return;
        const rawText = el.textContent;
        const mediaRecorder = createVideoRecorder();
        el.addEventListener('click', function () {
            if (mediaRecorder.state === 'recording') {
                mediaRecorder.stop();
                el.textContent = 'Stop Recording';
            } else {
                mediaRecorder.start();
                el.textContent = rawText;
            }
        });
    }

    function takePhoto(scene) {
        const video = document.querySelector('video');

        if (video == null) return;

        const filename = 'photo.png';

        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        let width = video.clientWidth * 2;
        let height = video.clientHeight * 2;
        canvas.width = width;
        canvas.height = height;
        const videoStyle = window.getComputedStyle(video);
        const top = videoStyle.getPropertyValue('top');

        video.pause();
        context.drawImage(video, 0, parseFloat(top), width, height);
        context.drawImage(
            scene.components.screenshot.getCanvas('perspective'),
            0,
            0,
            width,
            height
        );

        if (window.navigator.msSaveOrOpenBlob) {
            var blobObject = canvas.msToBlob();
            window.navigator.msSaveOrOpenBlob(blobObject, filename);
        } else {
            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/png');
            link.download = filename;
            link.click();
        }

        video.play();
    }

    function createVideoRecorder() {
        const video = document.querySelector('video');

        if (video == null) return;

        const filename = 'video.mp4';
        const fileType = 'video/mp4';

        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        let width = video.clientWidth;
        let height = video.clientHeight;
        canvas.width = width;
        canvas.height = height;

        const mediaRecorder = new MediaRecorder(canvas.captureStream(), { mimeType: fileType });
        const chunks = [];

        navigator.mediaDevices.getUserMedia({ video: true }).then((stream) => {
            video.srcObject = stream;
            video.play();
            function render() {
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                requestAnimationFrame(render);
            }
            render();
        });

        // 监听录制数据事件
        mediaRecorder.ondataavailable = function (event) {
            chunks.push(event.data);
        };

        // 监听录制结束事件
        mediaRecorder.onstop = function () {
            const videoBlob = new Blob(chunks, { type: fileType });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(videoBlob);
            link.download = filename;
            link.click();
        };

        mediaRecorder.onstart = function () {};

        return mediaRecorder;
    }
});
