<?php

namespace App;

use App\NovvoDesign\ResolveMessage as NovvoDesign;

class Router
{
	public function routing($message)
	{
		//MODEL CRIADA, CASO PRECISE DE 2 OU MAIS APLICAÇÕES EM UM SÓ ISO MODULE
		$header = 'empty';

		switch ($header)
		{
			case 'empty':
				return (new NovvoDesign())->resolve($message);
				break;
			
			default:
				return false;
				break;
		}
	}
}