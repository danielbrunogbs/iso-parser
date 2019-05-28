<?php

namespace App;

use Illuminate\Support\Facades\Log;

class Parser
{
	public $data;
	protected $iso_bits = [];
	protected $iso;
	protected $content;

	public function __construct($iso = null)
	{
		//Forma interativa de iserir a iso
		if($iso)
			$this->setIso($iso);
	}

	public function setMessage($string)
	{
		$this->data = $string;
	}

	public function setIso($iso)
	{
		$this->iso = $iso;
	}

	#### LEITURA DA ISO ####

	public function getMTI()
	{
		return substr($this->data,0,4);
	}

	private function bitmap()
	{
		$end = 4; //Ponto final de onde deve começar a busca (pulando o MTI da mensagem)
		$bitmaps = []; //Array para armazenar os bitmaps convertidos
		$bitmap_length = 16; //Quantidade inicial para somente um bitmap presente na mensagem
		$stop = 0; //Variável para encerrar o looping

		//Variáveis usadas para fazer a verificação somente 2x (usamos somente 2 mapas de bitmap)
		$step = 1;
		$step_limit = env('AMOUNT_BITMAPS', 2); //Setado no env para trabalhar de forma flexível

		//Verificar se tem 1 ou 2 bitmaps presente na mensagem
		while($stop != 1) //Parar o looping quando encontrar todos os bitmaps
		{
			$string = $this->data;
			$bitmap = substr($string, $end, $bitmap_length);

			//Converte o primeiro byte do bitmap para binário
			$first = base_convert($bitmap[0], 16, 2);
			$first = str_pad($first, 4, 0, STR_PAD_LEFT);

			//Verifica se o primeiro caractér do byte é igual a 1 (indicando a presença de outro bitmap)
			($first[0] == 1) ? $end += 16 : $stop = 1;

			//Verifica a quantidade de vezes que já foi feita a verificacão
			if($step <= $step_limit)
			{
				//Faz a leitura dos caracteres do bitmap, converte para binário e armazena na array
				$bitmap = str_split($bitmap);
				$bitmaps[$step] = null;

				foreach($bitmap as $byte)
				{
					$bit = base_convert($byte, 16, 2);
					$bit = str_pad($bit, 4, 0, STR_PAD_LEFT);

					$bitmaps[$step] .= $bit;
				}

				$step++;
			}
			else
			{
				$stop = 1;
			}
		}

		//Pega o resto da mensagem ignorando o MTI e BITMAP (conteúdo, 16 x 2 + 4)
		$this->content = substr($this->data, ($bitmap_length * count($bitmaps)) + 4);

		return $bitmaps;
	}

	private function bits()
	{
		$bitmap = $this->bitmap(); //Pega o array contendo os bitmaps em binário
		$bits = []; //Array para armazenar os bits recolidos do conteúdo
		$bit_number = 0; //Indicador do número do bit

		foreach($bitmap as $map) //Faz a leitura da quantidade de bitmaps retornados
		{
			$array = str_split($map); //Separa a string para fazer a leitura de cada caractér

			foreach($array as $key => $bit)
			{
				//Não pode seguir o padrão de chaves da array, logo precisamos pular um digito a frente
				if($bit_number == 0)
				{
					$bit_number = 1;
				}
				else
				{
					//Realiza a soma de 1 para indicar a posição correta do BIT
					$bit_number++;
				}

				//Se o caracter referente ao BIT for igual a 1 indica que está presente e é salvo na array
				if($bit == 1)
					array_push($bits, $bit_number);
			}
		}

		return $bits;
	}

	private function iso()
	{
		$iso = $this->iso;
		$bits = $this->bits(); //Pega os bits presentes na mensagem
		$content_iso = $this->content; //Pega o conteúdo
		$point = 0; //Último ponto de parada dentro da string do conteúdo
		$get = []; //Armazena a chave do array de acordo com o número do bit e conteúdo do mesmo

		foreach($bits as $key => $bit)
		{
			$detail = $iso[$bit]; //[0] => Tipo de Dado, [1] => Tamanho do campo, [2] => Tamanho variável ou fixo

			if($bit != 1)
			{
				//Verifica se a posição consultada no array é true o false, caso true é variável, caso false, fixo
				if($detail[2]) //Variável
				{
					if($key == 1) //Pular o primeiro BIT pois é só indicação do segundo mapa de BITS
						$point = 0;
					
					//Calcular a quantidade de caracteres do tamanho do BIT
					$length_length = strlen($detail[1]);
					//Pegar o conteúdo
					$length = substr($content_iso, $point, $length_length);
					
					//Pegar o conteúdo pulando os caracteres do tamanho do BIT e o último ponto de parada
					$content = substr($content_iso, $point + $length_length, $length);

					//Armazena o BIT como chave de array e conteúdo dentro da chave (facilitar na consulta)
					$get[$bit] = $content;

					//Soma a última posição com a quantidade de caracteres do tamanho e o tamanho do conteúdo do BIT
					$point = $point + $length_length + $length;
				}
				else //Fixo
				{	
					if($key == 1)
						$point = 0;

					$length = $detail[1]; //Pegando o tamanho fixo do BIT

					//Pegar o conteúdo a partir da última posição setada
					$content = substr($content_iso, $point, $length);

					$get[$bit] = $content;

					$point = $point + $length;
				}
			}
		}

		$this->iso_bits = $get;
	}

