<?php

namespace App\Controller;

use Silex\Api\ControllerProviderInterface;

/**
 * Summary :
 *  -> __construct
 *  -> connect
 *  -> manageErrors
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
        $app->error([$this, 'manageErrors']);

        $ctrl = $app['controllers_factory'];

        $ctrl->match('/', [$this, 'home'])->method('GET|POST');

        $ctrl->match('/{slug}', [$this, 'readText'])->method('GET|POST');

        return $ctrl;
    }

    public function manageErrors()
    {
        if ($this->app['debug']) { return; }
        return $this->app->render('error.html.twig');
    }

    public function home()
    {
        $today = date('Y-m-d');

        list($_, $month, $day) = explode('-', $today);

        $texts = (array) @ $this->app['text.published.all'][$today];

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
            if (!$this->app['debug'] && ! $this->isPublishedText($slug)) {
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

        // If the next text is unpublished => remove it
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
        $logDir  = $this->app['mail_cache_dir'];
        $ourMail = $this->app['swiftmailer.options']['username'];
        $factory = $this->app['model.factory.contact'];
        $contact = $factory->instantiate();
        $errors  = [];

        if ($request->isMethod('POST'))
        {
            $httpData = $request->request->all(); // http POST data

            $errors = $factory->bind($contact, $httpData);

            if (! $errors)
            {
                // Log
                $fp = fopen(
                    $logDir.'/'.date('Y-m-d-H-i-s_').uniqid().'.txt',
                    'w'
                );
                fwrite($fp,
                    "Name   : {$contact['name']}\n".
                    "Email  : {$contact['email']}\n".
                    "Subject: {$contact['subject']}\n".
                    "Message: {$contact['message']}"
                );
                fclose($fp);

                // Send
                $this->app->mail(\Swift_Message::newInstance()
                    ->setSubject($contact['subject'])
                    ->setFrom([($contact['email'] ?: $ourMail) => $contact['name']])
                    ->setTo($ourMail)
                    ->setBody($contact['message'])
                );

                // Notify
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
        return array_key_exists($text, $this->app['text.published.really']);
    }
}
