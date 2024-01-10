<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class AuthenticationController extends AbstractController
{
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/api/register', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        UserRepository $userRepository,
        JWTEncoderInterface $jwtEncoder): JsonResponse
    {
        $jsonData = $request->getContent();
        $data = json_decode($jsonData, true);

        $name = $data['name'];
        $email = $data['email'];
        $password = $data['password'];

        if ($userRepository->findOneBy(['email' => $email])) {
            return new JsonResponse(['error' => 'Cet email est déjà utilisé.'], Response::HTTP_CONFLICT);
        }

        if ($name != '' && $email != '' && $password != '') {
            $user = new User();
            $user->setName($name);
            $user->setEmail($email);

            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $token = $jwtEncoder->encode([
                'username' => $user->getUsername()
            ]);
        }

        return new JsonResponse(['token' => $token], JsonResponse::HTTP_CREATED);
    }
}
