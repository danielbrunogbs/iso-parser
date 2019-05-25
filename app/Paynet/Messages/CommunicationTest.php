<?php

namespace App\Paynet\Messages;

use App\Parser;
use App\Isos\Paynet;

class CommunicationTest extends Parser
{
	protected $parser;
	protected $successful = true;

	public function __construct(Parser $parser)
	{
		$this->parser = $parser;
	}

	public function success()
	{
		$iso = new Parser(Paynet::getIso());

		$iso->addMTI('0810');
		$iso->addData(7, $this->parser->getBit(7));
		$iso->addData(11, $this->parser->getBit(11));
		$iso->addData(12, $this->parser->getBit(12));
		$iso->addData(13, $this->parser->getBit(13));
		$iso->addData(39, '00');
		$iso->addData(41, $this->parser->getBit(41));
		$iso->addData(42, $this->parser->getBit(42));
		$iso->addData(62, 'CONECTADO COM SUCESSO');

		return $iso;
	}

	public function error()
	{
		$iso = new Parser(Paynet::getIso());

		$iso->addMTI('0810');
		$iso->addData(7, $this->parser->getBit(7));
		$iso->addData(11, $this->parser->getBit(11));
		$iso->addData(12, $this->parser->getBit(12));
		$iso->addData(13, $this->parser->getBit(13));
		$iso->addData(39, '12');
		$iso->addData(41, $this->parser->getBit(41));
		$iso->addData(42, $this->parser->getBit(42));
		$iso->addData(62, 'FALHA NA CONEXAO');

		return $iso;
	}

	public function process()
	{
		$response = $this->successful ? $this->success() : $this->error();

		return [
			'payload' => $response->getIso()
		];
	}
}