<?php

/**
 * @param null $get_seo
 * @return array
 */
function get_seo($get_seo = null)
{
    if (isset($get_seo) && $seo = $get_seo->seo->first()):
        $array = [];
        $array['title'] = $seo->title;
        $array['description'] = $seo->description;
		$array['h1'] = $seo->menu;

        return $array;
    else:
        return config('seo.default');
    endif;
}