<?php

use Asmet\Breadcrumbs\Models\Breadcrumbs;

/**
 * @param $type
 * @param null $obj
 * @return string
 */
function get_breadcrumbs($type, $obj = null)
{
    $breadcrumbs = new Breadcrumbs;

    return $breadcrumbs->buildCrumbs($type, $obj);
}