<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;




class ClientMobileController extends AbstractController
{

    #[Route('/affichageClient', name: 'mobile_list_client')]
    public function display(ClientRepository $clientRepository, NormalizerInterface $normalizer): Response
    {
        $client = $clientRepository->findAll();
        $json = $normalizer->serialize($client, 'json', ['groups' => 'client']);

        return new Response($json);
    }

    #[Route('/profileclient/{username}', name: 'app_profilemobile')]
    public function profile(Request $req, EntityManagerInterface $entityManager,NormalizerInterface $normalizer, $username )
    {
        //$username= $req->get("username");
        $em=$this->getDoctrine()->getManager();
//        $user=$em->getRepository(User::class)->findOneBy(['username'=>$username]);
//        $getusername= $user->getUsername();
        $client=$entityManager->getRepository(Client::class)->findOneBy(['username'=>$username]);

        if($client){
            $json = $normalizer->normalize($client, 'json', ['groups' => "client"]);
            return new Response(json_encode($json). "client affichéeeeeeeeeeeee");
        }
        else{
            return new Response("client not found");
        }

    }

    #[Route("/clientById/{id}", name: "ClientById")]
    public function clientId($id, NormalizerInterface $normalizer, ClientRepository $clientRepository)
    {
        $client = $clientRepository->find($id);
        $json = $normalizer->serialize($client, 'json', ['groups' => "client"]);
        return new Response(json_encode($json));
    }

    #[Route("addClient/new", name: "add_Client")]
    public function addclient(Request $req,   NormalizerInterface $Normalizer, UserPasswordHasherInterface $userPasswordHasher): Response
    {

        $em = $this->getDoctrine()->getManager();

        $client = new Client();
        $client->setFirstname($req->get('firstname'));
        $client->setLastname($req->get('lastname'));
        $client->setAddress($req->get('address'));
        $client->setPhonenumber($req->get('phonenumber'));
        $client->setEmail($req->get('email'));
        $client->setUsername($req->get('username'));
        $client->setPassword($req->get('password'));

        $user =new User();
        $user->setUsername($req->get('username'));
        $user->setEmail($req->get('email'));
        $user->setRoles(['client']);
        $user->setPassword($userPasswordHasher->hashPassword(
            $user,$req->get('password')
        ));

//        $dateString = '2023-04-29 12:00:00';
//        $format = 'Y-m-d H:i:s';
//        $dateTime = \DateTime::createFromFormat($format, $dateString);
//
//        $client->setDatelimite($dateTime);
//
//        $client->setImage($req->get('image'));
        $em->persist($user);
        $em->persist($client);
        $em->flush();

        $jsonContent = $Normalizer->normalize($client, 'json', ['groups' => 'client']);
        return new Response(json_encode($jsonContent));
    }

    #[Route("/updateclient/{username}", name: "updateclient")]
    public function updateclient(Request $req, $username, NormalizerInterface $Normalizer, UserPasswordHasherInterface $userPasswordHasher)
    {
        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository(Client::class)->findOneBy(['username'=>$username]);
        $client->setFirstname($req->get('firstname'));
        $client->setLastname($req->get('lastname'));
        $client->setAddress($req->get('address'));
        $client->setPhonenumber($req->get('phonenumber'));
        $client->setEmail($req->get('email'));
        $client->setUsername($req->get('username'));
        $client->setPassword($req->get('password'));

        $user= $em->getRepository(User::class)->findOneBy(['username'=>$username]);
        $user->setEmail($req->get('email'));
        $user->setUsername($req->get('username'));
        $user->setPassword($userPasswordHasher->hashPassword(
            $user,$req->get('password')
        ));

        $em->flush();

        $jsonContent = $Normalizer->normalize($client, 'json', ['groups' => 'client']);
        return new Response("client updated successfully " . json_encode($jsonContent));
    }

    #[Route("deleteclient/{username}", name: "deleteclient")]
    public function deleteclient(Request $req, $username, NormalizerInterface $Normalizer)
    {

        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository(Client::class)->findOneBy(['username'=>$username]);
        if (!$client) {
            return new Response("Client not found");
        }
        $user = $em->getRepository(User::class)->findOneBy(['username'=>$username]);
        if ($user) {
            $em->remove($client);
            $em->remove($user);
            $em->flush();
            $jsonContent = $Normalizer->normalize($client, 'json', ['groups' => 'client']);
            return new Response("Enchere deleted successfully " . json_encode($jsonContent));
        }
        return new Response("User not found");
    }


}