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

        $ctrl->get('/{year}-{month}-{day}', [$this, 'readText']);

        return $ctrl;
    }

    public function readText($year, $month, $day)
    {
        return $this->app->render('text.html.twig', [
            'article' => $this->app['afrikapieText']->findAndTransform($year, $month, $day)
        ]);
    }
}
