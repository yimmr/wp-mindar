<?php

namespace MyWPAR;

class ARPage
{
    public const AR_TABLE = 'pl_ar_table';

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

    public function shortcodeExists($sortcodeId)
    {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}".static::AR_TABLE." WHERE shortcode_id={$sortcodeId}") > 0;
    }

    public function getObjects($sortcodeId)
    {
        global $wpdb;

        $rawData = $wpdb->get_row("SELECT markers,objects FROM {$wpdb->prefix}".static::AR_TABLE." WHERE shortcode_id={$sortcodeId}");
        $markers = $this->parseJSONColumn($rawData, 'markers');
        $objects = $this->parseJSONColumn($rawData, 'objects');

        return array_map(fn ($object) => ['marker' => array_shift($markers), 'object' => $object], $objects);
    }

    public function getPageCurrentData()
    {
        $data = [];
        $sortcodeId = \get_option('pl_ar_current_id');
        $attrs = (array) \get_option('pl_ar_current_options', []);

        if (!$this->shortcodeExists($sortcodeId)) {
            \wp_die('No item with id:'.$sortcodeId.' exists');
        }

        // 提取公共属性
        foreach (['type', 'slatlong'] as $key) {
            if (isset($attrs[$key])) {
                $data[$key] = 'slatlong' === $key ? $this->parseLatLong($attrs[$key]) : $attrs[$key];
                unset($attrs[$key]);
            }
        }

        $method = $data['type'];

        if (!method_exists($this, $method)) {
            \wp_die('Unsupported type:'.$method);
        }

        $this->{$method}($data, $this->getObjects($sortcodeId), $attrs);

        $data['preload'] = $this->preload;

        return \apply_filters('pl_wpar_page_current_data', $data);
    }

    public function face(&$data, $objects, $attrs = [])
    {
        $anchor = [];

        if (isset($attrs['anchor'])) {
            $anchor = explode(' ', $attrs['anchor']);
            unset($attrs['anchor']);
        }

        $data['items'] = $this->buildObjectsData($objects, $attrs);

        foreach ($data['items'] as $i => &$item) {
            $item['object']['animation'] = 'property: position; to: 0 0.1 0.1; dur: 1000; easing: easeInOutQuad; loop: true; dir: alternate';
            $item['object']['type'] = 'gltf-model';
            $item['object']['src'] = $item['object']['gltf-model'];
            unset($item['object']['gltf-model']);

            $item['anchorIndex'] = $anchor[$i] ?? 1;
        }
    }

    public function image(&$data, $objects, $attrs = [])
    {
        $useMindar = false;
        if (isset($attrs['mindar'])) {
            $useMindar = true;
            unset($attrs['mindar']);
        }

        if ($data['isMindar'] = $useMindar) {
            // 基本一样
            $this->face($data, $objects, $attrs);
            $item = reset($data['items']);
            $data['target_src'] = '';

            if ($item['marker'] && (!$item['marker']['type'] || 'image' == $item['marker']['type'])) {
                $target = substr($item['marker']['url'], 0, strrpos($item['marker']['url'], '.'));
                $data['target_src'] = $target.'.mind';
            }
        } else {
            $data['items'] = $this->buildObjectsData($objects, $attrs);
        }
    }

    public function marker(&$data, $objects, $attrs = [])
    {
        $data['items'] = $this->buildObjectsData($objects, $attrs);
    }

    public function location(&$data, $objects, $attrs = [])
    {
        if (isset($attrs['latlong'])) {
            $latlong = $this->parseLatLong($attrs['latlong']);
            $attrs['gps-entity-place'] = "longitude: {$latlong[1]}; latitude: {$latlong[0]};";
            unset($attrs['latlong']);
        }

        $data['items'] = $this->buildObjectsData($objects, $attrs);
    }

    protected function buildObjectsData(&$objects, $attrs = [])
    {
        $items = [];
        foreach ($objects as $arr) {
            $object = array_merge($this->parseObject($arr['object']), $attrs);
            $marker = [];

            if ($arr['marker']) {
                $makerExt = pathinfo($arr['marker'], PATHINFO_EXTENSION);
                $marker['url'] = \PL_AR_LINK.$arr['marker'];
                $marker['type'] = 'patt' == $makerExt ? 'pattern' : '';
            }

            $items[] = compact('marker', 'object');
        }
        return $items;
    }

    public function parseObject($object)
    {
        $ext = pathinfo($object, PATHINFO_EXTENSION);

        switch ($ext) {
            case 'jpg':
            case 'png':
                $object = ['type' => 'image', 'src' => $this->addPreload(\PL_AR_LINK.$object)];
                break;
            case 'gltf':
                $object = [
                    'type'       => 'entity',
                    'gltf-model' => '#'.$this->addPreload(\PL_AR_LINK.$object),
                ];
                break;
            default:
                $object = ['type' => 'html', 'content' => $object];
                break;
        }
        return $object;
    }

    public function addPreload($src)
    {
        $srcId = 'animated-asset-'.(count($this->preload) + 1);
        $this->preload[$srcId] = $src;
        return $srcId;
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
