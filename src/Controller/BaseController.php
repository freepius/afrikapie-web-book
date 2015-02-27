<?php

namespace App\Controller;

use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Summary :
 *  -> __construct
 *  -> connect
 */
class BaseController implements ControllerProviderInterface
{
    public function __construct(\Freepius\Silex\Application $app)
    {
        $this->app = $app;
    }

    public function connect(\Silex\Application $app)
    {
        $ctrl = $app['controllers_factory'];

        $ctrl->get('/{name}', [$this, 'readText']);

        return $ctrl;
    }

    public function readText($name)
    {
        try {
            $text = $this->app['afrikapieText']->findAndTransform($name);
        }
        catch (\Exception $e) {
            $this->app->abort(404,
                "Le texte \"$name\" n'existe pas !\n".
                ($this->app['debug'] ? $e->getMessage() : '')
            );
        }

        return $this->app->render('text.html.twig', ['text' => $text]);
    }
}
