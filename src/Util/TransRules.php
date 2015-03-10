<?php

namespace App\Util;

/**
 * TRANSFORMATION RULES:
 *   eventualFootnote
 *   footnote
 *   lightboxTextIcon
 *   popoverLinkIcon
 *   soundIcon
 *   soundPopoverLinkIcon
 *   tooltipIcon
 *   wikipediaFootnote
 *   wikipediaLinkIcon
 *
 * LOCAL TRANSFORMATIONS:
 *   format
 *   imageUrl
 *   soundUrl
 *   wikipediaUrl
 */
trait TransRulesTrait
{

//////////////////////////
// TRANSFORMATION RULES //
//////////////////////////

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
 * Data: term, url (mandatory) and caption (optional)
 */
function lightboxTextIcon($e)
{
    return sprintf(
        '<a href="%s" data-lightbox="global" data-title="%s">%s '.
            '<i class="fa fa-camera-retro"></i>'.
        '</a>',
        $this->imageUrl($e['url']), @ $e['caption'], $e['term']
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
 *       Then  file = term = $e
 *       Else  $e has 'file' and 'term' keys
 */
function soundIcon($e)
{
    $file = is_string($e) ? $e : $e['file'];
    $term = is_string($e) ? $e : $e['term'];

    return sprintf(
        '%s<sup class="fa fa-music small" data-sound="%s"></sup>',
        $term, $this->soundUrl($file)
    );
}

/**
 * Data: file, term (mandatory) and description (optional)
 */
function soundPopoverLinkIcon($e)
{
    $description = htmlspecialchars(
        $this->format(@ $e['description']), ENT_QUOTES
    );

    return sprintf(
        '<a tabindex="0" data-toggle="popover" data-content="%s" data-sound="%s" data-type="long">%s '.
            '<i class="fa fa-bell-o"></i>'.
        '</a>',
        $description, $this->soundUrl($e['file']),  $e['term']
    );
}

/**
 * Data: content and term (mandatory)
 */
function tooltipIcon($e)
{
    return sprintf(
        '%s<sup class="fa fa-comment-o small" data-toggle="tooltip" title="%s"></sup>',
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
        '<a href="%s" class="wikipedia" target="_blank">%s '.
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

/**
 * Transform:
 *
 *  -> 3 "\n" by 2 <br>
 *  -> 2 "\n" by 1 <br>
 *  -> 1 "\n" by one space
 *
 *  -> <url>http://my-website.com|My site</url>
 *      into <a href="http://my-website.com" target="_blank">My site</a>
 *
 *  -> <url>my-website.com</url>
 *      into <a href="http://my-website.com" target="_blank">my-website.com</a>
 *
 *  -> <wp>Page|My page</wp>
 *      into <a href="https://fr.wikipedia.org/wiki/Page" target="_blank">My page</a>
 *
 *  -> <wp>Page</wp>
 *      into <a href="https://fr.wikipedia.org/wiki/Page" target="_blank">Page</a>
 */
function format($c)
{
    // nl2br
    $c = str_replace(["\n\n\n", "\n\n", "\n"], ['<br><br>', '<br>', ' '], $c);

    // URL
    $c = preg_replace('|<url>(.*)\|(.*)</url>|U', '<a href="$1"        target="_blank">$2</a>', $c);
    $c = preg_replace('|<url>(.*)</url>|U'      , '<a href="http://$1" target="_blank">$1</a>', $c);

    // Wikipedia
    $wpUrl = function ($m) {
        return sprintf('<a href="%s" target="_blank">%s</a>',
            $this->wikipediaUrl($m[1]), @ $m[2] ?: $m[1]
        );
    };
    $c = preg_replace_callback('|<wp>(.*)\|(.*)</wp>|U', $wpUrl, $c);
    $c = preg_replace_callback('|<url>(.*)</url>|U'    , $wpUrl, $c);

    return $c;
}

function imageUrl($url)
{
    $prefix = strtok($url, '/');
    $file   = strtok('');

    switch ($prefix) {
        case 'anarchos': return "http://anarchos-semitas.net/media/web/$file.jpeg";
        case 'local'   : return "/texts/$this->slug/$file";
        default        : return $url;
    }
}

function soundUrl($file)
{
    return "/texts/$this->slug/$file";
}

function wikipediaUrl($page)
{
    return "https://fr.wikipedia.org/wiki/$page";
}

}/*END OF TRAIT*/
