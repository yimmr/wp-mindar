<a-scene mindar-image="imageTargetSrc: <?php echo $pageData['target_src']; ?>;"
         color-space="sRGB" renderer="colorManagement: true, physicallyCorrectLights" vr-mode-ui="enabled: false"
         device-orientation-permission-ui="enabled: false">
    <?php require_once __DIR__.'/assets.php'; ?>
    <?php foreach ($pageData['items'] as $item) {?>
    <a-entity
              mindar-face-target="targetIndex: <?php echo $item['targetIndex'] ?? 0; ?>">
        <a-plane src="#card" position="0 0 0" height="0.552" width="1" rotation="0 0 0"></a-plane>
        <?php echo $arPage->buildObjectHTML($item['object']); ?>
    </a-entity>
    <?php }?>
    <a-camera position="0 0 0" look-controls="enabled: false"></a-camera>
</a-scene>