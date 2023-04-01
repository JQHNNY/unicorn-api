<?php

namespace App\Service;

use App\Entity\Unicorn;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function sendEmail(string $emailTo, Unicorn $unicorn, string $html)
    {
        $email = (new Email())
            ->from('unicornfarm@special.be')
            ->to($emailTo)
            ->subject('Purchase ' . $unicorn->getName())
            ->html($html);

        $this->mailer->send($email);
    }
}