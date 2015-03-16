/*global document, window */

(function () {
    "use strict";

    /**
     * Return the number of the upper ".slide" element.
     */
    function getUpperSlideNum() {

        var i, rect,
            upper = 0,
            slides = document.querySelectorAll('.slide');

        for (i = 0; i < slides.length - 1; i += 1) {
            rect = slides[i].getBoundingClientRect();

            if (rect.top + rect.height < 100) { upper = i + 1; }
        }
        return upper;
    }

    /**
     * Preload the body background image.
     */
    window.onload = function () {
        var i, bg,
            preload = document.createElement('div');

        preload.id = 'preload';
        document.body.appendChild(preload);

        for (i = 0; i < 5; i += 1) {
            bg = document.createElement('div');
            bg.style.background = 'url(header-' + i + '.jpg) no-repeat -9999px -9999px';
            preload.appendChild(bg);
        }

        setTimeout(function () { preload.remove(); }, 5000)
    };

    /**
     * Change the body background image, depending on slides position
     */
    document.addEventListener('DOMContentLoaded', function () {
        var body  = document.body,
            old_i = getUpperSlideNum();

        body.onscroll = function () {
            var i = getUpperSlideNum();

            if (i !== old_i) {
                old_i = i;
                body.style.backgroundImage = 'url(header-' + i + '.jpg)';
            }
        };
    });

}());
