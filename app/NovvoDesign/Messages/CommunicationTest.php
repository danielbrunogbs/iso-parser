<?php

namespace App\NovvoDesign\Messages;

use App\Parser;
use App\Isos\NovvoDesign;

class CommunicationTest
{
	protected $parser;
	protected $successful = true;

	public function __construct(Parser $parser)
	{
		$this->parser = $parser;
	}

	public function success()
	{
		$iso = new Parser(NovvoDesign::getIso());

		$iso->addMTI('0810');
		$iso->addBit(7, $this->parser->getBit(7));
		$iso->addBit(11, $this->parser->getBit(11));
		$iso->addBit(12, $this->parser->getBit(12));
		$iso->addBit(13, $this->parser->getBit(13));
		$iso->addBit(39, '00');
		$iso->addBit(41, $this->parser->getBit(41));
		$iso->addBit(42, $this->parser->getBit(42));
		$iso->addBit(62, 'CONECTADO COM SUCESSO');

		return $iso;
	}

	public function error()
	{
		$iso = new Parser(NovvoDesign::getIso());

		$iso->addMTI('0810');
		$iso->addBit(7, $this->parser->getBit(7));
		$iso->addBit(11, $this->parser->getBit(11));
		$iso->addBit(12, $this->parser->getBit(12));
		$iso->addBit(13, $this->parser->getBit(13));
		$iso->addBit(39, '12');
		$iso->addBit(41, $this->parser->getBit(41));
		$iso->addBit(42, $this->parser->getBit(42));
		$iso->addBit(62, 'FALHA NA CONEXAO');

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