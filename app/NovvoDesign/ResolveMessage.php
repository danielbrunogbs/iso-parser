<?php

namespace App\NovvoDesign;

use App\Isos\NovvoDesign;
use App\Parser;

//MESSAGES
use App\NovvoDesign\Messages\CommunicationTest;

class ResolveMessage
{
	public function resolve($message)
	{
		$isoParser = new Parser();

		//Seta a mensagem dentro da classe
		$isoParser->setMessage($message);

		//Iso que será utilizada para realizar o parse
		$isoParser->setIso(NovvoDesign::getIso());

		switch ($isoParser->getMTI())
		{
			case '0800':
				return (new CommunicationTest($isoParser))->process();
				break;
			
			default:
				return null;
				break;
		}
	}
}