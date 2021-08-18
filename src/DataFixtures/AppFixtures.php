<?php

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Question;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $questionAndAnswers = $this->getData();

        foreach ($questionAndAnswers as $_question){
            $question = new Question();
            $question
                ->setText($_question['question'])
                ->setPoints($_question['points'])
            ;

            $manager->persist($question);

            foreach ($_question['answers'] as $_answer){
                $answer = new Answer();
                $answer
                    ->setQuestion($question)
                    ->setText($_answer['text'])
                    ->setCorrectOne($_answer['correct_one'])
                ;

                $manager->persist($answer);
            }
        }

        $manager->flush();
    }

    /**
     * @return array[]
     */
    public function getData()
    {
        return [
            [
                'question' => 'Who created Laravel?',
                'points' => 5,
                'answers' => [
                    ['text' => 'Christoph Rumpel', 'correct_one' => false],
                    ['text' => 'Jeffrey Way', 'correct_one' => false],
                    ['text' => 'Taylor Otwell', 'correct_one' => true],
                ],
            ],
            [
                'question' => 'Which of the following is a Laravel product?',
                'points' => 10,
                'answers' => [
                    ['text' => 'Horizon', 'correct_one' => true],
                    ['text' => 'Sunset', 'correct_one' => false],
                    ['text' => 'Nightfall', 'correct_one' => true],
                ],
            ],
        ];
    }
}
