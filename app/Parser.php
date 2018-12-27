<?php

namespace App;

use Illuminate\Support\Facades\Log;

class Parser
{
	protected $data;

	public function verification($string)
	{
		$this->data = $string;
	}

	public function mti()
	{
		return substr($this->data,0,4);
	}

	public function bitmap()
	{
		$first = substr($this->data,4,16);
		$second = substr($this->data,20,16);
		
		dd(hexdec($first));

		$first = base_convert($first, 16, 2);
		$second = base_convert($second, 16, 2);

		if(strlen($first) > 64)
			die();

		if(strlen($second) > 64)
			die();


		return [0 => $first, 1 => $second];
	}

	public function iso()
	{
		$bitmap = $this->bitmap();
		$bits = [];
		$bit_number = 0;

		dd($bitmap);

		foreach($bitmap as $map)
		{
			$array = str_split($map);

			foreach($array as $key => $bit)
			{

				if($bit_number == 0)
				{
					$bit_number = 1;
				}
				else
				{
					$bit_number = $bit_number + 1;
				}

				if($bit == 1)
					array_push($bits,$bit_number);

			}
		}

		dd($bits);

		return $bits;
	}
}