	public function getBit($bit)
	{
		//Só executar o parse da mensagem 1x
		if(empty($this->iso_bits))
			$this->iso();

		//Antes de retornar o valor, verifica se o BIT existe dentro da array
		return isset($this->iso_bits[$bit]) ? $this->iso_bits[$bit] : false;
	}

	#### LEITURA DA ISO ####

	########################

	#### CRIAÇÃO DA ISO ####

	protected $mti;
	protected $bits = [];
	protected $count_bitmap = 1;

	//Armazena o MTI da mensagem
	public function addMTI($mti)
	{
		$this->mti = $mti;
	}

	//Armazena o BITS utilizados na mensagem
	public function addBit($bit, $content)
	{
		$iso = $this->iso;

		$detail = $iso[$bit];

		//Verifica se o tamanho do BIT é variável ou fixo
		if($detail[2]) //Variável
		{
			//Calcula o tamanho do conteúdo e calcula os caracteres do tamanho (Para definir LLvar OU LLLvar)
			$length = str_pad(strlen($content), strlen($detail[1]), 0, STR_PAD_LEFT);

			//Armazena o BIT na array
			$this->bits[$bit] = [$bit, $length, $content];
		}
		else //Fixo
		{
			//Define o tamanho como nulo, pois já é indicado na ISO que é um tamanho fixo
			$length = $detail[1];

			//Pega o conteúdo de acordo com o tamanho fixo (evitar quebra de mensagem)
			$content = substr($content, 0, $length);

			//Armazena o BIT na array (tamanho enviado como nulo, pois é um tamanho fixo)
			$this->bits[$bit] = [$bit, null, $content];
		}
	}

	//Monta o BITMAP de acordo com o BITS setados na mensagem
	public function makeBitmap()
	{
		$bits = $this->bits;
		$amount = null; //Armazena a quantidade de bitmaps

		//Calcular quantos bitmaps serão enviado na mensagem
		$amount = max($bits)[0] / 64; //Divide o BIT pelo tamanho de um bitmap
		$amount = (int) ceil($amount); //Depois arredonda para cima, indicando a quantidade de bitmap's

		//Respeitar o limite de bitmaps definido nas configurações
		if($amount > env('AMOUNT_BITMAPS'))
			$amount = env('AMOUNT_BITMAPS');

		$bitmap = str_pad('', 64 * $amount, '0');

		$init = 1; //Quantidade inicial de execução
		$active = 0; //Posição dentro do bitmap

		//Deixa as posições dos bit ativos que indicam a presença de outros map's na mensagem
		while($init < $amount)
		{
			$bitmap[$active] = '1';

			$active += 64;
			$init++;
		}

		//Seta os bit dentro do bitmap
		foreach($bits as $key => $detail)
		{
			//Subtração necessária para seguir o padrão de chaves da array que começa a partir do zero
			$bit = $key - 1;

			//Só marca como ativo o bit na mapa quando localizada a posição dentro do mesmo
			isset($bitmap[$bit]) ? $bitmap[$bit] = '1' : false;
		}

		$bitmap = str_split($bitmap); //Separa os caracteres do bitmap
		$return = null; //Armazena a string do bitmap convertido
		$bit = null; //Monta cada byte convertido em hexa
		$sum = 0; //Contador

		//Converte em hexadecimal
		foreach($bitmap as $byte)
		{
			$bit .= $byte; //Armazena o byte
			$sum++; //Acrescenta mais um

			//Quando chegar em 4 bytes
			if($sum == 4)
			{
				//Converte o byte em hexadecimal
				$return .= base_convert($bit, 2, 16);
				$sum = 0; //Limpa o contador
				$bit = null; //Limpa a variável do byte
			}
		}

		//Deixa a string em maiúsculo
		$return = strtoupper($return);

		return $return;
	}

	//Monta a mensagem de retorno
	public function getIso()
	{
		$mti = $this->mti;
		$bitmap = $this->makeBitmap();
		$content = null;

		foreach($this->bits as $bit)
			$content .= $bit[1] . $bit[2];

		return $mti . $bitmap . $content;
	}
}