<?php

namespace Asmet\Breadcrumbs\Models;


use function method_exists;

class Breadcrumbs
{
    private $crumbs = []; // массив крошек

    /**
     * @param $type
     * @param null $obj
     * @return string
     */
    public function buildCrumbs($type, $obj = null)
    {
        $setting = config('breadcrumbs.' . $type);
        $crumb = [];

        switch ($setting['type']):
            case 'static':
                $crumb['name'] = $setting['name'];
                $crumb['url'] = url($this->getUrl($setting, $obj));
                $this->crumbs[] = $crumb;

                if (array_key_exists('parent', $setting)):
                    $this->previousCrumb($setting);
                endif;
                break;
            case 'dynamic':
                $name = $setting['name'];
                $alias_method = $setting['alias_method'] ?? 'scopeUrl';
                $crumb['name'] = $obj->$name;
                $crumb['url'] = '/' . $this->getUrl($setting, $obj, $alias_method);
                $this->crumbs[] = $crumb;

                if (array_key_exists('parent', $setting)):
                    $this->previousCrumb($setting, $obj);
                endif;
                break;
        endswitch;

        $crumbs = array_reverse($this->crumbs);
        $setting = config('breadcrumbs.setting');
        $separator = $setting['separator'];
        $markup = $setting['markup'];
        $bootstrap = $setting['bootstrap'];

        return view('breadcrumbs::breadcrumbs', compact('crumbs', 'separator', 'markup', 'bootstrap'))->render();
    }


    protected function getUrl($setting, $obj = null, $alias_method = 'scopeUrl')
    {
        if (array_key_exists('alias', $setting) && !empty($obj)):
            return method_exists($obj, $alias_method) ? $obj->$alias_method() : $obj->url();
        //return method_exists($obj, 'getAlias') ? $obj->getAlias() : '#';
        elseif (array_key_exists('route_name', $setting)):
            return route($setting['route_name'], $obj ?? []);
        elseif (array_key_exists('url', $setting)):
            return $setting['url'];
        endif;
    }

    protected function previousCrumb($setting, $obj = null)
    {
        if (array_key_exists('parent_type', $setting) && !empty($obj)):
            if ($setting['parent_type'] === 'relation' &&
                array_key_exists('method', $setting)
            ):
                $method = $setting['method'];
                if (method_exists($obj->$method, 'last')):
                    $this->buildCrumbs($setting['parent'], $obj->$method->last());
                else:
                    $this->buildCrumbs($setting['parent'], $obj->$method);
                endif;
            elseif ($setting['parent_type'] === 'table_column' &&
                array_key_exists('column', $setting)
            ):
                $column = $setting['column'];
                if (is_numeric($obj->$column) && $obj->$column != 0):
                    $this->buildCrumbs($setting['self'], $obj->find($obj->$column));
                else:
                    $this->buildCrumbs($setting['parent']);
                endif;
            endif;
        else:
            $this->buildCrumbs($setting['parent']);
        endif;
    }
}