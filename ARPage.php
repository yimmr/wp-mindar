<?php

namespace MyWPAR;

class ARPage
{
    protected $preload = [];

    public function buildObjectHTML($data)
    {
        $type = $data['type'];
        unset($data['type']);
        $attrStr = '';

        foreach ($data as $key => $value) {
            if (null !== $value) {
                $attrStr .= ' '.(true === $value ? $key : "{$key}=\"{$value}\"");
            }
        }

        return "<a-{$type}{$attrStr}></a-{$type}>";
    }

    public function getPageCurrentData()
    {
        global $wpdb;

        $data = ['items' => []];
        $sortcodeId = \get_option('pl_ar_current_id');
        $attrs = (array) \get_option('pl_ar_current_options', []);

        $table = $wpdb->prefix.'pl_ar_table';
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE shortcode_id={$sortcodeId}");

        if (0 == $count) {
            \wp_die('No item with id:'.$sortcodeId.' exists');
        }

        $rawData = $wpdb->get_row("SELECT markers,objects FROM {$table} WHERE shortcode_id={$sortcodeId}");
        $markers = $this->parseJSONColumn($rawData, 'markers');
        $objects = $this->parseJSONColumn($rawData, 'objects');

        // 提取公共属性
        foreach (['type', 'slatlong'] as $key) {
            if (isset($attrs[$key])) {
                $data[$key] = 'slatlong' === $key ? $this->parseLatLong($attrs[$key]) : $attrs[$key];
                unset($attrs[$key]);
            }
        }

        // 构建项目数据
        foreach ($markers as $key => $value) {
            if ($value && !empty($objects[$key])) {
                $data['items'][] = $this->makeItemData($objects[$key], $value, $attrs, $data['type'], $data);
            }
        }

        $data['preload'] = $this->preload;

        return \apply_filters('pl_wpar_page_current_data', $data);
    }

    protected function makeItemData($objectURL, $makerURL, $attrs = [], $type = '', &$data = [])
    {
        $ext = pathinfo($objectURL, PATHINFO_EXTENSION);
        $objectURL = \PL_AR_LINK.$objectURL;

        if ('gltf' == $ext) {
            $srcId = 'animated-asset-'.(count($this->preload) + 1);
            $this->preload[$srcId] = $objectURL;
        }

        switch ($ext) {
            case 'jpg':
            case 'png':
                $object = ['type' => 'image', 'src' => $objectURL];
                // if (isset($attrs['scale'])) {
                //     $object['autoscale'] = $attrs['scale'];
                //     unset($attrs['scale']);
                // }
                break;
            case 'gltf':
                $object = [
                    'type'            => 'entity',
                    'animation-mixer' => true,
                    'gltf-model'      => '#'.$srcId,
                    'position'        => '0 0 0.1',
                    'scale'           => '0.007 0.007 0.007',
                ];
                break;
            default:break;
        }

        if (isset($attrs['latlong'])) {
            $latlong = $this->parseLatLong($attrs['latlong']);
            $attrs['gps-entity-place'] = "longitude: {$latlong[1]}; latitude: {$latlong[0]};";
            unset($attrs['latlong']);
        }

        $makerExt = pathinfo($makerURL, PATHINFO_EXTENSION);

        if ('image' == $type) {
            $makerFileName = substr($makerURL, 0, strrpos($makerURL, '.'));
            $object = [
                'type'      => 'gltf-model',
                'src'       => $object['gltf-model'],
                'animation' => 'property: position; to: 0 0.1 0.1; dur: 1000; easing: easeInOutQuad; loop: true; dir: alternate',
            ];
            $data['target_src'] = \PL_AR_LINK.$makerFileName.'.mind';
        }

        $object = array_merge($object, $attrs);
        $marker = ['url' => \PL_AR_LINK.$makerURL, 'type' => 'patt' == $makerExt ? 'pattern' : ''];

        return compact('marker', 'object');
    }

    protected function parseLatLong($string)
    {
        return explode(',', $string) + [0, 0];
    }

    public function parseJSONColumn(&$data, $column, $default = [])
    {
        if (!empty($data->{$column})) {
            $string = $data->{$column};
            return json_decode(str_replace("'", '"', stripcslashes($string)), true) ?: $default;
        }

        return $default;
    }
}
