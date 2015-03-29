<?php

define('APP'  , __DIR__);
define('ROOT' , dirname(APP));
define('CACHE', ROOT.'/cache');
define('WEB'  , ROOT.'/web');

require ROOT.'/vendor/autoload.php';

/*
 * Include host-dependent configuration parameters
 * (with servernames, passwords...).
 */
require_once APP.'/load-config.php';

$app = new \Freepius\Silex\Application;

/* Locale of the application */
setlocale(LC_ALL, 'fr_FR.UTF-8');

/* Locale of the current request : always 'fr' */
$app['locale'] = 'fr';

/* debug */
$app['debug'] = DEBUG;

// from marie-c9(at)hotmail.fr account
$app['bing_maps_api_key'] = BING_MAPS_API_KEY;


/*************************************************
 * Register services
 ************************************************/

/* twig */
$app->register(new \Silex\Provider\TwigServiceProvider(), [
    'twig.path'    => [APP.'/Resources/views'],
    'twig.options' => ['cache' => DEBUG ? null : (CACHE.'/twig')],
]);

/* freepius/php-asset */
$app->register(new \Freepius\Pimple\Provider\AssetServiceProvider, [
    'asset.cdn.use' => ! $app['debug'],
    'asset.config'  => ['base.url' => BASE_URL_FOR_ASSET],
]);

/* freepius/php-richtext */
$app->register(new \Freepius\Pimple\Provider\RichtextServiceProvider);

/* Transformer for "Afrikapié format" */
$app['afrikapieText'] = function () {
    return new \App\Util\AfrikapieText(APP.'/Resources/texts');
};


/*************************************************
 * Define the routes
 ************************************************/

$app->mount('/', new \App\Controller\BaseController($app));


return $app;
