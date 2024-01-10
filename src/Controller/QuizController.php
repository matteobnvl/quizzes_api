<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\QuizAttempt;
use App\Entity\Reponse;
use App\Repository\CategoryRepository;
use App\Repository\QuestionRepository;
use App\Repository\QuizAttemptRepository;
use App\Repository\QuizRepository;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Builder\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class QuizController extends AbstractController
{
    #[Route('/api/quiz', name: 'app_quiz', methods: ['POST'])]
    public function index(Request $request, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'];
        $categoryId = $data['category'];

        $category = $categoryRepository->find($categoryId);

        $quiz = new Quiz();
        $quiz->setName($name);
        $quiz->setCategory($category);
        $quiz->setCreatedAt(new \DateTimeImmutable());
        
        $entityManager->persist($quiz);
        $entityManager->flush();

        return new JsonResponse(['success' => 'quiz créée'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/quiz/{quiz_id}', name:'app_quiz_by_id', methods:['GET'])]
    public function getQuizByIdQuizz(QuizRepository $quizRepository, int $quiz_id, SerializerInterface $serializer): JsonResponse
    {
        $quiz = $quizRepository->find($quiz_id);

        $questions = $quiz->getQuestions();
        $data = [];
        foreach ($questions as $question) {
            $responses = $question->getReponses();
            $data[] = [
                'question' => [
                    'id' => $question->getId(), 
                    'texte' => $question->getQuestion(),
                    'reponses' => array_map(function ($reponse) {
                        return ['id' => $reponse->getId() ,'texte' =>$reponse->getTexte(), 'correct' => $reponse->isJuste()];
                    }, $responses->toArray())
                ],
            ];
        }

        $json = $serializer->serialize($data, 'json');

        return new JsonResponse($json, JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/quiz/create-question', name: 'app_quiz_create_question', methods:['POST'])]
    public function createQuestion(
        Request $request, 
        QuizRepository $quizRepository,
        QuestionRepository $questionRepository,
        EntityManagerInterface $entityManager
        ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $quiz = $quizRepository->find($data['id_quiz']);

        $question = new Question();
        $question->setQuestion($data['question']);
        $question->setQuiz($quiz);

        $entityManager->persist($question);
        $entityManager->flush();
        
        $idQuestion = $question->getId();
        $questionEntity = $questionRepository->find($idQuestion);

        foreach ($data['reponse'] as $answers) {
            $reponse = new Reponse();
            $reponse->setTexte($answers['texte']);
            $reponse->setJuste($answers['correct']);
            $reponse->setQuestion($questionEntity);
            $entityManager->persist($reponse);
            $entityManager->flush();
        }


        return new JsonResponse(['succes' => 'questions créée'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/quiz/{quiz_id}/correct', name: 'app_quiz_correct', methods:['POST'])]
    public function correctQuizById(
        int $quiz_id, 
        Request $request, 
        QuizRepository $quizRepository,
        ReponseRepository $reponseRepository,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $quiz = $quizRepository->find($quiz_id);
        if (!$quiz) {
            return $this->json(['status' => 'error', 'message' => 'Quiz not found'], 404);
        }
        $json = [];
        $score = 0;

        foreach ($data['answers'] as $question) {
            $correctAnswers = $reponseRepository->findBy(['question' => $question['questionId'], 'juste' => true]);
            $correctAnswerIds = array_map(function($answer) {
                return $answer->getId();
            }, $correctAnswers);

            if ($correctAnswerIds == $question['answerIds']) {
                $json[] = true;
                $score++;
            } else {
                $json[] = false;
            }
        }
        $quiz_attempts = new QuizAttempt();
        $quiz_attempts->setScore($score);
        $quiz_attempts->setIsCorrect($score === count($json));
        $quiz_attempts->setAnswers(json_encode($json));
        $quiz_attempts->setQuiz($quiz);

        $entityManager->persist($quiz_attempts);
        $entityManager->flush();
        
        $json[] = ['score' => $score . '/' . count($json)];

        $json = $serializer->serialize($json, 'json');

        return new JsonResponse($json, JsonResponse::HTTP_CREATED);
    }


    #[Route('/api/quiz/page/{quiz_id}', name:'api_quiz_page', methods:['GET'])]
    public function getQuizPageInformation(
        int $quiz_id, 
        QuestionRepository $questionRepository, 
        QuizAttemptRepository $quizAttemptRepository,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $questions = $questionRepository->findBy(['quiz' => $quiz_id]);
        $quizAttempts = $quizAttemptRepository->findBy(['quiz' => $quiz_id]);
        $nbQuizAttempt = count($quizAttempts);
        $nbCorrectResponse = array_sum(array_map(function($response) {
            return $response->isIsCorrect() === true ? 1 : 0;
        }, $quizAttempts));
        $json = [
            'nbQuestions' => count($questions),
            'quizAttempts' => $quizAttempts,
            'nbQuizAttempt' => $nbQuizAttempt,
            'tauxCorrectQuiz' => $nbQuizAttempt != 0 ? $nbCorrectResponse / $nbQuizAttempt * 100 : null
        ];
        $json = $serializer->serialize($json, 'json');
        return new JsonResponse($json, JsonResponse::HTTP_CREATED);
    }
}
