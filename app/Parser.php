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

	#### CIRAÇÃO DA ISO ####

	protected $mti;
	protected $bits = [];
	protected $count_bitmap = 1;

	//ADICIONAR O MTI DA MENSAGEM
	public function addMTI($mti)
	{
		$this->mti = $mti; //SALVA O MTI DA TRANSAÇÃO A SER GERADA
	}

	//MONTA O BIT DE ACORDO COM A ISO UTILIZADA
	public function addData($bit_number, $content)
	{
		$point = 64; //TAMANHO DO PRIMEIRO MAPA DE BITMAP
		$count_bitmap = 1;

		//CALCULA QUANTOS BITMAP'S VÃO ESTAR PRESENTES NA MENSAGEM
		for($stop = 0;$stop != 1;$point += 64)
		{
			if($bit_number > $point)
			{
				$count_bitmap++;

				if($count_bitmap > $this->count_bitmap)
					$this->count_bitmap = $count_bitmap;
			}
			else
			{
				$stop = 1;
			}
		}

		//SE CASO O VALOR NÃO FOR VARIÁVEL UTILIZAR O TAMANHO PADRÃO PARA PEGAR O CONTEÚDO
		$length = $this->iso[$bit_number][1];

		//SE CASO FOR VARIÁVEL, CALCULAR O TAMANHO DO CONTEÚDO PARA ENVIAR
		if($this->iso[$bit_number][2])
			$length = str_pad(strlen($content), strlen($this->iso[$bit_number][1]), 0, STR_PAD_LEFT);

		//PEGA O CONTEÚDO DE ACORDO COM O TAMANHO
		$content = substr($content, 0, $length);

		//SE CASO O TAMANHO FOR FIXO, LIMPA A VARIÁVEL, POIS O VALOR NÃO DEVE ESTAR PRESENTE NA ISO
		if(!$this->iso[$bit_number][2])
			$length = null;

		$this->bits[$bit_number] = ['bit' => $bit_number, 'length' => $length, 'content' => $content];
	}

	//MONTA O BITMAP
	public function make_bitmap()
	{
		$bitmaps = null;

		//DEFINE OS BLOCOS DE BITMAP INDICANDO A PRESENÇA DOS OUTROS
		for($i = 1;$i <= $this->count_bitmap;$i++)
		{
			$index = '1';

			if($i == $this->count_bitmap)
				$index = '0';

			$bitmap = str_pad('', 64, '0');
			$bitmap[0] = $index;

			$bitmaps .= $bitmap;
		}

		//SETA A POSIÇÃO DOS BITMAP
		foreach($this->bits as $key => $bit)
		{
			$bitmaps[$key - 1] = 1;
		}

		$count = 64 * $this->count_bitmap; //CALCULO QUANTOS BITMAPS VOU CONVERTER
		$bitmap = str_pad('', (16 * $this->count_bitmap), 0); //MONTO O TAMANHO DE BITMAP (HEXADECIMAL)
		$bitmap_position = 0; //VARIÁVEL PARA CONTAGEM (POSICIONAR CORRETAMENTE OS BYTES)

		//USO O CALCULO DE TODOS OS BITMAPS E VOU CONVERTENDO DE 4 EM 4 FECHANDO NO TOTAL DE 16 LOOPS (SE CASO 1 BITMAP)
		for($i = 0;$i < $count;$i++)
		{
			$format = $bitmaps[$i] . $bitmaps[$i + 1] . $bitmaps[$i + 2] . $bitmaps[$i + 3];
			$bitmap[$bitmap_position] = base_convert($format, 2, 16);
			$i += 3;
			$bitmap_position++;
		}

		return $bitmap;
	}

	//MONTA A MENSAGEM DE RETORNO
	public function getIso()
	{
		$mti = $this->mti;
		$bitmap = $this->make_bitmap();
		$content = null;

		foreach($this->bits as $bit)
			$content .= $bit['length'] . $bit['content'];

		return $mti . $bitmap . $content;
	}
}