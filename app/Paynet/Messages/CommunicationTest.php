<?php

namespace App\Paynet\Messages;

use App\Parser;
use App\Isos\Paynet;

class CommunicationTest extends Parser
{
	protected $parser;

	public function __construct(Parser $parser)
	{
		$this->parser = $parser;
	}

	public function success()
	{
		$this->parser->set_iso(Paynet::getIso());

		$this->parser->add_mti('0810');
		$this->parser->data(7, $this->parser->get(7));
		$this->parser->data(11, $this->parser->get(11));
		$this->parser->data(12, $this->parser->get(12));
		$this->parser->data(13, $this->parser->get(13));
		$this->parser->data(39, '00');
		$this->parser->data(41, $this->parser->get(41));
		$this->parser->data(42, $this->parser->get(42));
		$this->parser->data(62, 'CONECTADO COM SUCESSO');

		return true;
	}

	public function process()
	{
		$response = $this->success() ? $this->parser->get_iso() : false;

		return [
			'forwarding' => false,
			'payload' => $response
		];
	}
}