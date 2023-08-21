<?php

use MyWPAR\ARPage;

require_once __DIR__.'/ARPage.php';

$arPage = new ARPage;
$pageData = $arPage->getPageCurrentData();
$type = $pageData['type'];

if (empty($type)) {
    wp_die('Server Error');
}

add_action('wp_ajax_pl_ar_new_page', 'pl_ar_new_page');
add_action('wp_ajax_nopriv_pl_ar_new_page', 'pl_ar_new_page');

add_action('wp_enqueue_scripts', function () use ($type) {
    switch ($type) {
        case 'image':
            // \wp_enqueue_script('aframe_min', PL_AR_LINK.'js/aframe-master.min.js', [], '1.3.0');
            // \wp_enqueue_script('aframe-ar-nft', PL_AR_LINK.'js/aframe-ar-nft.js');
            \wp_enqueue_script('aframe_min', PL_AR_LINK.'js/aframe-1.4.min.js', [], '1.4.2');
            \wp_enqueue_script('mindar-image-aframe', PL_AR_LINK.'js/mindar-face-aframe.prod.js');
            break;
        case 'location':
            \wp_enqueue_script('aframe_min', PL_AR_LINK.'js/aframe.min.js', [], '1.3.0');
            \wp_enqueue_script('aframe-look-at-component', PL_AR_LINK.'js/aframe-look-at-component.min.js', [], '0.8.0');
            \wp_enqueue_script('aframe-ar-nft', PL_AR_LINK.'js/aframe-ar-nft.js');
            break;
        case 'marker':
            \wp_enqueue_script('aframe_min', PL_AR_LINK.'js/aframe.min.js', [], '1.3.0');
            \wp_enqueue_script('aframe-ar', PL_AR_LINK.'js/aframe-ar.js');
            break;
        default:break;
    }

    \wp_enqueue_style('wp-mindar', PL_AR_LINK.'css/style.css');

    // wp_enqueue_script('aframe-extras', PL_AR_LINK.'js/aframe-extras.loaders.min.js');
    // wp_enqueue_script( 'aframe-resize', PL_AR_LINK.'js/resize.js' );
});

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <?php wp_head(); ?>
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <style>
        .arjs-loader {
            height: 100%;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .arjs-loader div {
            text-align: center;
            font-size: 1.25em;
            color: white;
        }
    </style>
</head>

<body style='margin: 0; overflow: hidden;'>
    <?php require_once 'views/'.$type.'.php'; ?>
</body>

</html>