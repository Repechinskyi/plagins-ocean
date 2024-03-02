<?php

namespace Asmet\FriendlyURLs\Traits;

use Asmet\FriendlyURLs\Models\Alias;

trait AliasTrait
{
    public $alias_type = 'default';

    public function alias() {
        return $this->morphOne(Alias::class, 'aliasable');
    }

    public function scopeUrl() {
        return $this->alias ? $this->alias->url : '#OBJECT-ALIAS-DOES-NOT-EXIST';
    }
}