<a-scene mindar-face="autoStart: true" embedded color-space="sRGB"
         renderer="colorManagement: true, physicallyCorrectLights" vr-mode-ui="enabled: false"
         device-orientation-permission-ui="enabled: false">
    <?php require_once __DIR__.'/assets.php'; ?>
    <?php foreach ($pageData['items'] as $item) {?>
    <a-entity mindar-image-target="targetIndex: 0">
        <?php echo $arPage->buildObjectHTML($item['object']); ?>
    </a-entity>
    <?php }?>
    <a-camera position="0 0 0" look-controls="enabled: false"></a-camera>
</a-scene>
<div id="ar-controls-container" class="ar-controls-container">
    <div class="buttons">
        <button id="start-button">Start</button>
        <button id="stop-button">Stop</button>
        <button id="switch-camera-button">Switch Camera</button>
    </div>
</div>