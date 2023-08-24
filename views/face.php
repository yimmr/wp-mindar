<a-scene mindar-face embedded color-space="sRGB" renderer="colorManagement: true, physicallyCorrectLights"
         vr-mode-ui="enabled: false" device-orientation-permission-ui="enabled: false">
    <a-camera active="false" position="0 0 0"></a-camera>
    <a-entity mindar-face-target="anchorIndex: 1">
        <a-sphere color="green" radius="0.1"></a-sphere>
    </a-entity>
</a-scene>
<?php return; ?>
<a-scene mindar-face color-space="sRGB" renderer="colorManagement: true, physicallyCorrectLights"
         vr-mode-ui="enabled: false" device-orientation-permission-ui="enabled: false">
    <?php require_once __DIR__.'/assets.php'; ?>
    <a-camera position="0 0 0"></a-camera>
    <?php foreach ($pageData['items'] as $item) {?>
    <a-entity mindar-image-target="targetIndex: 0">
        <?php echo $arPage->buildObjectHTML($item['object']); ?>
    </a-entity>
    <?php }?>
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