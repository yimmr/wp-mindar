<?php
$target = $pageData['target_src'];
?>
<a-scene mindar-image="imageTargetSrc: <?php echo $target; ?>"
         mindar-face="autoStart: false" embedded color-space="sRGB"
         renderer="colorManagement: true, physicallyCorrectLights" vr-mode-ui="enabled: false"
         device-orientation-permission-ui="enabled: false">
    <?php require_once __DIR__.'/assets.php'; ?>
    <a-camera position="0 0 0" look-controls="enabled: false"></a-camera>
    <?php foreach ($pageData['items'] as $item) {?>
    <a-entity mindar-image-target="targetIndex: 0">
        <a-plane src="<?php echo $item['marker']['url']; ?>"
                 position="0 0 0" height="0.552" width="1" rotation="0 0 0"></a-plane>
        <?php echo $arPage->buildObjectHTML($item['object']); ?>
    </a-entity>
    <?php }?>
</a-scene>
<div id="ar-controls-container" class="ar-controls-container">
    <div class="buttons">
        <button id="switch-camera-button">Switch Camera</button>
    </div>
</div>