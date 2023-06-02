<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SendMailController extends AbstractController
{
    #[Route('/send/mail', name: 'app_send_mail')]
    public function index(): Response
    {
        return $this->render('send_mail/index.html.twig', [
            'controller_name' => 'SendMailController',
        ]);
    }


#[Route('/sendmail', name: 'app_demandetravail_mail', methods: ['GET'])]
    public function sendEmail(NormalizerInterface $Normalizer,MailerInterface $mailer): Response
{


        $email = (new TemplatedEmail())
            ->from(new Address('kanzarinadak@gmail.com'))
            ->to( 'kanzarinadak@gmailcom')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('New Subscriber ')
            ->text('Sending emails is fun again!')
            ->html(" congratulations!!! you are registered successfully" );

        $mailer->send($email);




      return new Response("hi");
}

}
