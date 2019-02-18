<?php

namespace App;

use Illuminate\Support\Facades\Log;

class Parser
{
	protected $data;
	protected $iso;
	protected $bits = [];
	protected $bitmap;
	protected $mti;
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

		return substr($string, $this->point_content); //PEGA O CONTEÚDO A PARTIR DA 36° POSIÇÃO
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

	//ANTES DE PEGAR O VALOR DO BIT, VERIFICA SE O MESMO EXISTE!
	public function get($bit)
	{
		return isset($this->parser[$bit]) ? $this->parser[$bit] : 'BIT '.$bit.' não encontrado!';
	}

	#### LEITURA DA ISO ####

	########################

	#### CIRAÇÃO DA ISO ####

	//ADICIONAR O MTI DA MENSAGEM
	public function add_mti($mti)
	{
		$this->mti = $mti; //SALVA O MTI DA TRANSAÇÃO A SER GERADA
	}

	//MONTA O BIT DE ACORDO COM A ISO UTILIZADA
	public function data($bit_number, $content)
	{
		$bit = $this->iso[$bit_number]; //PEGA AS INFORAMAÇÕES DO BIT NA ISO
		$length = null;
		$length_length = strlen($bit[1]); //PEGA O TAMANHO DO TAMANHO PADRÃO

		//VERIFICA SE O MESMO É TAMANHO VARIÁVEL OU FIXO, SE CASO FOR, ELE JÁ CALCULA O TAMANHO DA STRING E SETA O TAMANHO
		if($bit[2])
			$length = str_pad(strlen($content),$length_length,0,STR_PAD_LEFT);

		$this->bits[$bit_number] = ['length' => $length, 'content' => $content]; //SALVA AS INFORMAÇÕES NA ARRAY
	}

	//CONVERTER O BITMAP PARA HEXA
	public function make_bitmap()
	{
		$bitmap = null;

		for($i = 0;$i <= 127;$i++)
		{
			$converter = $this->bitmap[$i] . $this->bitmap[$i + 1] . $this->bitmap[$i + 2] . $this->bitmap[$i + 3];
			$i = $i + 3;

			//dd($converter);

			$base = base_convert($converter, 2, 16);
			$bitmap .= $base;
		}

		//VERIFICA SE É NECESSÁRIO ENVIAR O SEGUNDO MAPA DE BITS
		if($this->bitmap[0] == 0)
			$bitmap = substr($bitmap,0,16);

		return $bitmap;
	}

	//MONTA A MENSAGEM DE RESPOSTA
	public function get_iso()
	{
		//SE A VARIÁVEL BITMAP ESTIVER VAZIA, COLOCAR OS VALORES PADRÕES
		if(empty($this->bitmap))
			$this->bitmap = str_pad(0,128,0);

		$content = null;

		//ADICIONA TODOS OS BITS NO BITMAP
		foreach($this->bits as $bit => $param)
		{
			//QUANDO ENCONTRAR UM BIT MAIOR QUE 64 (PRIMEIRO MAPA DE BITS) ELE INCLUI O SEGUNDO MAPA DE BITS
			if($bit > 64)
				$this->bitmap[0] = 1;

			$bit_position = $bit - 1; //POSIÇÃO REAL DO BIT
			$this->bitmap[$bit_position] = 1;

			//MONTA O CONTEÚDO DA MENSAGEM
			$content .= $param['length'].$param['content'];
		}

		$bitmap = $this->make_bitmap();

		return $this->mti.$bitmap.$content;
	}
}