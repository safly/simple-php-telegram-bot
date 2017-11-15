<?php
/**
 * BotHook
 * 
 * Class to interact with a telegram bot, and make it answer to some commands
 * 
 * Author: waterblue
 * Date: 2017/11/14
 */

include_once(conf.php);


$content = file_get_contents("php://input");
$update = json_decode($content, true);
$chatID = $update["message"]["chat"]["id"];
$message = $update["message"]["text"];

$hook = new BotHook(BOT_TOKEN, ONLYTRUSTED, &$trusted, $chatID, $message);

class BotHook
{
	private $api_url = '';

	private $commands = array(
		'/help'=>'help',
		'/uptime'=>'uptime',
		'/uname'=>'uname',
	);
	
	private $chat_id = '';

	public function __construct($bottoken, $onlytrusted, &$trusted $chat_id, $message)
	{
		$this->api_url = 'https://api.telegram.org/bot' . $bottoken;
		$this->chat_id = $chat_id;

		if (strpos($message, '/') === 0) {
			$this->command($message);
		}
	}

	private function isTrusted()
	{
		if (in_array($this->chat_id, $this->trusted)) {
			return true;
		}

		return false;
	}

	private function log($message) {
		error_log(date("Y-m-d H:i:s") . " - " . $message . "\n", 3, 'log.log');
	}

	private function command($command)
	{
		if ($trusted) && (!$this->isTrusted()) { $this->unauthorized(); }

		if (!in_array($command, array_keys($this->commands))) {
			$answer = "Unknown command, try /help to see a list of commands";
		} else {
			$answer = $this->{$this->commands[$command]}();
		}

		$this->send($answer);
	}

	private function send($message)
	{
		if (strlen($message) > 0) {
			$send = $this->api_url . "/sendmessage?chat_id=" . $this->chat_id . "&text=" . urlencode($message);
			file_get_contents($send);
			return true;
		}

		return false;
	}

	private function unauthorized()
	{
		$message = "You are not authorized to use commands in this bot!";
		return $this->send($message);
	}

	private function help()
	{
		$message = "/uptime - Retrieves the uptime of the server" . chr(10);
		$message .= "/uname - Retrieves the server name, build and kernel";

		return $message;
	}

	private function uptime()
	{
		return "Server uptime:". exec('uptime');
	}

	private function uname()
	{
		return exec('uname -a');
	}
}
