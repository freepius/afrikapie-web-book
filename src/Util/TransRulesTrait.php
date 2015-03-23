<?php

namespace App\Util;

/**
 * TRANSFORMATION RULES:
 *   collapsibleTextLinkIcon
 *   eventualFootnote
 *   footnote
 *   gallery
 *   linkIcon
 *   lightboxTextIcon
 *   popoverLinkIcon
 *   soundIcon
 *   soundPopoverLinkIcon
 *   tooltipIcon
 *   wikipediaFootnote
 *   wikipediaLinkIcon
 *
 * LOCAL TRANSFORMATIONS:
 *   imageUrl
 *   licenseUrl
 *   soundUrl
 *   wikipediaUrl
 *   format
 *   slugify
 */
trait TransRulesTrait
{

//////////////////////////
// TRANSFORMATION RULES //
//////////////////////////

/**
 * Data: file, marker, term (mandatory) and caption (optional)
 */
function collapsibleTextLinkIcon($e)
{
    $id     = uniqid();
    $linkId = "link_$id";
    $textId = "text_$id";
    $text   = static::readfile($this->dir.'/'.$this->slug.'/'.$e['file']);

    /**
     * 1) Link to open/close the collapsible text + to go on it.
     * 2) An eventual caption (as a tooltip).
     */
    $this->replaceTerm($e, function ($e) use ($linkId, $textId) {
        return sprintf(
            '<a href="#%1$s" id="%2$s" aria-expanded="false" aria-controls="%1$s" '.
                'data-toggle="collapse" data-target="#%1$s" data-title="%4$s"'.
            '>'.
                '%3$s <i class="fa fa-file-text-o small"></i>'.
            '</a>',
            $textId, $linkId, $e['term'], @ $e['caption']
        );
    });

    /**
     * 1) The text in a .collapse <div>.
     * 2) Two links to close the ".collapse <div>" + to go on the "parent link"
     */
    $this->putAtMarker($e, function ($e) use ($linkId, $textId, $text) {
        return
<<<EOT
<aside id="$textId" class="collapse">
    <div class="well clearfix">

        <a class="close" href="#$linkId" data-toggle="collapse" data-target="#$textId" aria-controls="$textId">
            <i class="fa fa-close"></i>
        </a>

        <div markdown="1">\n\n$text\n\n</div>

        <a class="close" href="#$linkId" data-toggle="collapse" data-target="#$textId" aria-controls="$textId">
            Fermer <i class="fa fa-close"></i>
        </a>

    </div>
</aside>
EOT;
    });
}

/**
 * Data: content, term (mandatory) and footnote (optional)
 * Make a footnote only if $e['footnote'] exists and is true.
 */
function eventualFootnote($e)
{
    if (@ $e['footnote']) { $this->footnote($e); }
}

/**
 * Data: content and term (mandatory)
 */
function footnote($e)
{
    // the reference
    $this->replaceTerm($e, function ($e) {
        return sprintf('%1$s[^%1$s]', $e['term']);
    });

    // the content
    $content = $e['content'];
    $content = is_callable($content) ? $content($e) : $content;

    // the footnote
    $this->text .= sprintf("[^%s]: %s\n", $e['term'], $this->format($content));
}

/**
 * Data: files (mandatory)
 */
function gallery($e)
{
    $out     = '';
    $files   = $e['files'];
    $colSize = (int) floor(12 / count($files));

    foreach ($files as $file) {
        $out .= sprintf(
            '<div class="col-sm-%s">'.
                '<a href="%2$s" data-lightbox="global">'.
                    '<img src="%2$s" class="img-responsive">'.
                '</a>'.
            '</div>',
            $colSize, $this->imageUrl($file)
        );
    }

    return '<div class="gallery row">'.$out.'</div>';
}

/**
 * Data: term and url (mandatory)
 */
function linkIcon($e)
{
    return sprintf(
        '<a href="%s" class="unbreak" target="_blank">%s '.
            '<i class="fa fa-external-link small"></i>'.
        '</a>',
        $e['url'], $e['term']
    );
}

/**
 * Data:   If  $e is string
 *       Then  term = $e
 *       Else  $e has term (mandatory), caption and file (optional)
 *
 * If file is not defined, then file = local/slugify(term).jpg
 * Eg: if term = "my words", then file = "local/my-words.jpg"
 */
function lightboxTextIcon($e)
{
    if (is_string($e)) { $e = ['term' => $e]; }

    $caption = htmlspecialchars($this->format(@ $e['caption']), ENT_QUOTES);

    /**
     * Case of $e['file'] not defined:
     *   if   term = "the term to search"
     *   then file = "local/the-term-to-search.jpg"
     */
    $file = @ $e['file'] ?: ('local/'.$this->slugify($e['term']).'.jpg');

    return sprintf(
        '<a href="%s" data-lightbox="global" data-title="%s">%s '.
            '<i class="fa fa-camera-retro"></i>'.
        '</a>',
        $this->imageUrl($file), $caption, $e['term']
    );
}

/**
 * Data: content, term (mandatory) and title (optional)
 */
function popoverLinkIcon($e)
{
    $content = htmlspecialchars($this->format($e['content']), ENT_QUOTES);
    $title   = htmlspecialchars(@ $e['title']               , ENT_QUOTES);

    return sprintf(
        '<a tabindex="0" data-toggle="popover" title="%s" data-content="%s">%s '.
            '<i class="fa fa-info-circle"></i>'.
        '</a>',
        $title, $content, $e['term']
    );
}

/**
 * Data:   If  $e is string
 *       Then  term = $e and file = slugify($e)
 *       Else  $e has 'file' and 'term' keys
 */
function soundIcon($e)
{
    $term = is_string($e) ? $e                 : $e['term'];
    $file = is_string($e) ? $this->slugify($e) : $e['file'];

    return sprintf(
        '<span class="unbreak">'.
            '%s<sup class="fa fa-music small" data-sound="%s"></sup>'.
        '</span>',
        $term, $this->soundUrl($file)
    );
}

/**
 * Data:   If  $e is string
 *       Then  term = $e
 *       Else  $e has term (mandatory), caption and file (optional)
 *
 * If file is not defined, then file = slugify(term)
 */
function soundPopoverLinkIcon($e)
{
    if (is_string($e)) { $e = ['term' => $e]; }

    $caption = htmlspecialchars(
        $this->format(@ $e['caption']), ENT_QUOTES
    );

    $file = $this->soundUrl(
        @ $e['file'] ?: $this->slugify($e['term'])
    );

    return sprintf(
        '<a tabindex="0" data-toggle="popover" data-content="%s" data-sound="%s" data-type="long">%s '.
            '<i class="fa fa-bell-o"></i>'.
        '</a>',
        $caption, $file,  $e['term']
    );
}

/**
 * Data: content and term (mandatory)
 */
function tooltipIcon($e)
{
    return sprintf(
        '<span class="unbreak">'.
            '%s<sup class="fa fa-comment-o small" data-title="%s"></sup>'.
        '</span>',
        $e['term'], $e['content']
    );
}

/**
 * Data:   If  $e is string
 *       Then  page = term = $e
 *       Else  $e has 'page' and 'term' keys
 */
function wikipediaFootnote($e)
{
    if (is_string($e)) {
        $e = ['page' => $e];
        $e['term'] =& $e['page'];
    }

    $e['content'] = function ($e) {
        return sprintf("Voir l'article Wikipedia <wp>%s|%s</wp>.",
            $e['page'], $e['term']
        );
    };

    $this->footnote($e);
}

/**
 * Data:   If  $e is string
 *       Then  page = term = $e
 *       Else  $e has 'page' and 'term' keys
 */
function wikipediaLinkIcon($e)
{
    $page = is_string($e) ? $e : $e['page'];
    $term = is_string($e) ? $e : $e['term'];

    return sprintf(
        '<a href="%s" class="unbreak" target="_blank">%s '.
            '<span class="fa-stack" style="font-size: 0.6em;">'.
                '<i class="fa fa-square-o fa-stack-2x"></i>'.
                '<i class="fa fa-stack-1x">W</i>'.
            '</span>'.
        '</a>',
        $this->wikipediaUrl($page), $term
    );
}


///////////////////////////
// LOCAL TRANSFORMATIONS //
///////////////////////////

function imageUrl($file)
{
    $namespace = strtok($file, '/');
    $filename  = strtok('');

    switch ($namespace) {
        case 'anarchos': return "http://anarchos-semitas.net/media/web/$filename.jpeg";
        case 'local'   : return "/texts/$this->slug/$filename";
        default        : return $file;
    }
}

function licenseUrl($license)
{
    // The Creative Commons licenses
    if ('CC' === substr($license, 0, 2))
    {
        list($_, $license, $version) = explode(' ', $license);

        return sprintf(
            '//creativecommons.org/licenses/%s/%s/deed.fr',
            strtolower($license), $version
        );
    }

    return '';
}

function soundUrl($file)
{
    return "/texts/$this->slug/$file";
}

function wikipediaUrl($page)
{
    return "https://fr.wikipedia.org/wiki/$page";
}

/**
 * Transform:
 *
 *  nl2br:
 *  ------
 *  -> 3 "\n" into 2 <br>
 *  -> 2 "\n" into 1 <br>
 *  -> 1 "\n" into 1 space
 *
 *  Image:
 *  ------
 *  -> <img>local/my-img.jpg</img>
 *     into <img src="/texts/2013-10-03/my-img.jpg" class="img-responsive">
 *
 *  URL:
 *  ----
 *  -> <url>http://my-website.com|My site</url>
 *     into <a href="http://my-website.com" target="_blank">My site</a>
 *
 *  -> <url>my-website.com</url>
 *     into <a href="http://my-website.com" target="_blank">my-website.com</a>
 *
 *  Wikipedia:
 *  ----------
 *  -> <wp>Page|My page</wp>
 *     into <a href="https://fr.wikipedia.org/wiki/Page" target="_blank">My page</a>
 *
 *  -> <wp>Page</wp>
 *     into <a href="https://fr.wikipedia.org/wiki/Page" target="_blank">Page</a>
 *
 *  Copyright (Flickr and Wikimedia Commons):
 *  -----------------------------------------
 *  -> <copy=wc>The author|CC BY-SA 3.0|The-file.jpg</copy>
 *     into <small>
 *              &copy; The author
 *              – <a href="//creativecommons.org/licenses/by-sa/3.0/deed.fr" target="_blank">CC BY-SA 3.0</a>
 *              – <a href="//commons.wikimedia.org/wiki/The-file.jpg" target="_blank">via Wikimedia Commons</a>
 *          </small>
 */
function format($c)
{
    // nl2br
    $c = str_replace(["\n\n\n", "\n\n", "\n"], ['<br><br>', '<br>', ' '], $c);

    // Image
    $c = preg_replace_callback('|<img>(.*)</img>|U', function ($m) {
        return '<img src="'.$this->imageUrl($m[1]).'" class="img-responsive">';
    }, $c);

    // URL
    $c = preg_replace('|<url>(.*)\|(.*)</url>|U', '<a href="$1"        target="_blank">$2</a>', $c);
    $c = preg_replace('|<url>(.*)</url>|U'      , '<a href="http://$1" target="_blank">$1</a>', $c);

    // Wikipedia
    $wpUrl = function ($m) {
        return sprintf('<a href="%s" target="_blank">%s</a>',
            $this->wikipediaUrl($m[1]), @ $m[3] ?: $m[1]
        );
    };
    $c = preg_replace_callback('|<wp>(.*)(\|(.*))?</wp>|U', $wpUrl, $c);

    // Copyright (Flickr and Wikimedia Commons)
    $c = preg_replace_callback('|<copy=(.*)>(.*)\|(.*)\|(.*)</copy>|U', function ($m) {

        list($_, $type, $author, $license, $file) = $m;

        switch ($type) {
            case 'fk': $text = 'via Flickr';
                       $url = "//www.flickr.com/photos/$file/in/photostream/";
                       break;
            case 'wc': $text = 'via Wikimedia Commons';
                       $url = "//commons.wikimedia.org/wiki/$file";
                       break;
            default:   return;
        }

        return sprintf(
            '<small>'.
                '&copy; %s'.
                ' – <a href="%s" target="_blank">%s</a>'.
                ' – <a href="%s" target="_blank">%s</a>'.
            '</small>',
            $author, $this->licenseUrl($license), $license, $url, $text
        );
    }, $c);

    return $c;
}

/**
 * Modify a string to remove all non ASCII characters and spaces,
 * and to put ASCII characters in lowercase.
 */
function slugify($text)
{
    // replace non letter or digits by -
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

    // trim
    $text = trim($text, '-');

    // transliterate
    if (function_exists('iconv'))
    {
        $text = iconv('utf-8', 'ascii//TRANSLIT', $text);
    }

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    return strtolower($text);
}

}/*END OF TRAIT*/
