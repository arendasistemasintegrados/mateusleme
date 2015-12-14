<?php
/*
 * E-cidade Software Publico para Gestao Municipal
 * Copyright (C) 2014 DBseller Servicos de Informatica
 * www.dbseller.com.br
 * e-cidade@dbseller.com.br
 *
 * Este programa e software livre; voce pode redistribui-lo e/ou
 * modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
 * publicada pela Free Software Foundation; tanto a versao 2 da
 * Licenca como (a seu criterio) qualquer versao mais nova.
 *
 * Este programa e distribuido na expectativa de ser util, mas SEM
 * QUALQUER GARANTIA; sem mesmo a garantia implicita de
 * COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM
 * PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais
 * detalhes.
 *
 * Voce deve ter recebido uma copia da Licenca Publica Geral GNU
 * junto com este programa; se nao, escreva para a Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 * 02111-1307, USA.
 *
 * Copia da licenca no diretorio licenca/licenca_en.txt
 * licenca/licenca_pt.txt
 */
class AcordosController extends AppController {
	public $name = 'Acordos';
	public $components = array (
			"RequestHandler",
			"Formatacao" 
	);
	public $helpers = array (
			"Javascript",
			"CakePtbr.Formatacao" 
	);
	
	/**
	 * Retorna as instituicoes utilizadas nos acordos
	 * 
	 * @return array
	 */
	private function getInstituicoes() {
		return $this->Acordo->Instituicao->getInstituicoesAcordos ();
	}
	
	/**
	 * Retorna os exercícios utilizados nos acordos de uma instituicao
	 * 
	 * @param integer $iInstituicao        	
	 * @return array
	 */
	private function getExercicios($iInstituicao) {
		$aExercicios = $this->Acordo->getExercicios ( $iInstituicao );
		
		return array_combine ( $aExercicios, $aExercicios );
	}
	public function index() {
		$aExercicios = array ();
		
		if (! empty ( $_GET ['instituicao'] )) {
			$this->data ['Filtro'] ['instituicao'] = $_GET ['instituicao'];
			
			$aExercicios = $this->getExercicios ( $_GET ['instituicao'] );
			
			if (! empty ( $_GET ['exercicio'] )) {
				$this->data ['Filtro'] ['exercicio'] = $_GET ['exercicio'];
			}
		}
		
		if (! empty ( $_GET ['mes'] )) {
			$this->data ['Filtro'] ['mes'] = $_GET ['mes'];
		}
		
		$this->set ( array (
				"aInstituicoes",
				"aExercicios",
				"aMeses" 
		), array (
				array_map ( 'utf8_encode', $this->getInstituicoes () ),
				$aExercicios,
				array (
						01 => 'Janeiro',
						02 => 'Fevereiro',
						03 => 'Março',
						04 => 'Abril',
						05 => 'Maio',
						06 => 'Junho',
						07 => 'Julho',
						08 => 'Agosto',
						09 => 'Setembro',
						10 => 'Outubro',
						11 => 'Novembro',
						12 => 'Dezembro' 
				) 
		) );
	}
	public function pesquisar() {
		unset ( $_GET ['url'] );
		
		if ($this->RequestHandler->isAjax ()) {
			
			$this->autoRender = false;
			
			$this->passedArgs ['page'] = $_POST ['page'];
			$this->passedArgs ['direction'] = $_POST ['sord'];
			$this->passedArgs ['sort'] = $_POST ['sidx'];
			$this->passedArgs ['limit'] = $_POST ['rows'];
			
			$aConditions = array (
					"Acordo.instituicao_id" => $_POST ['instituicao'],
					"Acordo.anousu" => $_POST ['exercicio'] 
			);
			
			if (! empty ( $_POST ['mes'] )) {
				$iCompetencia = ( int ) $_POST ['mes'] . $_POST ['exercicio'];
				
				$aConditions [] = "{$iCompetencia} between " . "(extract(month from Acordo.data_inicio)::varchar||extract(year from Acordo.data_inicio)::varchar)::integer" . " and (extract(month from Acordo.data_fim)::varchar||extract(year from Acordo.data_fim)::varchar)::integer";
			}
			
			$aAcordos = $this->paginate ( 'Acordo', $aConditions );
			$aRetorno = array ();
			
			foreach ( $aAcordos as $aAcordo ) {
				
				$aRetorno ['rows'] [] = array (
						'cell' => array (
								$aAcordo ['Acordo'] ['numero'],
								$this->Formatacao->formatDate ( $aAcordo ['Acordo'] ['data_assinatura'] ),
								$this->Formatacao->formatDate ( $aAcordo ['Acordo'] ['data_inicio'] ) . utf8_decode ( ' até ' ) . $this->Formatacao->formatDate ( $aAcordo ['Acordo'] ['data_fim'] ),
								$aAcordo ['Acordo'] ['objeto'] 
						),
						'id' => $aAcordo ['Acordo'] ['id'] 
				);
			}
			
			$aRetorno ['page'] = $_POST ['page'];
			$aRetorno ['total'] = $this->params ['paging'] ['Acordo'] ['pageCount'];
			$aRetorno ['records'] = $this->params ['paging'] ['Acordo'] ['count'];
			
			return $this->json ( $aRetorno );
		} else {
			$this->set ( "aParametros", $_GET );
		}
	}
	public function view($iId) {
		$this->loadModel ( 'AcordoAditamentoItem' );
		$this->loadModel ( 'Importacao' );
		
		$aAcordo = $this->Acordo->getAcordoById ( $iId );
		$aAditamentos = $this->Acordo->AcordoAditamento->getAditamentosByAcordo ( $iId );
		$aDocumentos = $this->Acordo->AcordoDocumento->getDocumentosByAcordo ( $iId );
		$aEmpenhos = $this->Acordo->AcordoEmpenho->getEmpenhosByAcordo ( $iId );
		$aItens = $this->AcordoAditamentoItem->getItensByAditamentoId ( $this->Acordo->AcordoAditamento->getUltimoIdAditamentoByAcordo ( $iId ) );
		
		$sDataImportacao = $this->Importacao->getUltimaImportacao ();
		
		$aAcordo ['Acordo'] ['valor_total'] = 0;
		if (! empty ( $aItens )) {
			$aAcordo ['Acordo'] ['valor_total'] = array_sum ( Set::extract ( '/AcordoAditamentoItem/valor_total', $aItens ) );
		}
		
		$this->set ( compact ( array (
				'aAcordo',
				'aAditamentos',
				'aDocumentos',
				'aEmpenhos',
				'aItens',
				'sDataImportacao' 
		) ) );
	}
	
