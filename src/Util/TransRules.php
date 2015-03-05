<?php
/**
 * Summary:
 *  -> lightboxTextIcon
 *  -> wikipediaFootnote
 *  -> wikipediaLinkIcon
 *  -> wikipediaUrl
 */

/**
 * Data: term, url (mandatory) and caption (optional)
 */
function lightboxTextIcon($e)
{
    return sprintf(
        '<a href="%s" data-lightbox="global" data-title="%s">%s '.
            ' <i class="fa fa-camera-retro"></i>'.
        '</a>',
        $e['url'], @ $e['caption'], $e['term']
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

    return
        str_replace($term, $term.'[^'.$term.']', $text)
        .
        sprintf(
            "[^%s]: Voir l'article Wikipedia <a href=\"%s\" target=\"_blank\">%s</a>.\n",
            $term, wikipediaUrl($page), $term
        );
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
        '<a href="%s" target="_blank">%s '.
            '<span class="fa-stack" style="font-size: 0.6em;">'.
                '<i class="fa fa-square-o fa-stack-2x"></i>'.
                '<i class="fa fa-stack-1x">W</i>'.
            '</span>'.
        '</a>',
        wikipediaUrl($page), $term
    );
}

function wikipediaUrl($page)
{
    return "https://fr.wikipedia.org/wiki/$page";
}
