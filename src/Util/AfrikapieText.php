<?php

namespace App\Util;

class AfrikapieText
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
        'la'    => '$1',
        'mc'    => '*$1*',
        'na'    => '$1',
        're'    => '$1',
        'to'    => '$1',
    ];

    /**
     * Transform 'mytag' into a regexp finding '<mytag>Value</mytag>'.
     */
    protected static function tagify($e)
    {
        return "|<$e>(.*)</$e>|U";
    }

    /**
     * From a $text (passed by reference),
     * find and return the value (or null) contained by a $tag.
     * Then, remove this $tag from the $text.
     */
    protected static function getter($tag, & $text)
    {
        $text = preg_replace_callback(
            self::tagify($tag),
            function ($m) use (& $value) { $value = @ $m[1]; return; },
            $text, 1
        );

        return $value;
    }

    public function findAndTransform($year, $month, $day)
    {
        $text = file_get_contents(__DIR__."/../Resources/views/texts/$year-$month/$year-$month-$day.md");

        // Retrieve and remove the "one shot" tags
        $oneShotInfo = array_fill_keys(['image', 'intro', 'next', 'prev', 'title'], null);

        foreach ($oneShotInfo as $tag => & $value) {
            $value = self::getter($tag, $text);
        }

        // Transform the "multiple occurrences" tags
        $tags = array_map([$this, 'tagify'], array_keys(self::TAGS_CONV));
        $rpls = array_values(self::TAGS_CONV);
        $text = preg_replace($tags, $rpls, $text);

        return $oneShotInfo + ['text' => $text];
    }
}
