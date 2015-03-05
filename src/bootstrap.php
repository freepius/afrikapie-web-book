<?php

define('APP'  , __DIR__);
define('ROOT' , dirname(APP));
define('CACHE', ROOT.'/cache');
define('WEB'  , ROOT.'/web');
define('DEBUG', true);

require ROOT.'/vendor/autoload.php';

$app = new \Freepius\Silex\Application;

$app['debug'] = DEBUG;

// from marie-c9(at)hotmail.fr account
$app['bing_maps_api_key'] = 'Aj6KakrmmAztTyUmXMu7wJnHplOuIYmGbXdd5brEpsAFk3nZL57oPmFgV47nMNHp';


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
    'asset.config'  => [
        'base.url' => $app['debug'] ? '//cdn.nomad/' : '//cdn.anarchos-semitas.net/',
    ],
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
