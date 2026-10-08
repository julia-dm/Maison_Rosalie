<?php

declare(strict_types=1);
namespace model\manager;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class MailManager
{
    private Mailer $mailer;
    private string $senderEmail;

    public function __construct()
    {
        $this->senderEmail = MAILER_EMAIL;
        $dsn = sprintf(
            'smtp://%s:%s@smtp.gmail.com:587',
            urlencode(MAILER_EMAIL),
            urlencode(MAILER_APP_PASSWORD)
        );

        $transport = Transport::fromDsn($dsn);
        $this->mailer = new Mailer($transport);
    }

    public function sendVerificationEmail(
        string $email,
        string $generatedKey
    ): void {

        $verificationLink =
            'http://localhost/Maison_Rosalie/public/index.php'
            . '?action=verifyEmail&key='
            . urlencode($generatedKey);

        $email = (new Email())
            ->from($this->senderEmail)
            ->to($email)
            ->subject('Confirmez votre adresse e-mail')
            ->html(
                '<h2>Bienvenue chez Maison Rosalie !</h2>
                <p>Veuillez confirmer votre adresse e-mail.</p>
                <p>
                    <a href="' . htmlspecialchars($verificationLink) . '">
                        Confirmer mon adresse e-mail
                    </a>
                </p>'
            );

        $this->mailer->send($email);
    }
}