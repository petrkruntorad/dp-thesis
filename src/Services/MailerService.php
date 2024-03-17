<?php

namespace App\Services;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

class MailerService
{
    public function __construct(
        private readonly MailerInterface       $mailer,
        private readonly ParameterBagInterface $parameterBag,
    )
    {
    }
    public function sendPasswordRecovery(string $userEmail, string $recoveryUrl, ResetPasswordToken $token)
    {
        // inits templated email for password recovery
        $email = (new TemplatedEmail())
            ->from(new Address($this->parameterBag->get('app.email'), $this->parameterBag->get('app.base_name')))
            ->to($userEmail)
            ->subject('Resetování hesla | ' . $this->parameterBag->get('app.base_name'))
            ->htmlTemplate('emails/admin/user/password_recovery.html.twig')
            ->context([
                'link' => $recoveryUrl,
                'link_expiration' => $token->getExpiresAt()
            ]);

        // sends email
        $this->mailer->send($email);
    }
    public function sendAccountCreated(string $userEmail, string $userPassword, string $loginUrl)
    {
        // inits templated email for new account
        $email = (new TemplatedEmail())
            ->from(new Address($this->parameterBag->get('app.email'), $this->parameterBag->get('app.base_name')))
            ->to($userEmail)
            ->subject('Byl vám zřízen nový uživatelský účet | ' . $this->parameterBag->get('app.base_name'))
            ->htmlTemplate('emails/admin/user/new_account.html.twig')
            ->context([
                'email_address' => $userEmail,
                'password' => $userPassword,
                'link' => $loginUrl
            ]);
        // sends email
        $this->mailer->send($email);
    }
}
