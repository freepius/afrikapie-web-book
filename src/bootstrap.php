<?php

define('APP'  , __DIR__);
define('ROOT' , dirname(APP));
define('CACHE', ROOT.'/cache');
define('WEB'  , ROOT.'/web');
define('DEBUG', true);

require ROOT.'/vendor/autoload.php';

$app = new \Freepius\Application;

$app['locale'] = 'fr';

$app['debug'] = DEBUG;


/*************************************************
 * Register services
 ************************************************/

/* twig */
$app->register(new \Silex\Provider\TwigServiceProvider(), [
    'twig.path'    => [APP.'/Resources/views'],
    'twig.options' => ['cache' => DEBUG ? null : (CACHE.'/twig')],
]);

/* freepius/php-asset */
$app->register(new \Freepius\Asset\ServiceProvider(), [
    'asset.cdn.use' => ! $app['debug'],
    'asset.config'  => [
        'base.url' => $app['debug'] ? '//cdn.nomad/' : '//cdn.anarchos-semitas.net/',
    ],
]);

/**
 * freepius/php-richtext
 * with an extension of the Richtext class
 */
$app->register(new \Freepius\Pimple\Provider\RichtextProvider(), [
    'richtext' => function ($c) {
        return new \App\Util\AfrikapieRichtext($c['richtext.config']);
    },
]);


/*************************************************
 * Define the routes
 ************************************************/

$app->mount('/', new \App\Controller\BaseController($app));


return $app;