	/**
	 * Retorna os exercicios para a instituição
	 * 
	 * @param integer $iInstituicao        	
	 * @return string json
	 */
	public function buscarExercicios($iInstituicao) {
		$this->autoRender = $this->layout = false;
		
		return $this->json ( $this->getExercicios ( $iInstituicao ) );
	}
	
	/**
	 * Faz o download do arquivo
	 * 
	 * @param integer $iIdDocumento        	
	 */
	public function baixarDocumento($iIdDocumento) {
		$aDocumento = $this->Acordo->AcordoDocumento->getDocumentoById ( $iIdDocumento );
		
		if (empty ( $aDocumento )) {
			$this->redirect ( 'index' );
		}
		
		$aArquivo = $this->Acordo->AcordoDocumento->baixarDocumento ( $aDocumento ['AcordoDocumento'] ['arquivo'] );
		
		if (empty ( $aArquivo )) {
			$this->redirect ( 'index' );
		}
		
		/**
		 * Verifica se o arquivo possui alguma extensão válida
		 */
		if (! preg_match ( '/(.*)\.(.+)$/i', trim ( $aDocumento ['AcordoDocumento'] ['nome'] ), $aExtensao )) {
			$this->redirect ( 'index' );
		}
		
		$this->view = 'Media';
		$aParametros = array (
				'id' => $aArquivo ['file'],
				'name' => $aExtensao [1],
				'extension' => strtolower ( $aExtensao [2] ),
				'download' => true,
				'path' => $aArquivo ['path'] 
		);
		
		$this->set ( $aParametros );
	}
}
?>