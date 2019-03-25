<?php

namespace App;

use Illuminate\Support\Facades\Log;

class Parser
{
	public $data;
	protected $iso_bits = [];
	protected $iso;
	protected $bitmap;
	protected $point_content;

	#### LEITURA DA ISO ####

	public function set($string)
	{
		$this->data = $string;
	}

	public function set_iso($iso)
	{
		$this->iso = $iso;
	}

	public function mti()
	{
		return substr($this->data,0,4);
	}

	private function bitmap()
	{
		$point = 4; //POSIÇÃO INICIAL
		$length = 16; //TAMANHO FIXO DO BITMAP
		$bitmaps = []; //VARIÁVEL DE ARMAZENAGEM
		$bits = []; //VARIÁVEL DE ARMAZENAGEM

		//BUSCA TODOS OS BITMAPS PRESENTES NA MENSAGEM
		for($stop = 0; $stop != 1; $point += 16)
		{
			$bitmap = substr($this->data, $point, $length);
			$first_bit = substr($this->data, $point, 1);
			$converted = base_convert($first_bit, 16, 2);
			$converted = str_pad($converted, 4, 0, STR_PAD_LEFT);
			$first_position = substr($converted, 0, 1);

			$bitmaps[] = $bitmap;

			if($first_position == 0)
				$stop = 1;
		}

		$this->point_content = $point; //SETA A POSIÇÃO PARA O CONTEÚDO CORRETO DA MENSAGEM

		//PEGA OS BITMAPS PRESENTES NA ARRAY
		foreach($bitmaps as $key => $bitmap)
		{
			$split = str_split($bitmap);
			$binary = null;

			foreach($split as $bit)
			{
				$convert = base_convert($bit, 16, 2);
				$convert = str_pad($convert, 4, 0, STR_PAD_LEFT);

				$binary .= $convert;
			}

			$bits[$key] = $binary;
		}

		return $bits;
	}

	private function content()
	{
		$string = $this->data; //PEGA A O VALOR SALVO NO ATRIBUTO

		return substr($string, $this->point_content); //PEGA O CONTEÚDO A PARTIR DA POSIÇÃO
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

		$this->iso_bits = $get;
	}

	//ANTES DE PEGAR O VALOR DO BIT, VERIFICA SE O MESMO EXISTE!
	public function get($bit)
	{
		return isset($this->iso_bits[$bit]) ? $this->iso_bits[$bit] : 'BIT '.$bit.' não encontrado!';
	}

	#### LEITURA DA ISO ####

	########################

	#### CIRAÇÃO DA ISO ####

	protected $mti;
	protected $bits = [];
	protected $count_bitmap = 1;

	//ADICIONAR O MTI DA MENSAGEM
	public function add_mti($mti)
	{
		$this->mti = $mti; //SALVA O MTI DA TRANSAÇÃO A SER GERADA
	}

	//MONTA O BIT DE ACORDO COM A ISO UTILIZADA
	public function data($bit_number, $content)
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
	public function get_iso()
	{
		$mti = $this->mti;
		$bitmap = $this->make_bitmap();
		$content = null;

		foreach($this->bits as $bit)
			$content .= $bit['length'] . $bit['content'];

		return $mti . $bitmap . $content;
	}
}