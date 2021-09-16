<?php

namespace App\Service;

use App\Entity\Eventos;

class TelegramService {

    private function enviaMensagemCadastroEvento(int $chatId, Eventos $evento) {

        $mensagem = 'Mais um evento cadastrado na plataforma https://eventosti.dev confira agora! ' .$evento->getNome() . ' em: ' . $evento->getLink();

        try {
        $url = 'https://api.telegram.org/bot' . $_ENV['TELEGRAM_TOKEN'] . '/sendMessage?chat_id='.$chatId.'&text='.urlencode($mensagem);
        file_get_contents($url);
        }
        catch (Exception) {

        }

    }

}
