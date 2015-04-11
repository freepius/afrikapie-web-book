<?php

define('APP'  , __DIR__);
define('ROOT' , dirname(APP));
define('CACHE', ROOT.'/cache');

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

$app['bing_maps_api_key'] = BING_MAPS_API_KEY;


/*************************************************
 * Register services
 ************************************************/

/* session (only used for flash messages) */
$app->register(new \Silex\Provider\SessionServiceProvider());

/* twig */
$app->register(new \Silex\Provider\TwigServiceProvider(), [
    'twig.path'    => [APP.'/Resources/views'],
    'twig.options' => ['cache' => DEBUG ? null : (CACHE.'/twig')],
]);

/* swiftmailer */
$app->register(new \Silex\Provider\SwiftmailerServiceProvider());

/* translation (only used by ValidatorServiceProvider) */
$app->register(new \Silex\Provider\TranslationServiceProvider());

/* validator */
$app->register(new \Silex\Provider\ValidatorServiceProvider());

/* freepius/php-asset */
$app->register(new \Freepius\Pimple\Provider\AssetServiceProvider, [
    'asset.cdn.use' => ! $app['debug'],
    'asset.config'  => ['base.url' => BASE_URL_FOR_ASSET],
]);

/* freepius/php-richtext */
$app->register(new \Freepius\Pimple\Provider\RichtextServiceProvider);
$app['richtext.config'] += ['remove.script.tags' => false];

/* Transformer for "Afrikapié format" */
$app['afrikapieText'] = function () {
    return new \App\Util\AfrikapieText(APP.'/Resources/texts');
};


/*************************************************
 * Twig extensions, global variables, filters and functions.
 ************************************************/

$app['twig'] = $app->extend('twig', function($twig, $app)
{
    // My trans dictionary
    $trans = [
        'contact.field.name'    => 'Qui êtes-vous ?',
        'contact.field.email'   => 'Votre email',
        'contact.field.subject' => 'Le sujet',
        'contact.field.message' => 'Votre message',
        'contact.help.email'    => '(facultatif)',
        'Error(s)'              => 'Il y a <b>0</b> erreur(s) au <b><a href="#contact">formulaire de contact</a></b>.',

        // Months
        'month.01' => 'janvier',
        'month.02' => 'février',
        'month.03' => 'mars',
        'month.04' => 'avril',
        'month.05' => 'mai',
        'month.06' => 'juin',
        'month.07' => 'juillet',
        'month.08' => 'août',
        'month.09' => 'septembre',
        'month.10' => 'octobre',
        'month.11' => 'novembre',
        'month.12' => 'décembre',
    ];

    // My simple trans filter
    $twig->addFilter(new \Twig_SimpleFilter('trans',
        function ($msg, array $rpls = []) use ($trans)
        {
            return strtr($trans[$msg], $rpls);
        }
    ));

    return $twig;
});


/*************************************************
 * SwiftMailer configuration
 ************************************************/

$app['swiftmailer.options'] =
[
    'host'       => 'smtp.alwaysdata.com',
    'port'       => 587,
    'username'   => 'contact@anarchos-semitas.net',
    'password'   => SMTP_PASSWORD,
    'encryption' => null,
    'auth_mode'  => null,
];


/*************************************************
 * Entity factories
 ************************************************/

$app['model.factory.contact'] = function ($app)
{
    return new \App\Model\Factory\Contact($app['validator']);
};


/*************************************************
 * Define the routes
 ************************************************/

$app->mount('/', new \App\Controller\BaseController($app));


return $app;
