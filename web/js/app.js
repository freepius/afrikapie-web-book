/*global $, document */

(function () {
    "use strict";

    document.addEventListener('DOMContentLoaded', function () {

        /**
         * Activate the Bootstrap TOOLTIPS.
         */
        $('[data-title]:not([data-lightbox])').tooltip({
            container: 'body'
        });

    });

}());
