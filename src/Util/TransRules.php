<?php

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
function wikipediaLinkIcon($e)
{
    $page = is_string($e) ? $e : $e['page'];
    $term = is_string($e) ? $e : $e['term'];

    return sprintf(
        '<a href="https://fr.wikipedia.org/wiki/%s" target="_blank">%s '.
            '<span class="fa-stack" style="font-size: 0.6em;">'.
                '<i class="fa fa-square-o fa-stack-2x"></i>'.
                '<i class="fa fa-stack-1x">W</i>'.
            '</span>'.
        '</a>',
        $page, $term
    );
}
