<?php

if (!empty($pageData['preload'])) {
    echo '<a-assets>';
    array_walk($pageData['preload'], function ($src, $id) {
        $type = 'item';
        if (is_array($src)) {
            $src = $src['src'];
            $type = $src['type'] ?? $type;
        }
        if ('image' == $type) {
            printf(' <img id="%s" src="%s" />', $id, $src);
        } else {
            printf('<a-asset-item id="%s" src="%s"></a-asset-item>', $id, $src);
        }
    });
    echo '</a-assets>';
}
