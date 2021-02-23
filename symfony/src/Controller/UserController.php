<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;
use App\Security\LoginFormAuthenticator;
use App\Form\Type\UserType;
use App\Form\Type\User\EditType;
use App\Entity\User;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class UserController extends AbstractController
{
    /**
     * Fonction d'envoi d'email en lien avec les comptes utilisateurs
     */
    private function _sendActivationEmail(User $user, MailerInterface $mailer)
    {
        $userActivationPage = $this->generateUrl('user_activate', ['token' => $user->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        $sent = false;
        // send activation email
        $email = (new TemplatedEmail())
            ->from(new Address('r.gueffier.pro@gmail.com', "L'équipe timeline.zapto.org"))
            ->to($user->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Inscription sur timeline.zapto.org')
            ->html('<p>Bonjour,</p><p>Ton compte a bien été créé sur timeline.zapto.org, tu peux activer celui-ci en visitant le lien suivant :</p><p>' . $userActivationPage . '</p><br><p>À très bientôt sur timeline.zapto.org !</p>');

        $email->getHeaders()
            // this header tells auto-repliers ("email holiday mode") to not
            // reply to this message because it's an automated email
            ->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');

        try {
            $sent = $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw new \Exception('Impossible d\'envoyer l\'email d\'activation. Merci de contacter le webmaster');
        }

        return $sent;
    }

    /**
     * @Route("/signin", name="user_signin")
     */
    public function signin(Request $request, UserPasswordEncoderInterface $passwordEncoder, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $password = $user->getPassword();

            // Encoding password
            $user->setPassword($passwordEncoder->encodePassword($user, $password));

            // create unique user key, then save to db and generate an activation link send by email
            $uuid = Uuid::v4();
            $token = $uuid->toBase58();
            $user->setToken($token);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->_sendActivationEmail($user, $mailer);

            return $this->render('user/registered.html.twig', [
                'user' => $user
            ]);
        }

        return $this->render('user/signin.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/signin/activation/token/{token}", name="user_activate")
     */
    public function activation($token): Response
    {
        $status = 'error';
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['token' => $token]);

        if ($user) {
            $status = 'already';
            if ($user->getEnabled() === false) {
                // if user not enabled yet, then enable it
                $user->setEnabled(true);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                $status = 'success';
            }
        }

        return $this->render('user/activation.html.twig', [
            'status' => $status
        ]);
    }

    /**
     * @Route("/user/profile", name="user_profile")
     */
    public function profile(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        if (!$user) {
            throw $this->createNotFoundException(
                'Impossible de visualiser votre compte, merci de vous reconnecter ou de réessayer plus tard.'
            );
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/user/delete", name="user_delete")
     */
    public function delete(): Response
    {
        // usually you'll want to make sure the user is authenticated first
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        if (!$user) {
            throw $this->createNotFoundException(
                'Impossible de visualiser votre compte, merci de vous reconnecter ou de réessayer plus tard.'
            );
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/user/edit", name="user_edit")
     */
    public function edit(Request $request, MailerInterface $mailer): Response
    {
        // usually you'll want to make sure the user is authenticated first
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $actual_email = $user->getEmail();

        $form = $this->createForm(EditType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            // change token
            $uuid = Uuid::v4();
            $token = $uuid->toBase58();
            $user->setToken($token);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            if ($user->getEmail() !== $actual_email) {
                $this->_sendActivationEmail($user, $mailer);
                // if email has been updated, user should confirm email again
                return $this->render('user/registered.html.twig', [
                    'user' => $user
                ]);
            }

            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
