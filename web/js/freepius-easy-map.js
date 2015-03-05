/*global L */

(function (L) {
    'use strict';

    // "EASY MAP" CLASS DEFINITION
    // =============================

    L.EasyMap = function (id, options) {
        var baseLayerFirst, baseLayers, i, layerId, map, tmp = {},

            /////////////////////
            // Default options //
            /////////////////////

            o = {
                baseLayer : undefined, // alias of baseLayers.first
                center    : [0.0, 0.0],
                zoom      : 5,

                baseLayers: {
                    enabled : [],    // what base layers to enable? (empty => all)
                    extra   : {},    // extra base layers
                    first   : 'OSM', // default base layer
                    options : {}     // various options to init. the base layers (eg: a Bing API key)
                },

                controls: {
                    attribution : true,
                    fullscreen  : false,
                    layers      : true,
                    pan         : false,
                    scale       : false,
                    zoomslider  : false
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

        baseLayerFirst = o.baseLayer || o.baseLayers.first;
        baseLayers     = this.getBaseLayers(o.baseLayers.options);

        // add extra base layers
        for (i in o.baseLayers.extra)
            baseLayers[i] = o.baseLayers.extra[i];

        // select only some base layers
        if (o.baseLayers.enabled.length > 0) {

            // select the default one
            tmp[baseLayerFirst] = baseLayers[baseLayerFirst];

            // select the ones to enable
            for (i in o.baseLayers.enabled) {

                layerId = o.baseLayers.enabled[i];

                if (baseLayers[layerId])
                    tmp[layerId] = baseLayers[layerId];
            }

            baseLayers = tmp;
        }

        ///////////////////////////////////////
        // Init. and config. the Leaflet map //
        ///////////////////////////////////////

        o.layers = [baseLayers[baseLayerFirst]];

        // Add controls through options

        o.attributionControl = o.controls.attribution;
        o.fullscreenControl  = o.controls.fullscreen;
        o.panControl         = o.controls.pan;

        if (o.controls.zoomslider) {
            o.zoomsliderControl = true;
            o.zoomControl = false;
        }

        // Initialize a new Leaflet map
        map = L.map(id, o);

        // Remove the Leaflet copyright (too much space ; sorry)
        if (map.attributionControl)
            map.attributionControl.setPrefix('');

        // Add controls through functions

        if (o.controls.layers)
            L.control.layers(baseLayers).addTo(map);

        if (o.controls.scale)
            L.control.scale({maxWidth: 150}).addTo(map);

        return map;
    };

    L.EasyMap.BING_MAPS_API_KEY = undefined;

    L.EasyMap.prototype.getBaseLayers = function (options) {

        var bingMapsAPIKey = options.bingMapsAPIKey || L.EasyMap.BING_MAPS_API_KEY,

            layers = {
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

        if (L.bingLayer && bingMapsAPIKey)
            layers.BingAerial = L.bingLayer(bingMapsAPIKey);

        if (L.stamenTileLayer)
            layers.WaterColor = L.stamenTileLayer('watercolor');

        return layers;
    };

    L.easyMap = function (id, options) {
        return new L.EasyMap(id, options);
    };

}(L));
