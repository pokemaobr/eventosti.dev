<?php

namespace App\Service;

use App\Entity\Call4papers;
use App\Entity\Eventos;

class TelegramService {

    public function enviaMensagemCadastroEvento(int $chatId, Eventos $evento) {

        $mensagem = 'Mais um evento cadastrado na plataforma https://eventosti.dev confira agora! ' .$evento->getNome() . ' em: ' . $evento->getLink();

        try {
        $url = 'https://api.telegram.org/bot' . $_ENV['TELEGRAM_TOKEN'] . '/sendMessage?chat_id='.$chatId.'&text='.urlencode($mensagem);
        file_get_contents($url);
        }
        catch (Exception) {

        }

    }

    public function enviaMensagemCadastroCall4Papers(int $chatId, Call4papers $evento) {

        $mensagem = 'Mais um Call 4 Papers cadastrado na plataforma https://eventosti.dev confira agora! ' .$evento->getNome() . ' em: ' . $evento->getLink();

        try {
            $url = 'https://api.telegram.org/bot' . $_ENV['TELEGRAM_TOKEN'] . '/sendMessage?chat_id='.$chatId.'&text='.urlencode($mensagem);
            file_get_contents($url);
        }
        catch (Exception) {

        }

    }

}
