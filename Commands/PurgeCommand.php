<?php
/**
 * Created by PhpStorm.
 * User: Azhe
 * Date: 12/08/2018
 * Time: 20.13
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use src\Utils\Time;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;


class PurgeCommand extends UserCommand
{
    protected $name = 'purge';
    protected $description = 'Remove message only target with range';
    protected $usage = '/purge>';
    protected $version = '1.0.0';

    /**
     * Execute command
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $mssg_id = $message->getMessageId();
        $pecah = explode(' ', $message->getText());
        $repMssg = $message->getReplyToMessage();

        $time = $message->getDate();
	    $time = Time::jeda($time);

        if (isset($repMssg)) {
            $repMssgId = $repMssg->getMessageId();
            $deleted = 0;
            while ($mssg_id >= $repMssgId) {
                $del = Request::deleteMessage([
                    'chat_id' => $chat_id,
                    'message_id' => $mssg_id
                ]);
                if ($del->isOk()) {
                    $mssg_id--;
                    $deleted++;
                }
                $repMssgId++;
            }
            $text = "\nSebanyak : " . $deleted;
        } else if (isset($pecah[1]) && is_numeric($pecah[1])) {
            $range = $mssg_id - $pecah[1];
	        $num = 0;
            for ($x = $mssg_id; $x >= $range; $x--) {
                $del = Request::deleteMessage([
                    'chat_id' => $chat_id,
                    'message_id' => $x
                ]);
	            if ($del->isOk()) {
		            $num++;
	            }
            }
	        $text = "Selesai hapus " . $num;
        } else {
            $text = "Reply sampai mana pesan akan di purge, atau jumlah ygn akan di purge";
        }

        if (isset($text)) {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => $text . $time,
                'parse_mode' => 'HTML'
            ]);
        }
    }
}
