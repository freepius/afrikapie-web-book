<?php
/**
 * Summary:
 *  -> eventualFootnote
 *  -> footnote
 *  -> lightboxTextIcon
 *  -> popoverLinkIcon
 *  -> tooltipIcon
 *  -> wikipediaFootnote
 *  -> wikipediaLinkIcon
 *
 *  -> specialFormat
 *  -> wikipediaUrl
 */

/**
 * Data: content, term (mandatory) and footnote (optional)
 * Make a footnote only if $e['footnote'] exists and is true.
 */
function eventualFootnote($text, $e)
{
    if (! @ $e['footnote']) return $text;
    return footnote($text, $e);
}

/**
 * Data: content and term (mandatory)
 */
function footnote($text, $e)
{
    $content = specialFormat($e['content']);
    $term    = $e['term'];

    return str_replace($term, $term.'[^'.$term.']', $text).
           '[^'.$term.']: '.$content."\n";
}

/**
 * Data: term, url (mandatory) and caption (optional)
 */
function lightboxTextIcon($e)
{
    $url = $e['url'];

    if ('anarchos/' === substr($url, 0, 9)) {
        $url = 'http://anarchos-semitas.net/media/web/'.substr($url, 9);
    }

    return sprintf(
        '<a href="%s" data-lightbox="global" data-title="%s">%s '.
            ' <i class="fa fa-camera-retro"></i>'.
        '</a>',
        $url, @ $e['caption'], $e['term']
    );
}

/**
 * Data: content, term (mandatory) and title (optional)
 */
function popoverLinkIcon($e)
{
    $content = str_replace(
        ["\n\n\n"  , "\n\n", "\n"],
        ['<br><br>', '<br>', ' '],
        $e['content']
    );
    $content = htmlspecialchars(specialFormat($content), ENT_QUOTES);
    $title   = htmlspecialchars(@ $e['title']          , ENT_QUOTES);

    return sprintf(
        '<a tabindex="0" data-toggle="popover" title="%s" data-content="%s">%s '.
            '<i class="fa fa-info-circle"></i>'.
        '</a>',
        $title, $content, $e['term']
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
function wikipediaFootnote($text, $e)
{
    $page = is_string($e) ? $e : $e['page'];
    $term = is_string($e) ? $e : $e['term'];

    return footnote($text, [
        'term'    => $term,
        'content' => sprintf("Voir l'article Wikipedia <url>%s|%s</url>.",
                             wikipediaUrl($page), $term),
    ]);
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
        '<a href="%s" target="_blank">%s '.
            '<span class="fa-stack" style="font-size: 0.6em;">'.
                '<i class="fa fa-square-o fa-stack-2x"></i>'.
                '<i class="fa fa-stack-1x">W</i>'.
            '</span>'.
        '</a>',
        wikipediaUrl($page), $term
    );
}

/**
 * Transform:
 *
 *  -> <url>http://my-website.com|My site</url>
 *      into <a href="http://my-website.com" target="_blank">My site</a>
 *
 *  -> <url>my-website.com</url>
 *      into <a href="http://my-website.com" target="_blank">my-website.com</a>
 */
function specialFormat($c)
{
    $c = preg_replace('|<url>(.*)\|(.*)</url>|U', '<a href="$1"        target="_blank">$2</a>', $c);
    $c = preg_replace('|<url>(.*)</url>|U'      , '<a href="http://$1" target="_blank">$1</a>', $c);

    return $c;
}

function wikipediaUrl($page)
{
    return "https://fr.wikipedia.org/wiki/$page";
}
