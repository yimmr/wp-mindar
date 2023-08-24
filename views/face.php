<a-scene mindar-face embedded color-space="sRGB" renderer="colorManagement: true, physicallyCorrectLights"
         vr-mode-ui="enabled: false" device-orientation-permission-ui="enabled: false">
    <a-assets>
        <a-asset-item id="glassesModel"
                      src="https://cdn.jsdelivr.net/gh/hiukim/mind-ar-js@1.2.2/examples/face-tracking/assets/glasses/scene.gltf"></a-asset-item>
    </a-assets>
    <a-camera active="false" position="0 0 0"></a-camera>
    <a-entity mindar-face-target="anchorIndex: 168">
        <a-gltf-model rotation="0 0 0" position="0 0 0" scale="0.01 0.01 0.01" src="#glassesModel"></a-gltf-model>
    </a-entity>
</a-scene>
<div id="ar-controls-container" class="ar-controls-container">
    <div class="buttons">
        <button id="take-photo-button">Take Photo</button>
        <button id="record-video-button">Record</button>
        <button id="start-button">Start</button>
        <button id="stop-button">Stop</button>
        <button id="switch-camera-button">Switch Camera</button>
    </div>
</div>
<canvas id="media-canvas"></canvas>