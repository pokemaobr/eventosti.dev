<?php

namespace App\Service;

use App\Entity\Eventos;
use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterService {

    private TwitterOAuth $twitter;

    public function __construct() {
        $this->twitter = new TwitterOAuth($_ENV['TWITTER_API_KEY'],$_ENV['TWITTER_API_SECRET'],$_ENV['TWITTER_ACCESS_TOKEN'],$_ENV['TWITTER_TOKEN_SECRET']);
        $this->twitter->setApiVersion('2');
    }

    public function enviaMensagemCadastroEvento(Eventos $evento) {

        $mensagem = 'Mais um evento cadastrado na plataforma https://eventosti.dev confira agora! ' .$evento->getNome() . ' em: ' . $evento->getLink();

        try {
        //$this->twitter->send($mensagem);
        $tweet = $this->twitter->post('tweets',['text' => $mensagem]);
        }
        catch (Exception $e) {
           dd($e->getMessage());
        }
    }

}