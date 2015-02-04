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
        return $this->app->render('text.html.twig', [
            'text' => $this->app['afrikapieText']->findAndTransform($name)
        ]);
    }
}
