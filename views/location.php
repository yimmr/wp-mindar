<a-scene vr-mode-ui="enabled: false" arjs='sourceType: webcam; videoTexture: true; debugUIEnabled: false;'>
    <?php require_once __DIR__.'/assets.php'; ?>
    <?php foreach ($pageData['items'] as $item) {?>
    <?php echo $arPage->buildObjectHTML($item['object']); ?>
    <?php }?>
    <a-camera
              gps-camera<?php echo isset($pageData['slatlong']) ? "=\"simulateLatitude: {$pageData['slatlong'][0]}; simulateLongitude: {$pageData['slatlong'][1]}\"" : ''; ?>
        rotation-reader></a-camera>
</a-scene>