<?php

namespace App\Util;

use Symfony\Component\Yaml;

/**
 * PUBLIC:
 *   __construct
 *   findAndTransform
 *   readfile
 *
 * PROTECTED:
 *   defaultMetadata
 *   removeMarkers
 *   transformSimple
 *   transformEnhanced
 *   putAtMarkerCollection
 *   applyCollection
 *   replaceTermCollection
 *   putAtMarker
 *   replaceTerm
 */
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

        // Retrieve the "raw text" + the metadata
        $this->originalText = $this->readfile($slug, 'text.md')."\n\n";

        $this->metadata = (new Yaml\Parser)->parse(
            $this->readfile($slug, 'metadata.yml')
        );

        // Complete the metadata
        $this->metadata += ['slug' => $slug] + $this->defaultMetadata();

        // Apply the wanted transformations
        $out   = [];
        $trans = array_intersect(
            ['enhanced', 'simple'],
            (array) $this->metadata['trans']
        );

        foreach ($trans as $t) {
            $out[$t] = $this->{'transform'.ucfirst($t)}();
        }

        // Return the transformed texts + the metadata
        return $out + $this->metadata;
    }

    /**
     * Check if a file is readable (if not, throw a \RuntimeException).
     * Then, open it and return its content.
     *
     * @param  string $file  A file in "$this->dir/$slug" directory
     *
     * @return string
     */
    public function readfile($slug, $file)
    {
        $path = "{$this->dir}/$slug/$file";

        if (!is_readable($path) || !is_file($path)) {
            throw new \RuntimeException("Unable to open the file \"$path\"");
        }
        return file_get_contents($path);
    }

    /**
     * Return the default values:
     *  -> for some metadata
     *  -> for the modules activation
     */
    protected function defaultMetadata()
    {
        return [
            // for some metadata
            'intro'    => null,
            'next'     => date('Y-m-d', strtotime("$this->slug +1 day")),
            'prev'     => date('Y-m-d', strtotime("$this->slug -1 day")),
            'template' => 'main',
            'trans'    => ['enhanced', 'simple'],

            // for the modules activation
            'module' => [
                'audio' => true,
            ],
        ];
    }

    /**
     * In the current text, remove the markers
     * (ie, the tags <marker=An ID>).
     */
    protected function removeMarkers()
    {
        $this->text = preg_replace('|<marker=.*>|U', '', $this->text);
    }

    protected function transformSimple()
    {
        $this->text = $this->originalText;

        $this->applyCollection('comments'  , 'footnote');
        $this->applyCollection('longnotes' , 'eventualFootnote');
        $this->applyCollection('wikipedias', 'wikipediaFootnote');

        $this->removeMarkers();
        return $this->text;
    }

    protected function transformEnhanced()
    {
        $this->text = $this->originalText;

        $this->replaceTermCollection('comments'  , 'tooltipIcon');
        $this->putAtMarkerCollection('galleries' , 'gallery');
        $this->replaceTermCollection('links'     , 'linkIcon');
        $this->replaceTermCollection('lightboxes', 'lightboxTextIcon');
        $this->replaceTermCollection('longnotes' , 'popoverLinkIcon');
        $this->replaceTermCollection('longsounds', 'soundPopoverLinkIcon');
        $this->putAtMarkerCollection('photowalls', 'photoWall');
        $this->replaceTermCollection('sounds'    , 'soundIcon');
        $this->applyCollection      ('subtexts'  , 'collapsibleTextLinkIcon');
        $this->replaceTermCollection('wikipedias', 'wikipediaLinkIcon');

        $this->removeMarkers();
        return $this->text;
    }

    /**
     * In the current text ($this->text),
     * put some content on each marker of the $collection,
     * using the $callback function (see TransRulesTrait.php).
     */
    protected function putAtMarkerCollection($collection, $callback)
    {
        $collection = (array) @ $this->metadata[$collection];

        foreach ($collection as $e) {
            $this->putAtMarker($e, [$this, $callback]);
        }
    }

    /**
     * Apply a $callback function (see TransRulesTrait.php)
     * on each element of a $collection.
     */
    protected function applyCollection($collection, $callback)
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
     * put some content on a marker (contained in $e),
     * using the $callback function (see TransRulesTrait.php).
     */
    protected function putAtMarker($e, $callback)
    {
        $marker = "<marker={$e['marker']}>";
        $this->text = str_replace($marker, $callback($e)."\n".$marker, $this->text);
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
