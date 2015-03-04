/*global L */

(function (L) {
    'use strict';

    // "EASY MAP" CLASS DEFINITION
    // =============================

    var EasytMap = function (id, options) {
        var baseLayers, i, layerId, map, tmp = {},

            /////////////////////
            // Default options //
            /////////////////////

            o = {
                center : [0.0, 0.0],
                zoom   : 5,

                baseLayers: {
                    enabled : [],    // what base layers to enable? (default: all)
                    extra   : {},    // extra base layers
                    first   : 'OSM', // default base layer
                    options : {}     // various options to init. the base layers (eg: a Bing API key)
                },

                controls: {
                    fullscreen : false,
                    layers     : true,
                    pan        : false,
                    scale      : false,
                    zoomslider : false
                }
            };

        ////////////////////////////////////
        // Merge default and user options //
        ////////////////////////////////////

        for (i in options)
            if ('baseLayers' !== i && 'controls' !== i)
                o[i] = options[i];

        for (i in options.baseLayers || {})
            o.baseLayers[i] = options.baseLayers[i];

        for (i in options.controls || {})
            o.controls[i] = options.controls[i];

        /////////////////////////////////
        // Get the enabled base layers //
        /////////////////////////////////

        baseLayers = this.getBaseLayers(o.baseLayers.options);

        // add extra base layers
        for (i in o.baseLayers.extra)
            baseLayers[i] = o.baseLayers.extra[i];

        // select only some base layers
        if (o.baseLayers.enabled) {

            // select the default one
            tmp[o.baseLayers.first] = baseLayers[o.baseLayers.first];

            // select the ones to enable
            for (i in o.baseLayers.enabled) {

                layerId = o.baseLayers.enabled[i];

                if (baseLayers.hasOwnProperty(layerId))
                    tmp[layerId] = baseLayers[layerId];
            }

            baseLayers = tmp;
        }

        ///////////////////////////////////////
        // Init. and config. the Leaflet map //
        ///////////////////////////////////////

        o.layers = [baseLayers[o.baseLayers.first]];

        // Add ZoomSlider control through options
        if (o.controls.zoomslider) {
            o.zoomsliderControl = true;
            o.zoomControl = false;
        }

        // Initialize a new Leaflet map
        map = L.map(id, o);

        // Remove the Leaflet copyright (too much space ; sorry)
        if (map.attributionControl)
            map.attributionControl.setPrefix('');

        // Add controls

        if (o.controls.layers)
            L.control.layers(baseLayers).addTo(map);

        if (o.controls.pan)
            L.control.pan().addTo(map);

        if (o.controls.scale)
            L.control.scale({maxWidth: 150}).addTo(map);

        return map;
    };

    EasytMap.prototype.getBaseLayers = function (options) {

        var layers = {
            OSM: L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors, CC-BY-SA'
            }),
            Landscape: L.tileLayer('http://{s}.tile.thunderforest.com/landscape/{z}/{x}/{y}.png', {
                attribution: 'Tiles &copy; <a href="http://www.thunderforest.com/landscape/">Gravitystorm</a>' +
                             ' / map data <a href="http://osm.org/copyright">OpenStreetMap</a>'
            }),
            Outdoors: L.tileLayer('http://{s}.tile.thunderforest.com/outdoors/{z}/{x}/{y}.png', {
                attribution: 'Tiles &copy; <a href="http://www.thunderforest.com/outdoors/">Gravitystorm</a>' +
                             ' / map data <a href="http://osm.org/copyright">OpenStreetMap</a>'
            })
        };

        if (L.stamenTileLayer)
            layers.WaterColor = L.stamenTileLayer('watercolor');

        if (L.bingLayer && options.bingMapsAPIKey)
            layers.BingAerial = L.bingAerial(options.bingMapsAPIKey);

        return layers;
    };

    L.easyMap = function (id, options) {
        return new EasytMap(id, options);
    };

}(L));
