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

            if (rect.y + rect.height < 100) { upper = i + 1; }
        }
        return upper;
    }

    /**
     * Preload the body background image:
     * http://perishablepress.com/3-ways-preload-images-css-javascript-ajax/
     */
    window.onload = function () {
        document.getElementById("preload-1").style.background = "url(header-1.png) no-repeat -9999px -9999px";
        document.getElementById("preload-2").style.background = "url(header-2.png) no-repeat -9999px -9999px";
        document.getElementById("preload-3").style.background = "url(header-3.png) no-repeat -9999px -9999px";
        document.getElementById("preload-4").style.background = "url(header-4.png) no-repeat -9999px -9999px";
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
