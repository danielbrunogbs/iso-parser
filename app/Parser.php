<?php

namespace App;

use Illuminate\Support\Facades\Log;

class Parser
{
	protected $data;

	public function set($string)
	{
		$this->data = $string;
	}

	public function mti()
	{
		return substr($this->data,0,4);
	}

	private function bitmap()
	{
		//PEGA O BITMAP ATRAVÉS DAS POSIÇÕES
		$bitmap = ['first' => substr($this->data,4,16), 'second' => substr($this->data,20,16)];
		$bits = []; //VARIÁVEL PARA ARMAZENAGEM

		foreach($bitmap as $map) //LÊ A ARRAY ARMAZENANDO O BITMAP 1 E 2
		{
			$array = str_split($map); //TRANSFORMA A STRING E ARRAY
			$binary = ''; //VARIÁVEL PARA ARMAZENAGEM

			foreach($array as $bit) //PEGA O BITMAP DA $ARRAY E LÊ CADA CARACTER
			{
				$convert = base_convert($bit,16,2); //CONVERTE EM BINÁRIO
				$convert = str_pad($convert,4,0,STR_PAD_LEFT); //MANTÉM O TAMANHO DE 4 CARACTERES COM 0 ALINHADO A ESQUERDA

				$binary .= $convert; //ACRESCENTA A VARIÁVEL $CONVERT NA VARIÁVEL DE ARMAZENAGEM
			}

			array_push($bits,$binary); //ARMAZENA O BITMAP CONVERTIDO EM BINÁRIO NA ARRAY
		}

		return $bits;
	}

	private function content()
	{
		$string = $this->data; //PEGA A O VALOR SALVO NO ATRIBUTO

		return substr($string,36); //PEGA O CONTEÚDO A PARTIR DA 36° POSIÇÃO
	}

	private function bits()
	{
		$bitmap = $this->bitmap(); //PEGA OS BITMAP EM BINÁRIO
		$bits = []; //VARIÁVEL PARA ARMAZENAGEM
		$bit_number = 0; //VARIÁVEL QUE INDICA O NUMÉRO DO BIT

		foreach($bitmap as $map) //FAZ A LEITURA DA ARRAY $BITMAP NA QUAL CONTEM OS BITMAP
		{
			$array = str_split($map); //TRANSFORMA A STRING E ARRAY

			foreach($array as $key => $bit) //LÊ CADA CARACTER DA $ARRAY
			{

				if($bit_number == 0) //TRANSFORMAR O VALOR 0 EM 1 POIS O BIT NÃO COMEÇA COM 0 E SIM EM 1
				{
					$bit_number = 1;
				}
				else
				{
					$bit_number = $bit_number + 1; //SOMA +1 PARA MONSTRAR A POSIÇÃO REAL DO BIT
				}

				//SE O BIT FOR IGUAL A 1 QUER DIZER QUE ESTÁ PRESENTE
				if($bit == 1)
					array_push($bits,$bit_number); //ENTÃO É SALVO NA ARRAY

			}
		}

		return $bits;
	}

	public function iso($iso)
	{
		$bits = $this->bits(); //PEGAR BITS
		$content_iso = $this->content(); //PEGAR CONTEÚDO FORA O SEGUNDO MAPA DE BITS
		$point = 0; //VARIÁVEL PARA ARMAZENAGEM
		$get = []; //VARIÁVEL PARA ARMAZENAGEM

		foreach($bits as $key => $bit)
		{
			$detail = $iso[$bit]; //[0] => Tipo de Dado, [1] => Tamanho do campo, [2] => Tamanho variável ou fixo

			if($bit != 1)
			{
				//SE CASO A 3 POSIÇÃO DA ARRAY FOR TRUE QUER DIZER QUE O TAMANHO DO BIT É VARIÁVEL CASO CONTRÁRIO É FIXO
				if($detail[2])
				{ //VARIÁVEL

					if($key == 1) //PULAR O PRIMEIRO BIT (BITMAP) POIS ELE NÃO É COLETADO
						$point = 0; //SETAR A POSIÇÃO ZERO
					
					$length_length = strlen($detail[1]); //CALCULAR O TAMANHO DO (TAMANHO) DO BIT
					$length = substr($content_iso, $point, $length_length); //PEGAR O VALOR REFERENTE AO TAMANHO DO CONTEÚDO
					
					//PEGAR O CONTEÚDO A PARTIR DA POSIÇÃO SOMADA COM O TAMANHO DO (TAMANHO) DO BIT
					$content = substr($content_iso, $point + $length_length, $length);

					$get[$bit] = $content; //ARMAZENANDO O BIT JUNTO COM O CONTEÚDO DENTRO DE UM ARRAY

					$point = $point + $length_length + $length; //SOMANDO A POSIÇÃO COM O TAMANHO DO BIT MAIS O TAMANHO DO CONTEÚDO

				}
				else
				{ //FIXO
					
					if($key == 1) //PULAR O PRIMEIRO BIT (BITMAP) POIS ELE NÃO É COLETADO
						$point = 0; //SETAR A POSIÇÃO ZERO

					$length = $detail[1]; //PEGANDO O TAMANHO DO CONTEÚDO FIXO

					//PEGAR CONTEÚDO A PARTIR DA POSIÇÃO
					$content = substr($content_iso, $point, $length);

					$get[$bit] = $content; //ARMAZENANDO O BIT JUNTO COM O CONTEÚDO DENTRO DE UM ARRAY

					$point = $point + $length; //SOMA A POSIÇÃO JUNTO COM O TAMANHO DO CONTEÚDO DO BIT

				}
			}
		}

		return $get;
	}
}