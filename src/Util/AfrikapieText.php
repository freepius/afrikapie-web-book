<?php

namespace App\Util;

use Symfony\Component\Yaml;

class AfrikapieText
{
    use TransRulesTrait;

    protected $dir;
    protected $metadata;
    protected $originalText;
    protected $slug;
    protected $text;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    public function findAndTransform($slug)
    {
        $this->slug = $slug;

        $textPath     = "{$this->dir}/$slug/text.md";
        $metadataPath = "{$this->dir}/$slug/metadata.yml";

        $this->originalText = self::readfile($textPath)."\n\n";

        $this->metadata = (new Yaml\Parser)->parse(
            self::readfile($metadataPath)
        );

        // Default values for some metadata
        $defaultMetadata =
        [
            'intro' => null,
            'next'  => date('Y-m-d', strtotime("$slug +1 day")),
            'prev'  => date('Y-m-d', strtotime("$slug -1 day")),
        ];

        return $this->metadata + $defaultMetadata + [
            'slug'     => $slug,
            'simple'   => $this->transformSimple(),
            'enhanced' => $this->transformEnhanced(),
        ];
    }

    /**
     * Check if a file is readable (if not, throw a \RuntimeException).
     * Then, open it and return its content.
     *
     * @param  string $path
     *
     * @return string
     */
    protected static function readfile($path)
    {
        if (!is_readable($path) || !is_file($path)) {
            throw new \RuntimeException("Unable to open the file \"$path\"");
        }
        return file_get_contents($path);
    }

    protected function transformSimple()
    {
        $this->text = $this->originalText;

        $this->replaceCollection('comments'  , 'footnote');
        $this->replaceCollection('longnotes' , 'eventualFootnote');
        $this->replaceCollection('wikipedias', 'wikipediaFootnote');

        return $this->text;
    }

    protected function transformEnhanced()
    {
        $this->text = $this->originalText;

        $this->replaceTermCollection('comments'  , 'tooltipIcon');
        $this->replaceTermCollection('links'     , 'linkIcon');
        $this->replaceTermCollection('lightboxes', 'lightboxTextIcon');
        $this->replaceTermCollection('longnotes' , 'popoverLinkIcon');
        $this->replaceTermCollection('longsounds', 'soundPopoverLinkIcon');
        $this->replaceTermCollection('sounds'    , 'soundIcon');
        $this->replaceTermCollection('wikipedias', 'wikipediaLinkIcon');

        return $this->text;
    }

    /**
     * Replace the current text ($this->text)
     * by the application of a $callback function (see TransRulesTrait.php)
     * on each element of a $collection.
     */
    protected function replaceCollection($collection, $callback)
    {
        $collection = (array) @ $this->metadata[$collection];

        array_map([$this, $callback], $collection);
    }

    /**
     * In the current text ($this->text),
     * replace a $collection of terms ($this->metadata[$collection])
     * using the $callback function (see TransRulesTrait.php).
     */
    protected function replaceTermCollection($collection, $callback)
    {
        $collection = (array) @ $this->metadata[$collection];

        foreach ($collection as $e) {
            $this->replaceTerm($e, [$this, $callback]);
        }
    }

    /**
     * In the current text ($this->text),
     * replace a term (being $e or contained in $e)
     * using the $callback function (see TransRulesTrait.php).
     *
     * Note:
     *  1) If the term form is 'My term/2',
     *     only the 2nd occurrence of 'My term' will be replace!
     *
     *  2) $e is passed by reference. It can be modified!
     */
    protected function replaceTerm(& $e, $callback)
    {
        if (is_string($e)) $term =& $e;
        else               $term =& $e['term'];

        /**
         * Case 1: all occurrence of $term will be replace
         */
        if (false === strpos($term, '/')) {
            $this->text = str_replace($term, $callback($e), $this->text);
            return;
        }

        /**
         * Case 2: only the $nth occurrence of $term will be replace
         */
        list($term, $nth) = explode('/', $term, 2);

        $terms       = array_fill(1, $nth, "/$term/");
        $rplcs       = array_fill(1, $nth, '__TMP_REPLACEMENT__');
        $rplcs[$nth] = $callback($e);

        $this->text = preg_replace($terms, $rplcs, $this->text, 1);
        $this->text = str_replace('__TMP_REPLACEMENT__', $term, $this->text);
    }
}
