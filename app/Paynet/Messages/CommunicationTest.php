<?php

namespace App\Paynet\Messages;

use App\Parser;

class CommunicationTest extends Parser
{
	protected $parser;

	public function __construct($parser)
	{
		$this->parser = $parser;
	}

	public function success()
	{
		//CRIAR FUNÇÃO DE RESPOSTA
	}

	public function process()
	{
		$response = $this->success();

		return [
			'forwarding' => false,
			'payload' => $response->get_iso()
		];
	}
}