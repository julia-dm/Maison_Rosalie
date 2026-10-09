<?php

declare(strict_types=1);
namespace model\manager;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailManager
{
    private Mailer $mailer;
    private string $senderEmail;

    public function __construct()
    {
        $this->senderEmail = MAILER_EMAIL;
        // MAILER_DSN (facultatif, dans config.php) permet un autre serveur SMTP ; sinon Gmail
        $dsn = defined('MAILER_DSN') ? MAILER_DSN : sprintf(
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

    // message du formulaire de contact, envoyé à Maison Rosalie
    // (« répondre à » = le visiteur, pour lui répondre directement)
    public function sendContactMessage(string $name, string $email, string $message): void
    {
        $recipient = defined('CONTACT_EMAIL') ? CONTACT_EMAIL : $this->senderEmail;

        $mail = (new Email())
            ->from(new Address($this->senderEmail, 'Site Maison Rosalie'))
            ->to($recipient)
            ->replyTo(new Address($email, $name))
            ->subject('Nouveau message de contact – ' . $name)
            // texte brut : rien de ce que tape le visiteur n'est interprété en HTML
            ->text(
                "Nouveau message envoyé depuis le formulaire de contact.\n\n"
                . "Nom : $name\n"
                . "E-mail : $email\n\n"
                . "Message :\n$message\n"
            );

        $this->mailer->send($mail);
    }
}