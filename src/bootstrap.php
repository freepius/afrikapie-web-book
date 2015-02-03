<?php

define('APP'  , __DIR__);
define('ROOT' , dirname(APP));
define('CACHE', ROOT.'/cache');
define('WEB'  , ROOT.'/web');
define('DEBUG', true);

require ROOT.'/vendor/autoload.php';

$app = new \Freepius\Silex\Application;

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
$app->register(new \Freepius\Asset\ServiceProvider, [
    'asset.cdn.use' => ! $app['debug'],
    'asset.config'  => [
        'base.url' => $app['debug'] ? '//cdn.nomad/' : '//cdn.anarchos-semitas.net/',
    ],
]);

/* freepius/php-richtext */
$app->register(new \Freepius\Pimple\Provider\RichtextProvider);

/* Transformer for "Afrikapié format" */
$app['afrikapieText'] = function () {
    return new \App\Util\AfrikapieText;
};


/*************************************************
 * Define the routes
 ************************************************/

$app->mount('/', new \App\Controller\BaseController($app));


return $app;
