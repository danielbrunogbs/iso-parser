<?php

namespace App\Paynet;

use App\Isos\Paynet;
use App\Parser;

//MESSAGES
use App\Paynet\Messages\CommunicationTest as PaynetCommunicationTest;

class ResolveMessage
{
	public function resolve($message)
	{
		$isoParser = new Parser();

		//Seta a mensagem dentro da classe
		$isoParser->setMessage($message);

		//Iso que será utilizada para realizar o parse
		$isoParser->setIso(Paynet::getIso());

		switch ($isoParser->getMTI())
		{
			case '0800':
				return (new PaynetCommunicationTest($isoParser))->process();
				break;
			
			default:
				return null;
				break;
		}
	}
}