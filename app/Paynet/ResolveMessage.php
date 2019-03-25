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

		//SALVA A MENSAGEM QUE SERÁ REALIZADO A PARSE
		$isoParser->set($message);

		//ISOS UTILIZADAS DURANTE AS TROCAS DE MENSAGENS
		$isoParser->iso(Paynet::getIso());

		switch ($isoParser->mti()) {

			case '0800':
				return (new PaynetCommunicationTest($isoParser))->process();
				break;
			
			default:
				return null;
				break;
		
		}
	}
}