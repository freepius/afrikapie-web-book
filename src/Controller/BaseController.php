<?php

namespace App\Controller;

use Silex\Api\ControllerProviderInterface;

/**
 * Summary :
 *  -> __construct
 *  -> connect
 *  -> home
 *  -> readText
 *  -> contact      [protected]
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

        $ctrl->match('/', [$this, 'home'])->method('GET|POST');

        $ctrl->match('/{slug}', [$this, 'readText'])->method('GET|POST');

        return $ctrl;
    }

    public function home()
    {
        return (true === $contact = $this->contact()) ?
            $this->app->redirect("/")                 :
            $this->app->render('home.html.twig', $contact);
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

        return (true === $contact = $this->contact()) ?
            $this->app->redirect("/$slug")            :
            $this->app->render('text.html.twig', ['text' => $text] + $contact);
    }

    /**
     * Return true if the mail is sent.
     */
    protected function contact()
    {
        $request = $this->app['request_stack']->getMasterRequest();
        $ourMail = $this->app['swiftmailer.options']['username'];
        $factory = $this->app['model.factory.contact'];
        $contact = $factory->instantiate();
        $errors  = [];

        if ($request->isMethod('POST'))
        {
            $httpData = $request->request->all(); // http POST data

            $errors = $factory->bind($contact, $httpData);

            // No error => log + send a mail + redirect
            if (! $errors)
            {
                // TODO: to activate
                /*$this->app->mail(\Swift_Message::newInstance()
                    ->setSubject($contact['subject'])
                    ->setFrom([$contact['email'] => $contact['name']])
                    ->setTo($ourMail)
                    ->setBody($contact['message'])
                );*/

                $this->app->addFlash('success',
                    'Votre message a bien été envoyé. <b>Merci.</b>');

                return true;
            }
        }

        return [
            'contact'  => $contact,
            'errors'   => $errors,
            'pathInfo' => $request->getPathInfo(),
        ];
    }
}
