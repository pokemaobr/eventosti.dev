<?php

namespace App\Service;

use App\Entity\Eventos;
use DG\Twitter\Twitter;

class TwitterService {

    private Twitter $twitter;

    public function __construct() {
        $this->twitter = new Twitter($_ENV['TWITTER_API_KEY'],$_ENV['TWITTER_API_SECRET'],$_ENV['TWITTER_ACCESS_TOKEN'],$_ENV['TWITTER_TOKEN_SECRET']);
    }

    public function enviaMensagemCadastroEvento(Eventos $evento) {

        $mensagem = 'Mais um evento cadastrado na plataforma https://eventosti.dev confira agora! ' .$evento->getNome() . ' em: ' . $evento->getLink();

        try {
        $this->twitter->send($mensagem);
        }
        catch (Exception $e) {

        }
    }

}