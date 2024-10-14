<?php

namespace App\Service;

use App\Entity\Eventos;
use Trukes\ThreadsApiPhpClient\Threads;

class ThreadsService
{

    private $client;

    public function __construct()
    {
        $this->client = Threads::client($_ENV['THREADS_TOKEN']);
    }

    public function enviaMensagemCadastroEvento(Eventos $evento)
    {

        $mensagem = 'Mais um evento cadastrado na plataforma https://eventosti.dev confira agora! ' . $evento->getNome() . ' em: ' . $evento->getLink();

        try {
            $post = $this->client->publish()->create(
                'pokemaobr',
                'TEXT',
                $mensagem
            );

            $create = $this->client->publish()->publish(
                'pokemaobr',
                $post
            )->data();

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

}