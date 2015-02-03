/*global $, document */

(function () {
    "use strict";

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
