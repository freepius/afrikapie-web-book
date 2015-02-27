<?php

namespace App\Util;

use Symfony\Component\Yaml;

class AfrikapieText
{
    /**
     * The directory where to search the files
     * (texts, "simple text" metadata and "enhanced text" metadata)
     */
    protected $dir;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    public function findAndTransform($name)
    {
        $textPath     = "{$this->dir}/md/$name.md";
        $simplePath   = "{$this->dir}/simple/$name.yml";
        $enhancedPath = "{$this->dir}/enhanced/$name.yml";

        $text             = self::readfile($textPath);
        $simpleMetadata   = self::readfile($simplePath);
        $enhancedMetadata = self::readfile($enhancedPath);

        // Parse the 2 Yaml files
        $parser           = new Yaml\Parser;
        $simpleMetadata   = $parser->parse($simpleMetadata);
        $enhancedMetadata = $parser->parse($enhancedMetadata);

        // Default values for some metadata
        $defaultMetadata =
        [
            'intro' => null,
            'image' => $name,
            'next'  => date('Y-m-d', strtotime("$name +1 day")),
            'prev'  => date('Y-m-d', strtotime("$name -1 day")),
        ];

        return $simpleMetadata + $defaultMetadata +
        [
            'simple'   => self::transformSimple($text, $simpleMetadata),
            'enhanced' => self::transformEnhanced($text, $enhancedMetadata),
        ];
    }

    /**
     * Check if a file is readable (if not, throw a \RuntimeException).
     * Then, open it and return its content.
     *
     * @param  string $path
     * @return string
     */
    protected static function readfile($path)
    {
        if (!is_readable($path) || !is_file($path)) {
            throw new \RuntimeException("Unable to open the file \"$path\"");
        }
        return file_get_contents($path);
    }

    protected static function transformSimple($text, array $metadata)
    {
        return $text;
    }

    protected static function transformEnhanced($text, array $metadata)
    {
        return $text;
    }
}
