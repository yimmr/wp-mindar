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
        recordVideoButton('#record-video-button');
    });

    function switchCameraButton(selector, arSystem) {
        if (!arSystem) return;

        const el = document.querySelector(selector);
        el?.addEventListener('click', function () {
            arSystem.switchCamera();
        });
    }

    function takePhotoButton(selector) {
        const el = document.querySelector(selector);
        el?.addEventListener('click', () => takePhoto(sceneEl));
    }

    function recordVideoButton(selector) {
        const el = document.querySelector(selector + '');
        if (!el) return;
        const rawText = el.textContent;
        let mediaRecorder;

        el.addEventListener('click', async function () {
            try {
                if (!mediaRecorder) {
                    mediaRecorder = await createVideoRecorder(sceneEl);
                }
                if (mediaRecorder.state === 'recording') {
                    mediaRecorder.stop();
                    el.textContent = rawText;
                    el.classList.remove('btn-red');
                } else {
                    mediaRecorder.start();
                    el.textContent = 'Stop Recording';
                    el.classList.add('btn-red');
                }
            } catch (error) {
                alert(error);
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

        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/png');
        link.download = filename;
        link.click();
        window.URL.revokeObjectURL(link.href);

        video.play();
    }

    async function createVideoRecorder(scene) {
        if (!navigator.mediaDevices) {
            throw new Error('Not support recording');
        }

        let videoType = '';
        if (
            !MediaRecorder.isTypeSupported((videoType = 'video/mp4')) &&
            !MediaRecorder.isTypeSupported((videoType = 'video/webm'))
        ) {
            throw new Error('Not support recording');
        }

        let recorder = null;

        function init() {
            const chunks = [];
            const video = document.querySelector('video');
            if (video == null) throw new Error('Media not found.');

            const stream = video.captureStream();
            const canvas = scene.components.screenshot.getCanvas('perspective');
            const canvasStream = canvas.captureStream();

            // const constraints = { audio: true };
            // const stream = await navigator.mediaDevices.getUserMedia(constraints);

            recorder = new MediaRecorder(
                [new MediaStream([...stream.getVideoTracks(), ...canvasStream.getVideoTracks()])],
                { mimeType: videoType }
            );

            recorder.addEventListener('dataavailable', function (e) {
                if (e.data.size > 0) chunks.push(e.data);
            });

            recorder.addEventListener('stop', function () {
                console.log('data available after MediaRecorder.stop() called.');

                const filename = prompt('Enter a name for your sound clip', 'video');
                const videoBlob = new Blob(chunks, { type: videoType });
                const url = URL.createObjectURL(videoBlob);
                const link = document.createElement('a');
                link.href = url;
                link.download = filename + '.' + videoType.split('/').pop();
                link.click();
                window.URL.revokeObjectURL(url);

                console.log('recorder stopped');
            });
        }

        return {
            start() {
                init();
                recorder.start();
                console.log(recorder.state);
                console.log('recorder started');
            },
            stop() {
                if (!recorder) return;
                recorder.stop();
                console.log(recorder.state);
                console.log('recorder stopped');
            },
            get state() {
                return recorder ? recorder.state : '';
            },
        };
    }
});
