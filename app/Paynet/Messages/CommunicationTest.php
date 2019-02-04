<?php

namespace App\Paynet\Messages;

use App\Parser;

class CommunicationTest extends Parser
{
	protected $parser;

	public function __construct($parser)
	{
		$this->parser = $parser;

		$this->process();
	}

	public function process()
	{
		return dd($this->parser);
	}
}