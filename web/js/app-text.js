/*global $, L, document, window */
/*global afrikapiePath, bingMapsAPIKey, currentPoint, textPoints */

(function () {
    'use strict';

    /////////////////////
    //     MAPPING     //
    /////////////////////

    var i,

        // Map definition
        map = L.easyMap('map', {
            baseLayers : {
                enabled : ['BingAerial'],
                first   : 'Landscape',
                options : {bingMapsAPIKey : bingMapsAPIKey}
            },
            center   : currentPoint,
            controls : {fullscreen: true},
            zoom     : 10
        }),

        // Icon definitions
        currentIcon = L.divIcon({
            className : 'current-text-icon',
            html      : '<i class="fa fa-circle"></i>',
            iconSize  : L.point(30, 30)
        }),
        otherIcon = L.divIcon({
            className : 'other-text-icon',
            iconSize  : L.point(20, 20)
        });

    // Path of the Afrikapié travel
    L.polyline(afrikapiePath, {
        clickable : false,
        color     : 'brown',
        opacity   : 0.7,
        weight    : 8
    }).addTo(map);

    // Marker for the current text
    L.marker(currentPoint, {clickable: false, icon: currentIcon}).addTo(map);

    // Markers for all other texts
    function createOtherTextMarker(slug) {
        L.marker(textPoints[slug], {icon: otherIcon, riseOnHover: true})
            .bindLabel('Aller au texte « ' + slug + ' »')
            .on('click', function () { window.location.assign('/' + slug); })
            .addTo(map);
    }

    for (i in textPoints)
        if (textPoints[i] !== currentPoint)
            createOtherTextMarker(i);

    ////////////////////////
    // OTHER THAN MAPPING //
    ////////////////////////

    /**
     * On mouseenter, hide the header content.
     * Useful to see the full background picture.
     */
    var header = document.querySelector('body > header'),
        inner  = $(header.querySelector('.inner'));
    header.onmouseenter = function () { inner.fadeOut(); };
    header.onmouseleave = function () { inner.fadeIn(); };

}());
