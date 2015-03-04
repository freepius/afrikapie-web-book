/*global $, L, asCarto, document, window, afrikapiePath, bingMapsAPIKey, currentPoint, textPoints */

(function () {
    "use strict";

    var map = L.easyMap('map', {
            baseLayers : {
                enabled : ['Bing'],
                first   : 'Landscape',
                options : {bingMapsAPIKey : bingMapsAPIKey}
            },
            center   : currentPoint,
            controls : {fullscreen: true, layers: true},
            zoom     : 10
        }),
        i,
        currentIcon = L.divIcon({
            iconSize  : L.point(30, 30),
            className : 'current-text-icon',
            html      : '<i class="fa fa-circle"></i>'
        }),
        otherIcon   = L.divIcon({iconSize: L.point(20, 20), className: 'other-text-icon'});

    function createOtherTextMarker(text) {
        L.marker(textPoints[text], {icon: otherIcon})
            .bindLabel('Aller au texte « ' + text + ' »')
            .on('click', function () { window.location.assign('/' + text); })
            .addTo(map);
    }

    // Path of the Afrikapié travel
    L.polyline(afrikapiePath, {
        clickable : false,
        color     : 'brown',
        opacity   : 0.7,
        weight    : 8
    }).addTo(map);

    // Marker for the current text point
    L.marker(currentPoint, {clickable: false, icon: currentIcon}).addTo(map);

    // Markers for all other texts
    for (i in textPoints) {
        if (textPoints[i] !== currentPoint) { createOtherTextMarker(i); }
    }

    /**
     * On mouseenter, hide the header content.
     * Useful to see the full background picture.
     */
    $('body > header').hover(
        function () { $(this).children('.inner').fadeOut(); },
        function () { $(this).children('.inner').fadeIn(); }
    );

    $(document).ready(function () {

    });

}());
