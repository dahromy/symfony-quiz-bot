<?php


namespace App\Conversations;


use App\Entity\Answer;
use App\Entity\Question;
use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer as BotManAnswer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question as BotManQuestion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QuizConversation extends Conversation
{
    /** @var ArrayCollection<Question> */
    protected $quizQuestions;

    /** @var integer */
    protected $userPoints = 0;

    /** @var integer */
    protected $userCorrectAnswers = 0;

    /** @var integer */
    protected $questionCount = 0; // we already had this one

    /** @var integer */
    protected $currentQuestion = 1;

    /** @var ContainerInterface */
    private $container;

    /**
     * QuizConversation constructor.
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->quizQuestions = $this->container->get('doctrine.orm.entity_manager')->getRepository(Question::class)->findAll();
        $this->questionCount = count($this->quizQuestions);

        $this->showInfo();
    }

    public function showInfo()
    {
        $this->say('You will be shown '.$this->questionCount.' questions about Laravel. Every correct answer will reward you with a certain amount of points. Please keep it fair, and don\'t use any help. All the best! 🍀');
        $this->checkForNextQuestion();
    }

    public function checkForNextQuestion()
    {
        if ($this->quizQuestions->count() > 0) {
            $this->askQuestion($this->quizQuestions->first());
        }

        $this->showResult();
    }
//
//    private function askQuestion(Question $question)
//    {
//        $this->ask($this->createQuestionTemplate($question), function (BotManAnswer $answer) use ($question) {
//            /** @var Answer $quizAnswer */
//            $quizAnswer = $this->container->get('doctrine.orm.entity_manager')->getRepository(Answer::class)->findOneById($answer->getValue());
//
//            if (!$quizAnswer) {
//                $this->say('Sorry, I did not get that. Please use the buttons.');
//                return $this->checkForNextQuestion();
//            }
//
//            $this->quizQuestions->remove($question);
//
//            if ($quizAnswer->getCorrectOne()) {
//                $this->userPoints += $question->getPoints();
//                $this->userCorrectAnswers++;
//                $answerResult = '✅';
//            } else {
//                /** @var Answer $correctAnswer */
//                $correctAnswer = $this->container->get('doctrine.orm.entity_manager')->getRepository(Answer::class)->findOneBy(['correct_one' => true]);
//                $answerResult = "❌ (Correct: {$correctAnswer->getText()})";
//            }
//            $this->currentQuestion++;
//
//            $this->say("Your answer: {$quizAnswer->getText()} {$answerResult}");
//            $this->checkForNextQuestion();
//        });
//    }
//
//    private function createQuestionTemplate(Question $question): BotManQuestion
//    {
//        $questionText = '➡️ Question: '.$this->currentQuestion.' / '.$this->questionCount.' : '.$question->getText();
//        $questionTemplate = BotManQuestion::create($questionText);
//        $answers = $question->getAnswers();
//
//        foreach ($answers as $answer) {
//            $questionTemplate->addButton(Button::create($answer->getText())->value($answer->getId()));
//        }
//
//        return $questionTemplate;
//    }
//
//    private function showResult()
//    {
//        $this->say('Finished ?');
//        $this->say("You made it through all the questions. You reached {$this->userPoints} points! Correct answers: {$this->userCorrectAnswers} / {$this->questionCount}");
//    }

    public function askQuestion(Question $question)
    {
        $this->ask($this->createQuestionTemplate($question), function (BotManAnswer $answer) use ($question) {

            /** @var Answer $quizAnswer */
            $quizAnswer = $this->container->get('doctrine.orm.entity_manager')->getRepository(Answer::class)->findOneBy([
                'id' => $answer->getValue()
            ]);

            if (!$quizAnswer) {
                $this->say('Sorry, I did not get that. Please use the buttons.');
                $this->checkForNextQuestion();
            }

            if ($quizAnswer->getCorrectOne()) {
                $this->userPoints += $question->getPoints();
                $this->userCorrectAnswers++;
                $answerResult = '✅';
            } else {
                $correctAnswer = $this->container->get('doctrine.orm.entity_manager')->getRepository(Answer::class)->findOneBy([
                    'question' => $question,
                    'correctOne' => true
                ])->getText();

                $answerResult = "❌ (Correct: {$correctAnswer})";
            }

            $this->currentQuestion++;

            $this->say("Your answer: {$quizAnswer->getText()} {$answerResult}");
            $this->checkForNextQuestion();
        });
    }

    public function showResult()
    {
        $this->say('Finished 🏁');
        $this->say("You made it through all the questions. You reached {$this->userPoints} points! Correct answers: {$this->userCorrectAnswers} / {$this->questionCount}");
    }

    public function createQuestionTemplate(Question $question)
    {
        $questionText = '➡️ Question: '.$this->currentQuestion.' / '.$this->questionCount.' : '.$question->getText();
        $questionTemplate = BotManQuestion::create($questionText);
        $answers = $question->getAnswers();

        foreach ($answers as $answer) {
            $questionTemplate->addButton(Button::create($answer->getText())->value($answer->getId()));
        }

        return $questionTemplate;
    }

    public function setQuizData(Question $question)
    {
        $data = [];

        foreach ($this->quizQuestions as $quizQuestion){
            if ($quizQuestion->getId() !== $question->getId()){
                $data[] = $quizQuestion;
            }
        }

        return $data;
    }
}
