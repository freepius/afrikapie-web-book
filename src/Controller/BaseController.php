<?php

namespace App\Controller;

use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Summary :
 *  -> __construct
 *  -> connect
 *  -> manageErrors
 *  -> home
 *  -> feed
 *  -> odt
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

        $ctrl->get('/feed', [$this, 'feed']);

        if ($app['debug']) {
            $ctrl->get('/pub/{slug}.odt', [$this, 'odt']);
        }

        $ctrl->match('/{slug}', [$this, 'readText'])->method('GET|POST');

        return $ctrl;
    }

    public function manageErrors()
    {
        if ($this->app['debug']) { return; }
        return $this->app->render('page/error.html.twig');
    }

    public function home()
    {
        $today = date('Y-m-d');

        list(, $month, $day) = explode('-', $today);

        $num   = 0;
        $texts = @ $this->app['text.published.all'][$today];

        if (is_array($texts))
        {
            $num   = count($texts);
            $texts = array_reverse($texts);
        }

        return (true === $contact = $this->contact()) ?
            $this->app->redirect("/")                 :
            $this->app->render('page/home.html.twig', $contact + [
                'today' => [
                    'day'   => $day,
                    'month' => $month,
                    'num'   => $num,
                    'texts' => $texts,
                ],
            ]);
    }

    /**
     * Only the 10 last published texts.
     *
     * Each text contains : slug, title, publication date and "raw text"
     * (without any transformation).
     */
    public function feed()
    {
        $i        = 1;
        $tmp      = $this->app['text.published.really'];
        $titles   = $this->app['text.titles'];
        $response = new Response(null, 200, ['content-type' => 'application/rss+xml']);

        /**
         * 1) Retrieve only the 10 last published texts.
         * 2) Do not include the static texts (key = 1).
         */
        while ((list($slug, $pubDate) = each($tmp))
               &&
               $i++ < 11 && $pubDate !== 1
        ) {
            $texts[$slug] = [
                'title'   => $titles[$slug],
                'pubDate' => $pubDate,
                'content' => $this->app['richtext']->transform(
                    $this->app['afrikapieText']->rawText($slug)
                ),
            ];
        }

        return $this->app->render('page/feed.xml.twig',
            ['texts' => $texts], $response
        );
    }

    /**
     * Make an ODT version ONLY for the "travel texts".
     */
    public function odt($slug)
    {
        $out = $this->app['text.pub_dir']."/$slug.odt";

        $title = @ $this->app['text.titles'][$slug];

        if (!$this->isPublishedText($slug) || !$title) {
            throw new \Exception("Le texte \"$slug\" n’est pas publié ou ne dispose pas de version \"odt\".");
        }

        if (!file_exists($out))
        {
            $md = "# $title\n\n".$this->app['afrikapieText']->rawText($slug);

            $in = tempnam('/tmp', 'anarchos-semitas_afrikapied_').'.md';

            file_put_contents($in, $md);

            exec("pandoc --smart --base-header-level=2 $in -o $out");

            unlink($in);

            return $this->app->redirect("/pub/$slug.odt");
        }
    }

    public function readText($slug)
    {
        if (! $this->isPublishedText($slug)) {
            throw new \Exception("Le texte \"$slug\" n’est pas publié.");
        }

        $text = $this->app['afrikapieText']->findAndTransform($slug);

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
                // Add, to the subject, the requested URI
                $contact['subject'] =
                    '['.$request->getRequestUri().'] '.$contact['subject'];

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
        return $this->app['debug'] ||
               array_key_exists($text, $this->app['text.published.really']);
    }
}
