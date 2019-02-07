<?php

namespace App\Paynet;

use App\Isos\Paynet;
use App\Parser;

//MESSAGES
use App\Paynet\Messages\CommunicationTest as PaynetCommunicationTest;

class ResolveMessage extends Parser
{
	public function resolve($message)
	{
		//SALVA A MENSAGEM QUE SERÁ REALIZADO A PARSE
		$this->data = $message;

		//ISOS UTILIZADAS DURANTE AS TROCAS DE MENSAGENS
		$paynet = $this->iso(Paynet::getIso());

		switch ($this->mti()) {

			case '0800':
				return (new PaynetCommunicationTest($paynet))->process();
				break;
			
			default:
				return null;
				break;
		
		}
	}
}