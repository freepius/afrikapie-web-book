<?php

namespace App\Util;

use Freepius\Richtext;

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
     * @param \Freepius\Richtext
     */
    protected $richtext;

    public function __construct(Richtext $richtext)
    {
        $this->richtext = $richtext;
    }

    public function transform($year, $month, $day)
    {
        $tagify = function ($e) { return "|<$e>(.*)</$e>|U"; };

        $text = file_get_contents(__DIR__."/../Resources/views/texts/$year-$month/$year-$month-$day.md");

        // Get the title
        $text = preg_replace_callback(
            $tagify('title'),
            function ($m) use (& $title) { $title = $m[1]; return; },
            $text, 1
        );

        // Get the introduction sentence
        $text = preg_replace_callback(
            $tagify('is'),
            function ($m) use (& $intro) { $intro = $m[1]; return; },
            $text, 1
        );

        // Transform the Afrikapié tags
        $tags = array_map($tagify, array_keys(self::TAGS_CONV));
        $rpls = array_values(self::TAGS_CONV);
        $text = preg_replace($tags, $rpls, $text);

        // Transform with Markdown and SmartyPants
        $text = $this->richtext->transform($text);

        return [
            'title' => $title,
            'intro' => $intro,
            'text'  => $text,
        ];
    }
}
