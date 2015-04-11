<?php

namespace App\Controller;

use Silex\Api\ControllerProviderInterface;

/**
 * Summary :
 *  -> __construct
 *  -> connect
 *  -> home
 *  -> readText
 *  -> contact         [protected]
 *  -> isPublishedText [protected]
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
        $today = date('Y-m-d');

        list($_, $month, $day) = explode('-', $today);

        $texts = (array) @ $this->app['publishedTexts'][$today];

        return (true === $contact = $this->contact()) ?
            $this->app->redirect("/")                 :
            $this->app->render('home.html.twig', $contact + [
                'today' => [
                    'day'   => $day,
                    'month' => $month,
                    'num'   => count($texts),
                    'texts' => $texts,
                ],
            ]);
    }

    public function readText($slug)
    {
        try {
            if (! $this->isPublishedText($slug)) {
                throw new \Exception('Texte non publié.');
            }

            $text = $this->app['afrikapieText']->findAndTransform($slug);
        }
        catch (\Exception $e) {
            $this->app->abort(404,
                "Le texte \"$slug\" n'existe pas !\n".
                ($this->app['debug'] ? $e->getMessage() : '')
            );
        }

        // If the next text is unpublished => remove it.
        if ($next =& $text['next'] && ! $this->isPublishedText($next)) { $next = null; }

        return (true === $contact = $this->contact()) ?
            $this->app->redirect("/$slug")            :
            $this->app->render("text/tpl-{$text['template']}.html.twig",
                ['text' => $text] + $contact
            );
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

    /**
     * Determine if a $text is published or not.
     */
    protected function isPublishedText($text)
    {
        $found = false;
        $pub   = $this->app['publishedTexts'];
        $today = date('Y-m-d');

        // Search the $text, for each publication date <= today
        while (($e = each($pub)) && $e[0] <= $today && ! $found = in_array($text, $e[1]));

        return $found;
    }
}
