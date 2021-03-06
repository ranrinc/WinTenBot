<?php
/**
 * Created by PhpStorm.
 * User: Azhe
 * Date: 11/25/2018
 * Time: 6:12 AM
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use src\Handlers\MessageHandlers;
use src\Model\Group;
use src\Model\Members;

class PromoteCommand extends UserCommand
{
	protected $name = 'promote';
	protected $description = 'Promote chat member (bot must admin)';
	protected $usage = '/promote';
	protected $version = '1.0.0';
	
	/**
	 * Execute command
	 *
	 * @return \Longman\TelegramBot\Entities\ServerResponse
	 * @throws \Longman\TelegramBot\Exception\TelegramException
	 */
	public function execute()
	{
		$promoteRes = null;
		$tindakan = '';
		$message = $this->getMessage();
		$mHandler = new MessageHandlers($message);
		$chat_id = $message->getChat()->getId();
		$from_id = $message->getFrom()->getId();
		$repMssg = $message->getReplyToMessage();
		$pecah = explode(' ', $message->getText(true));
		
		$senderId = $message->getFrom()->getId();
		$promoteByName = trim($message->getFrom()->getFirstName() . ' ' . $message->getFrom()->getLastName());
		
		if ($repMssg != '') {
			$promotedName = trim($repMssg->getFrom()->getFirstName() . ' ' . $repMssg->getFrom()->getLastName());
			$repFrom_id = $repMssg->getFrom()->getId();
			$isAdmin = Group::isAdmin($from_id, $chat_id);
			$isSudoer = Group::isSudoer($from_id);
			if ($isAdmin || $isSudoer) {
				if ($pecah[0] == '-d') {
					$mHandler->sendText('Depromoting chat member..');
					$promoteRes = Members::promote($chat_id, $repFrom_id);
				} else {
					$mHandler->sendText('Promoting chat member..');
					$promoteRes = Members::promote($chat_id, $repFrom_id);
				}
			}
		} else {
			if ($pecah[0] == '-d') {
				$mHandler->sendText('Depromoting chat member..');
				$promoteRes = Members::demote($chat_id, $from_id);
			} else {
				$mHandler->sendText('Promoting chat member..');
				$promoteRes = Members::promote($chat_id, $from_id);
			}
			$promotedName = $promoteByName;
		}
		
		if ($promoteRes->isOk()) {
			if ($pecah[0] != '-d') {
				$text = "<a href='tg://user?id=" . $from_id . "'>$promotedName</a> menjadi Admin " .
					"\nDirekomendasikan oleh <a href='tg://user?id=$senderId'>" . $promoteByName . '</a>';
			} else {
				$text = "<a href='tg://user?id=" . $from_id . "'>$promotedName</a> tidak menjadi Admin " .
					"\nDiturunkan oleh <a href='tg://user?id=$senderId'>" . $promoteByName . '</a>';
			}
		} else {
			$text = '<b>🚫 Status : </b><code>' . $promoteRes->getDescription() . '.</code>';
		}
		
		return $mHandler->editText($text);
	}
}
