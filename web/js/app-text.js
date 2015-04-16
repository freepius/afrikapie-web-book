/*global $, buzz, document */

(function () {
    'use strict';

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
     * WHEN THE DOM IS LOADED !
     */
    document.addEventListener('DOMContentLoaded', function () {

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
         * Equalize the heights of "marie drawing" and map
         */
        document.getElementById('map').style.height =
            document.getElementById('marie-img').clientHeight + 'px';

    });

}());
