<?php

namespace App\Util;

use Symfony\Component\Yaml;

include __DIR__.'/TransRules.php';

class AfrikapieText
{
    protected $dir;
    protected $metadata;
    protected $originalText;
    protected $text;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    public function findAndTransform($slug)
    {
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

        $this->replaceCollection('wikipedias', 'wikipediaFootnote');

        return $this->text;
    }

    protected function transformEnhanced()
    {
        $this->text = $this->originalText;

        $this->replaceTermCollection('lightboxes', 'lightboxTextIcon');
        $this->replaceTermCollection('popovers'  , 'popoversLinkIcon');
        $this->replaceTermCollection('wikipedias', 'wikipediaLinkIcon');

        return $this->text;
    }

    /**
     * Replace the current text ($this->text)
     * by the application of a $callback function (see TransRules.php)
     * on each element of a $collection.
     */
    protected function replaceCollection($collection, $callback)
    {
        $collection = (array) @ $this->metadata[$collection];

        foreach ($collection as $e) {
            $this->text = $callback($this->text, $e);
        }
    }

    /**
     * In the current text ($this->text),
     * replace a $collection of terms ($this->metadata[$collection])
     * using the $callback function (see TransRules.php).
     */
    protected function replaceTermCollection($collection, $callback)
    {
        $collection = (array) @ $this->metadata[$collection];

        foreach ($collection as $toSearch) {
            $this->replaceTerm($toSearch, $callback);
        }
    }

    /**
     * In the current text ($this->text),
     * replace a term (contained in $toSearch)
     * using the $callback function (see TransRules.php).
     */
    protected function replaceTerm($toSearch, $callback)
    {
        $term = is_string($toSearch) ? $toSearch : $toSearch['term'];

        $this->text = str_replace($term, $callback($toSearch), $this->text);
    }
}
