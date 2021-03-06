<?php
/**
 * Created by PhpStorm.
 * User: azhe403
 * Date: 28/08/18
 * Time: 21:07
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use src\Model\Group;
use src\Utils\Words;
use src\Utils\Time;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class KataCommand extends UserCommand
{
    protected $name = 'kata';
    protected $description = 'Add word to blacklist or whitelist';
    protected $usage = '<kata>';
    protected $version = '1.0.0';

    public function execute()
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $mssg_id = $message->getMessageId();
        $from_id = $message->getFrom()->getId();

        $time = $message->getDate();
	    $time1 = Time::jedaNew($time);

        $pecah = explode(' ', $message->getText());
	    $isSudoer = Group::isSudoer($from_id);
        if ($isSudoer) {
            switch ($pecah[1]) {
                case 'blok':
                    $katas = [
                        'kata' => $pecah[2],
                        'kelas' => 'blok',
                        'id_telegram' => $message->getFrom()->getId(),
                        'id_grup' => $chat_id
                    ];
	                $blok = json_decode(Words::tambahKata($katas), true);
                    $text = '<b>Diblok : </b>' . $pecah[2] .
                        "\n<b>Status : </b>" . $blok['message'];
                    break;

                case 'biar':
                    $katas = [
                        'kata' => $pecah[2],
                        'kelas' => 'biar',
                        'id_telegram' => $message->getFrom()->getId(),
                        'id_grup' => $chat_id
                    ];
	                $blok = json_decode(Words::tambahKata($katas), true);
                    $text = '<b>Dibiar : </b>' . $pecah[2] .
                        "\n<b>Status : </b>" . $blok['message'];
                    break;

                case 'del':
	                $del = json_decode(Words::hapusKata($pecah[2]), true);
                    $text = '<b>Hapus : </b>' . $pecah[2] .
                        "\n<b>Status : </b>" . $del['message'];
                    break;

                case 'update':
	                Words::simpanJson();
                    $text = 'Basis data kata berhasil di perbarui';
                    break;

                case 'all':
                    $text = "🗒 <b>Ini list kata</b>\n"
	                    . Words::allBadword();

                    break;

                default:
                    $text = '<b>Penggunaan /kata</b>' .
                        "\n<code>/kata [command] katamu</code>" .
                        "\n<b>Command : </b><code>blok, biar, del</code>";
            }
        } else {
            $text = "<b>You isn't sudoer</b>";
        }
	
	    $time2 = Time::jedaNew($time);
        $time = "\n\n ⏱ " . $time1 . ' | ⏳ ' . $time2;

        if ($text != '') {
	        Words::simpanJson();
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text' => $text . $time,
                'reply_to_message_id' => $mssg_id,
                'parse_mode' => 'HTML'
            ]);
        }
    }
}
