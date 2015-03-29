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

        $ctrl->get('/', [$this, 'home']);

        $ctrl->get('/{slug}', [$this, 'readText']);

        return $ctrl;
    }

    public function home(Request $request)
    {
        return $this->app->render('home.html.twig', [] + $this->contact($request));
    }

    public function readText($slug)
    {
        try {
            $text = $this->app['afrikapieText']->findAndTransform($slug);
        }
        catch (\Exception $e) {
            $this->app->abort(404,
                "Le texte \"$slug\" n'existe pas !\n".
                ($this->app['debug'] ? $e->getMessage() : '')
            );
        }

        return $this->app->render('text.html.twig', ['text' => $text]);
    }
}
