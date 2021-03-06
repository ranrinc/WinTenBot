<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;
use src\Handlers\MessageHandlers;
use src\Model\Group;
use src\Model\Settings;
use src\Utils\Words;

/**
 * Generic message command
 */
class GenericmessageCommand extends SystemCommand
{
    protected $name = 'genericmessage';
    protected $description = 'Handle generic message';
    protected $version = '1.0.0';

    /**
     * Execute command
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $pesan = $this->getMessage()->getText();
        $message = $this->getMessage();
        $mHandler = new MessageHandlers($message);
        $from_id = $message->getFrom()->getId();
        $from_first_name = $message->getFrom()->getFirstName();
        $from_last_name = $message->getFrom()->getLastName();
        $from_username = $message->getFrom()->getUsername();
        $chat_id = $message->getChat()->getId();
        $chat_username = $message->getChat()->getUsername();
        $chat_type = $message->getChat()->getType();
        $chat_title = $message->getChat()->getTitle();
        $repMsg = $this->getMessage()->getReplyToMessage();

        $kata = strtolower($pesan);
        $pesanCmd = explode(' ', strtolower($pesan))[0];

        // Pindai kata
        if (Words::isBadword($kata)) {
            $mHandler->deleteMessage();
        }

        // Perika apakah Aku harus keluar grup?
        if (isRestricted
            && !$message->getChat()->isPrivateChat()
            && Group::isMustLeft($message->getChat()->getId())) {
            $text = 'Sepertinya saya salah alamat. Saya pamit dulu..' .
                "\nGunakan @WinTenBot";
            $mHandler->sendText($text);
            return Request::leaveChat(['chat_id' => $chat_id]);
        }

        // Command Aliases
        switch ($pesanCmd) {
            case 'ping':
                return $this->telegram->executeCommand('ping');
                break;
            case '@admin':
                return $this->telegram->executeCommand('report');
                break;
            case Words::cekKandungan($pesan, '#'):
                return $this->telegram->executeCommand('get');
                break;
        }

        // Chatting
        switch (true) {
            case Words::cekKata($kata, 'gan'):
                $chat = 'ya gan, gimana';
                break;
            case Words::cekKata($kata, 'mau tanya'):
                $chat = 'Langsung aja tanya gan';
                break;
            case Words::cekKata($kata, thanks):
                $chat = 'Sama-sama, senang bisa membantu gan...';
                break;

            default:
                break;
        }

        if ($from_username == '') {
            $group_data = Settings::getNew(['chat_id' => $chat_id]);
            if ($group_data[0]['enable_warn_username'] == 1) {
                $limit = $group_data[0]['warning_username_limit'];
                $chat = "Hey, You..!\nSegera pasang username, atau kick/ban." .
                    "\nPeringatan %2/$limit tersisa";
                $btn_markup[] = ['text' => 'Pasang Username', 'url' => urlStart . '?start=username'];
                $mHandler->deleteMessage($group_data[0]['last_warning_username_message_id']);
                $r = $mHandler->sendText($chat, null, $btn_markup);
                Settings::saveNew([
                    'last_warning_username_message_id' => $r->result->message_id,
                    'chat_id' => $chat_id,
                ], [
                    'chat_id' => $chat_id,
                ]);
                return $r;
            }
        }

        $mHandler->sendText($chat, null, $btn_markup);

        if ($repMsg !== null) {
            if ($message->getChat()->getType() != "private") {
                $mssgLink = 'https://t.me/' . $chat_username . '/' . $message->getMessageId();
                $chat = "<a href='tg://user?id=" . $from_id . "'>" . $from_first_name . '</a>' .
                    " mereply <a href='" . $mssgLink . "'>pesan kamu" . '</a>' .
                    ' di grup <b>' . $chat_title . '</b>'
                    . "\n" . $message->getText();
                $data = [
                    'chat_id' => $repMsg->getFrom()->getId(),
                    'text' => $chat,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ];

                if ($chat_username != '') {
                    $btn_markup = [['text' => '💬 Balas Pesan', 'url' => $mssgLink]];
                    $data['reply_markup'] = new InlineKeyboard([
                        'inline_keyboard' => array_chunk($btn_markup, 2),
                    ]);
                }

                return Request::sendMessage($data);
            } else {
                $chat_id = $repMsg->getCaptionEntities()[3]->getUrl();
                $chat_id = str_replace("tg://user?id=", "", $chat_id);

                $data = [
                    'chat_id' => $chat_id,
                    'text' => "lorem",
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ];

                return Request::sendMessage($data);
            }
        }

        $pinned_message = $message->getPinnedMessage()->getMessageId();
        if (isset($pinned_message)) {
            return $this->telegram->executeCommand('pinnedmessage');
        }
    }
}

