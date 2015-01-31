<?php

namespace App\Util;

use Freepius\Richtext;

class AfrikapieRichtext extends Richtext
{
    const TAGS_CONV = [
        'ar'    => '*<span dir="rtl">$1</span>*',
        'arl'   => '*$1*',
        'bird'  => '$1',
        'ci'    => '$1',
        'en'    => '*$1*',
        'flora' => '$1',
        'fp'    => '$1',
        'frt'   => '$1',
        'is'    => '**$1**',
        'la'    => '$1',
        'mc'    => '*$1*',
        'na'    => '$1',
        're'    => '$1',
        'title' => '## $1',
        'to'    => '$1',
    ];

    public function markdown($text)
    {
        $tags = array_map(
            function ($e) { return "/<$e>(.*)<\/$e>/U"; },
            array_keys(self::TAGS_CONV)
        );

        $replacements = array_values(self::TAGS_CONV);

        $text = preg_replace($tags, $replacements, $text);

        return parent::markdown($text);
    }
}
