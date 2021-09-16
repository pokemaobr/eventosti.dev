<?php

namespace App\Service;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;

class EmailService
{
    private $mail;

    public function __construct(private $nick, private $email)
    {

        $this->mail = (new Email())
            ->to(new Address($email, $nick))
            ->from(new Address('contato@pokemaobr.dev', 'Pokemaobr'))
            ->replyTo(new Address('contato@pokemaobr.dev', 'Pokemaobr'));

    }

    public function avisarCadastro(string $evento, MailerInterface $mailer)
    {

        try {

            $this->mail
                ->subject('Evento cadastrado em eventosti.dev')
                ->html('<p>Olá pokemão, acabaram de cadastrar o evento "'.$evento.'" no site!</p>' .
                   '<br /><br /><p>Valeu, valida lá</p>')
                ->text('Troque sua caixa de e-mail porque é uma bosta.');

            $mailer->send($this->mail);
        } catch
        (Exception $e) {
            echo "A mensagem não pode ser enviada. Erro do envio: {$this->mail->ErrorInfo}";
        }
    }


}