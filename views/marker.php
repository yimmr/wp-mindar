 <a-scene embedded vr-mode-ui="enabled: false"
          arjs="sourceType: webcam; debugUIEnabled: false; detectionMode: mono_and_matrix; matrixCodeType: 3x3;">
     <?php require_once __DIR__.'/assets.php'; ?>
     <?php foreach ($pageData['items'] as $item) {?>
     <a-marker type="<?php echo $item['marker']['type']; ?>"
               url="<?php echo $item['marker']['url']; ?>">
         <?php echo $arPage->buildObjectHTML($item['object']); ?>
     </a-marker>
     <?php }?>
     <a-entity camera></a-entity>
 </a-scene>