<?php

declare(strict_types=1);

/*
 * This file is part of the `botman-demo` project.
 *
 * (c) Sergio Gómez <sergio@uco.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Conversations\QuizConversation;
use BotMan\BotMan\BotMan;
use Symfony\Component\HttpFoundation\Response;

class WebhookController
{
    public function __invoke(BotMan $botman): Response
    {
        $botman->hears('Hi', function (BotMan $bot) {
            $bot->reply('Hello!');
        });

        $botman->hears('start', function (BotMan $bot) {
            $bot->startConversation(new QuizConversation());
        });

        $botman->listen();

        return new Response('', Response::HTTP_OK);
    }
}
