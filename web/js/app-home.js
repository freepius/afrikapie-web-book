/*global $, document, navigator, setTimeout, window */

(function () {
    "use strict";

    /**
     * Browser detection
     */
    var userAgent = navigator.userAgent.toLowerCase(),
        browser = {
            version : (userAgent.match(/.+(?:rv|it|ra|ie|me)[\/: ]([\d.]+)/) || [])[1],
            chrome  : /chrome/.test(userAgent),
            safari  : /webkit/.test(userAgent) && !/chrome/.test(userAgent),
            opera   : /opera/.test(userAgent),
            msie    : /msie/.test(userAgent) && !/opera/.test(userAgent),
            mozilla : /mozilla/.test(userAgent) && !/(compatible|webkit)/.test(userAgent)
        };

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

        preload.id = 'preload-body';
        document.body.appendChild(preload);

        // There are 8 photos to load!
        for (i = 0; i < 8; i += 1) {
            bg = document.createElement('div');
            bg.style.background = 'url(/images/home-bg/' + i + '.jpg) no-repeat -9999px -9999px';
            preload.appendChild(bg);
        }

        setTimeout(function () { preload.remove(); }, 5000);
    };

    /**
     * WHEN THE DOM IS LOADED !
     */
    document.addEventListener('DOMContentLoaded', function () {

        /**
         * Change the body background image, depending on slides position
         */
        var body  = document.body,
            old_i = getUpperSlideNum();

        window.addEventListener('scroll', function () {
            var i = getUpperSlideNum();

            if (i !== old_i) {
                old_i = i;
                body.style.backgroundImage = 'url(images/home-bg/' + i + '.jpg)';
            }
        });

        /**
         * Activate the affix navbar (containing the important shortcuts).
         */
        $('#home > nav > ul').affix({
            offset: {
                top: function () { return $('#main').outerHeight(true); }
            }
        });

        /**
         * If user agent is Mozilla or Chrome, delete the "Notice for old navigators".
         */
        if (browser.mozilla || browser.chrome)
            $('#old-navigator-notice').remove();

    });

}());
