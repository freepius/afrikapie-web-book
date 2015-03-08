/*global $, L, document, window */
/*global afrikapiePath, currentPoint, textPoints */

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
                first   : 'Landscape'
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
        }),

        // Layer to group the markers
        markers = L.layerGroup().addTo(map);

    // Path of the Afrikapié travel
    L.polyline(afrikapiePath, {
        clickable : false,
        color     : 'brown',
        opacity   : 0.7,
        weight    : 8
    }).addTo(map);

    // Marker for the current text
    L.marker(currentPoint, {clickable: false, icon: currentIcon, riseOnHover: true})
        .addTo(markers);

    // Markers for all other texts
    function createOtherTextMarker(slug) {
        L.marker(textPoints[slug], {icon: otherIcon, riseOnHover: true})
            .bindLabel('Aller au texte « ' + slug + ' »')
            .on('click', function () { window.location.assign('/' + slug); })
            .addTo(markers);
    }

    for (i in textPoints)
        if (textPoints[i] !== currentPoint)
            createOtherTextMarker(i);

    // Under the zoom level 8, hide the markers
    map.on('zoomend', function () {
        if (map.getZoom() < 8) {
            if (map.hasLayer(markers))
                map.removeLayer(markers);
            return;
        }

        if (!map.hasLayer(markers))
            map.addLayer(markers);
    });

    ////////////////////////
    // OTHER THAN MAPPING //
    ////////////////////////

    /**
     * On mouseenter, hide the header content.
     * Useful to see the full background picture.
     */
    var header = document.querySelector('body > header'),
        inner  = header.querySelector('.inner');
    header.onmouseenter = function () { inner.style.opacity = 0; };
    header.onmouseleave = function () { inner.style.opacity = 1; };

    /**
     * Handle the choice of display
     */
    var displayLinks = $('#choose-display > a'),
        displayTabs  = $('#displays > div');

    displayLinks.click(function (e) {
        if ($(this).hasClass('active')) return;
        displayLinks.toggleClass('active');
        displayTabs.toggle(0);
    });

    /**
     * Activate the eventual Bootstrap popovers.
     */
    $('a[data-toggle="popover"]').popover({
        html      : true,
        placement : 'auto',
        trigger   : 'focus'
    });

    /**
     * Activate the eventual Bootstrap tooltips.
     */
    $('[data-toggle="tooltip"]').tooltip();

}());
