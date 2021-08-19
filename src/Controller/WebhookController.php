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
use App\Entity\Question;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use BotMan\BotMan\BotMan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class WebhookController
{
    /**
     * @var QuestionRepository
     */
    private $questionRepository;
    /**
     * @var AnswerRepository
     */
    private $answerRepository;

    /**
     * WebhookController constructor.
     * @param QuestionRepository $questionRepository
     * @param AnswerRepository $answerRepository
     */
    public function __construct(QuestionRepository $questionRepository, AnswerRepository $answerRepository)
    {
        $this->questionRepository = $questionRepository;
        $this->answerRepository = $answerRepository;
    }

    public function __invoke(BotMan $botman): Response
    {
        $botman->hears('Hi', function (BotMan $bot) {
            $bot->reply('Hello!');
        });

        $botman->hears('start', function (BotMan $bot) {
            $bot->startConversation(new QuizConversation($this->questionRepository, $this->answerRepository));
        });

        $botman->listen();

        return new Response('', Response::HTTP_OK);
    }
}
