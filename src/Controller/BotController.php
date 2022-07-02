<?php

namespace App\Controller;

use App\Conversations\OnboardingConversation;
use App\Conversations\QuizConversation;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\SymfonyCache;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Web\WebDriver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BotController extends AbstractController
{
    /** @var EntityManagerInterface  */
    private $entityManager;

    /** @var BotMan */
    private $botman;

    /**
     * QuizConversation constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        DriverManager::loadDriver(WebDriver::class);

        $adapter = new FilesystemAdapter();

        $config = [
            'conversation_cache_time' => 40,
            'user_cache_time' => 30,
            'matchingData' => [
                'driver' => 'web',
            ],
        ];

        $this->botman = BotManFactory::create($config, new SymfonyCache($adapter));
    }

    /**
     * @Route("/message", name="message")
     */
    function messageAction(Request $request): Response
    {
        // Give the bot some things to listen for.
        $this->botman->hears('(hello|hi|hey)', function (BotMan $bot) {
            $bot->reply('Hello!');
        });

        $this->botman->hears('test', function (BotMan $bot) {
            $bot->startConversation(new OnboardingConversation());
        });

        $this->botman->hears('start', function (BotMan $bot) {
            $bot->startConversation(new QuizConversation($this->entityManager));
        });

        $this->botman->hears('Hello BotMan!', function($bot) {
            $bot->reply('Hello!');
            $bot->ask('Whats your name?', function($answer, $bot) {
                $bot->say('Welcome '.$answer->getText());
            });
        });

        // Start listening
        $this->botman->listen();

        return new Response();
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request): Response
    {
        return $this->render('homepage.html.twig');
    }

    /**
     * @Route("/chatframe", name="chatframe")
     */
    public function chatframeAction(Request $request): Response
    {
        return $this->render('chat_frame.html.twig');
    }
}