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

		switch ($this->mti()) {

			case '0420':
				$parser = $this->iso(Paynet::getIso());
				return (new PaynetCommunicationTest($parser))->process();
				break;
			
			default:
				return null;
				break;
		
		}
	}
}