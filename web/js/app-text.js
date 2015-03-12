/*global $, L, buzz, document, window */
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

    ////////////////////
    //     SOUNDS     //
    ////////////////////

    function shortSound(el) {
        el.onmouseenter = el.sound.play;
    }

    function longSound(el) {
        // The <i> of the long sound
        var $icon = $(el.children[0]);

        el.sound.bind('ended', function () {
            $icon.removeClass('fa-bell-slash-o').addClass('fa-bell-o');
        });

        el.onclick = function () {
            // Turn the sound ON
            if (el.sound.isPaused()) {
                $icon.removeClass('fa-bell-o').addClass('fa-bell-slash-o');
                el.sound.play();
            // Turn the sound OFF
            } else {
                $icon.removeClass('fa-bell-slash-o').addClass('fa-bell-o');
                el.sound.pause();
            }
        };
    }

    function removeLongSound(el) {
        var newEl = document.createElement('span');
        newEl.innerHTML = el.text;
        el.parentNode.replaceChild(newEl, el);
    }

    /**
     * Activate the short and long SOUNDS (using Buzz library).
     */
    [].forEach.call(
        document.querySelectorAll('[data-sound]'),
        function (el) {
            var type = el.getAttribute('data-type');

            // If ogg and mp3 are not supported: remove the element!
            if (!buzz.isOGGSupported() && !buzz.isMP3Supported())
                return ('long' === type) ? removeLongSound(el) : el.remove();

            el.sound = new buzz.sound(
                el.getAttribute('data-sound'),
                {formats: ['ogg', 'mp3']}
            );

            if ('long' === type) longSound(el);
            else shortSound(el);
        }
    );

    ////////////////////////
    // OTHER INTERACTIONS //
    ////////////////////////

    /**
     * On mouseenter, hide the HEADER content.
     * Useful to see the full background picture.
     */
    var header = document.querySelector('body > header'),
        inner  = header.querySelector('.inner');
    header.onmouseenter = function () { inner.style.opacity = 0; };
    header.onmouseleave = function () { inner.style.opacity = 1; };

    /**
     * Handle the choice of DISPLAY
     */
    var displayLinks = $('#choose-display > a'),
        displayTabs  = $('[property="articleBody"] > div');

    displayLinks.click(function (e) {
        if ($(this).hasClass('active')) return;
        displayLinks.toggleClass('active');
        displayTabs.toggle(0);
    });

    /**
     * Activate the Bootstrap POPOVERS.
     */
    $('a[data-toggle="popover"]').popover({
        html      : true,
        placement : 'auto',
        trigger   : 'focus'
    });

    /**
     * Activate the Bootstrap TOOLTIPS.
     */
    $('[data-toggle="tooltip"]').tooltip();

}());
