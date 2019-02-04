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
		$this->data = $message;
		dd($this->iso(Paynet::getIso()));

		switch ($this->mti()) {

			case '9102':
				return new PaynetCommunicationTest($this->iso(Paynet::getIso()));
				break;
			
			default:
				return false;
				break;
		
		}
	}
}