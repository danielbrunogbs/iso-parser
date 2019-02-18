<?php

namespace App\Paynet\Messages;

use App\Parser;
use App\Isos\Paynet;

class CommunicationTest extends Parser
{
	protected $parser;

	public function __construct($parser)
	{
		$this->parser = $parser;
	}

	public function success()
	{
		$this->set_iso(Paynet::getIso());

		$this->add_mti('0810');
		$this->data(7, $this->get(7));
		$this->data(11, $this->get(11));
		$this->data(12, $this->get(12));
		$this->data(13, $this->get(13));
		$this->data(39, '00');
		$this->data(41, $this->get(41));
		$this->data(42, $this->get(42));
		$this->data(62, 'CONECTADO COM SUCESSO');
		$this->data(129, 'TESTE');

		return true;
	}

	public function process()
	{
		$response = $this->success() ? $this->get_iso() : false;

		return [
			'forwarding' => false,
			'payload' => $response
		];
	}
}