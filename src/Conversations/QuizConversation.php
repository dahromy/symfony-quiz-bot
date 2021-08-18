<?php


namespace App\Conversations;


use App\Entity\Question;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer as BotManAnswer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question as BotManQuestion;
use Doctrine\ORM\EntityManagerInterface;

class QuizConversation extends Conversation
{
    protected $quizQuestions;

    /** @var integer */
    protected $userPoints = 0;

    /** @var integer */
    protected $userCorrectAnswers = 0;

    /** @var integer */
    protected $questionCount = 0; // we already had this one

    /** @var integer */
    protected $currentQuestion = 1;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * QuizConversation constructor.
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->quizQuestions = $this->manager->getRepository(Question::class)->findAll();
        $this->questionCount = count($this->quizQuestions);

        $this->showInfo();
    }

    private function showInfo()
    {
        $this->say('You will be shown '.$this->questionCount.' questions about Laravel. Every correct answer will reward you with a certain amount of points. Please keep it fair, and don\'t use any help. All the best! 🍀');
        $this->checkForNextQuestion();
    }

    private function checkForNextQuestion()
    {
        if (count($this->quizQuestions)) {
            return $this->askQuestion($this->quizQuestions[0]);
        }

        $this->showResult();
    }

    private function askQuestion(Question $question)
    {
        $questionTemplate = BotManQuestion::create($question->getText());

        foreach ($question->getAnswers() as $answer) {
            $questionTemplate->addButton(Button::create($answer->getText())->value($answer->getId()));
        }

        $this->ask($questionTemplate, function (BotManAnswer $answer) use ($question) {
            $this->quizQuestions = array_shift($this->quizQuestions);

            $this->checkForNextQuestion();
        });
    }

    private function showResult()
    {
        $this->say('Finished 🏁');
    }
}
