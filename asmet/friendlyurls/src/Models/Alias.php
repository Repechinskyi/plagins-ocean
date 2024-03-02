<?php

namespace Asmet\FriendlyURLs\Models;

use Illuminate\Database\Eloquent\Model;
use Behat\Transliterator\Transliterator;
use DB;
use function is_object;

class Alias extends Model
{
    protected $fillable = ['url', 'raw_url'];
    protected $table = 'aliases';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function aliasable()
    {
        return $this->morphTo();
    }

    public function createUrl($obj)
    {
        $url = '';
        $settings = config('aliases.' . $obj->alias_type);

        //create url from settings
        foreach ($settings as $setting) {
            $url .= $this->createUrlPart($setting, $obj) . '/';
        }

        //delete trailing slash
        if (mb_substr($url, -1) == '/') {
            $url = mb_substr($url, 0, -1);
        }

        $url = preg_replace('|([/]+)|s', '/', $url);;


        if ($this->urlExist($url, $obj)) {
            $oldUrl = $url;
            for ($int = 1; $this->urlExist($oldUrl . "-$int", $obj); $int++) {}
            $url = $url . "-$int";
        }


        return $url;
    }

    private function urlExist($url, $obj)
    {
        if (is_object($obj->alias) && $obj->alias->url === $url) {
            return count(Alias::where('url', $url)->where('id', '!=', $obj->alias->id)->get());
        }else {
            return count(Alias::where('url', $url)->get());
        }
    }

    /**
     * create url part
     *
     *
     */
    private function createUrlPart($setting, $obj)
    {
        $urlPart = '';
        switch ($setting['type']) {
            case 'string':
                $urlPart = $setting['content'];
                break;
            case 'db':
                $column = $setting['column'];
                $query = DB::table($setting['table'])->select($column);


                switch ($setting['relation']):
                    case 'no':
                    case 'one':
                    case 'one_to_many':
                        if ( ! empty($setting['where'])):
                            $obj_column = $setting['where']['obj_column'];

                            if ($obj->$obj_column > 0):
                                $query->where($setting['where']['table_column'], $obj->$obj_column);

                                $urlPart = $query->first()->$column;
                            endif;
                        endif;
                        break;
                    case 'many_to_many':
                        $method = $setting['relation_param']['method'];
                        $urlPart = $obj->$method->first()->$column;
                        break;
                    case 'morph':
                    case 'morph_many':
                        $id = $setting['relation_param']['_id'];
                        $query->where($setting['relation_param']['able'] . '_id', $obj->$id)
                          ->where($setting['relation_param']['able'] . '_type', $setting['relation_param']['_type']);


                        if (is_object($query->first())):
                            $id = $query->first()->$column;
                            $sub_column = $setting['relation_param']['sub_column'];
                            $query = DB::table($setting['relation_param']['sub_table'])->select($sub_column)
                              ->where('id', $id);

                            $urlPart = $query->first()->$sub_column;
                        endif;
                        break;
                endswitch;


                if ($setting['translit']):
                    $urlPart = Transliterator::transliterate($urlPart);
                endif;

                break;

            case 'alias':
                $urlPart = Transliterator::transliterate($this->raw_url);
                break;
        }

        return $urlPart;
    }
}
