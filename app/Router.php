<?php

namespace App;

use App\Paynet\ResolveMessage as Paynet;

class Router
{
	public function routing($message)
	{
		//MODEL CRIADA, CASO PRECISE DE 2 OU MAIS APLICAÇÕES EM UM SÓ ISO MODULE
		$header = 'empty';

		switch ($header) {

			case 'empty':
				$resolve = new Paynet();
				return $resolve->resolve($message);
				break;
			
			default:
				return false;
				break;
				
		}
	}
}