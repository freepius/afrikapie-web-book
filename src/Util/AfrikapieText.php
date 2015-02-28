<?php

namespace App\Util;

use Symfony\Component\Yaml;

include __DIR__.'/TransRules.php';

class AfrikapieText
{
    /**
     * The directory where to search the files
     * (texts, "simple text" metadata and "enhanced text" metadata)
     */
    protected $dir;

    // Working attributes
    protected $simpleMetadata;
    protected $enhancedMetadata;
    protected $originalText;
    protected $text;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    public function findAndTransform($name)
    {
        $textPath     = "{$this->dir}/md/$name.md";
        $simplePath   = "{$this->dir}/simple/$name.yml";
        $enhancedPath = "{$this->dir}/enhanced/$name.yml";

        $this->originalText = self::readfile($textPath);
        $simpleMetadata     = self::readfile($simplePath);
        $enhancedMetadata   = self::readfile($enhancedPath);

        // Parse the 2 Yaml files
        $parser                 = new Yaml\Parser;
        $this->simpleMetadata   = $parser->parse($simpleMetadata);
        $this->enhancedMetadata = $parser->parse($enhancedMetadata);

        // Default values for some metadata
        $defaultMetadata =
        [
            'intro' => null,
            'image' => $name,
            'next'  => date('Y-m-d', strtotime("$name +1 day")),
            'prev'  => date('Y-m-d', strtotime("$name -1 day")),
        ];

        return $this->simpleMetadata + $defaultMetadata + [
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
        $this->metadata = $this->simpleMetadata;
        $this->text     = $this->originalText;

        return $this->text;
    }

    protected function transformEnhanced()
    {
        $this->metadata = $this->enhancedMetadata;
        $this->text     = $this->originalText;

        $this->replaceTermCollection('lightboxes', 'lightboxTextIcon');
        $this->replaceTermCollection('wikipedias', 'wikipediaLinkIcon');

        return $this->text;
    }

    /**
     * In the current text ($this->text),
     * replace a $collection of terms ($this->metadata[$collection])
     * using the $callback function (\App\Util\Trans\$callback).
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
     * using the $callback function (\App\Util\Trans\$callback).
     */
    protected function replaceTerm($toSearch, $callback)
    {
        $term = is_string($toSearch) ? $toSearch : $toSearch['term'];

        $this->text = str_replace($term, $callback($toSearch), $this->text);
    }
}
