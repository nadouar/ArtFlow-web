<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\Client;
use App\Entity\User;
use App\Repository\AdminRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class AdminMobileController extends AbstractController
{
    #[Route('/admin/mobile', name: 'app_admin_mobile')]
    public function index(): Response
    {
        return $this->render('admin_mobile/index.html.twig', [
            'controller_name' => 'AdminMobileController',
        ]);
    }


    #[Route("/loginmobile", name: "loginmobile")]
    public function login(Request $req)
    {
       $username= $req->get("username");
        $password= $req->get("password");

        $em=$this->getDoctrine()->getManager();
        $user=$em->getRepository(User::class)->findOneBy(['username'=>$username]);

        $userRoles= $user->getRoles();

        if($user){
            if(password_verify($password,$user->getPassword())){
                if (in_array('client',$userRoles)){
                    $serialiser= new Serializer([new ObjectNormalizer()]);
                    $formatted= $serialiser->normalize($user);
                    return new JsonResponse($formatted);
                }
                elseif (in_array('admin',$userRoles)){
                    $serialiser= new Serializer([new ObjectNormalizer()]);
                    $formatted= $serialiser->normalize($user);
                    return new JsonResponse($formatted);
                }
            }
            else{
                return new Response("password not found");
            }
        }else{
            return new Response("failed");

        }

    }

    #[Route("/adminById/{id}", name: "AdminById")]
    public function adminId($id, NormalizerInterface $normalizer, AdminRepository $adminRepository)
    {
        $admin = $adminRepository->find($id);
        $json = $normalizer->serialize($admin, 'json', ['groups' => "admin"]);
        return new Response(json_encode($json));
    }

    #[Route("addAdmin/new", name: "add_Admin")]
    public function addadmin(Request $req,   NormalizerInterface $Normalizer, UserPasswordHasherInterface $userPasswordHasher): Response
    {

        $em = $this->getDoctrine()->getManager();

        $admin = new Client();
        $admin->setFirstname($req->get('firstname'));
        $admin->setLastname($req->get('lastname'));
        $admin->setAddress($req->get('address'));
        $admin->setPhonenumber($req->get('phonenumber'));
        $admin->setEmail($req->get('email'));
        $admin->setUsername($req->get('username'));
        $admin->setPassword($req->get('password'));

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
        $em->persist($admin);
        $em->flush();

        $jsonContent = $Normalizer->normalize($admin, 'json', ['groups' => 'admin']);
        return new Response(json_encode($jsonContent));
    }

    #[Route("updateadmin/{id}", name: "updateadmin")]
    public function updateadmin(Request $req, $id, NormalizerInterface $Normalizer, UserPasswordHasherInterface $userPasswordHasher)
    {

        $em = $this->getDoctrine()->getManager();
        $admin = $em->getRepository(Client::class)->find($id);
        $admin->setFirstname($req->get('firstname'));
        $admin->setLastname($req->get('lastname'));
        $admin->setAddress($req->get('address'));
        $admin->setPhonenumber($req->get('phonenumber'));
        $admin->setEmail($req->get('email'));
        $admin->setUsername($req->get('username'));
        $admin->setPassword($req->get('password'));

        $user= $em->getRepository(User::class)->findOneBy(['username'=>$req->get('username')]);
        $user->setEmail($req->get('email'));
        $user->setUsername($req->get('username'));
        $user->setPassword($userPasswordHasher->hashPassword(
            $user,$req->get('password')
        ));

        $em->flush();

        $jsonContent = $Normalizer->normalize($admin, 'json', ['groups' => 'admin']);
        return new Response("client updated successfully " . json_encode($jsonContent));
    }

    #[Route("deleteadmin/{id}", name: "deleteadmin")]
    public function deleteadmin(Request $req, $id, NormalizerInterface $Normalizer)
    {

        $em = $this->getDoctrine()->getManager();
        $admin = $em->getRepository(Client::class)->find($id);
        if (!$admin) {
            return new Response("Client not found");
        }
        $user = $em->getRepository(User::class)->findOneBy(['username'=>$admin->getUsername()]);
        if ($user) {
            $em->remove($admin);
            $em->remove($user);
            $em->flush();
            $jsonContent = $Normalizer->normalize($admin, 'json', ['groups' => 'admin']);
            return new Response("admin deleted successfully " . json_encode($jsonContent));
        }
        return new Response("User not found");
    }

}
