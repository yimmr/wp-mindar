<a-scene mindar-image="imageTargetSrc: <?php echo $pageData['target_src']; ?>"
         vr-mode-ui="enabled: false" device-orientation-permission-ui="enabled: false">
    <?php require_once __DIR__.'/assets.php'; ?>
    <?php foreach ($pageData['items'] as $item) {?>
    <a-entity
              mindar-face-target="targetIndex: <?php echo $item['targetIndex'] ?? 0; ?>">
        <?php echo $arPage->buildObjectHTML($item['object']); ?>
    </a-entity>
    <?php }?>
    <a-camera position="0 0 0" look-controls="enabled: false"></a-camera>
</a-scene>