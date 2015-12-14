<?php
/**
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBSeller Servicos de Informatica
 *                            www.dbseller.com.br
 *                         e-cidade@dbseller.com.br
 *
 *  Este programa e software livre; voce pode redistribui-lo e/ou
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
 *  publicada pela Free Software Foundation; tanto a versao 2 da
 *  Licenca como (a seu criterio) qualquer versao mais nova.
 *
 *  Este programa e distribuido na expectativa de ser util, mas SEM
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais
 *  detalhes.
 *
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU
 *  junto com este programa; se nao, escreva para a Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *  02111-1307, USA.
 *
 *  Copia da licenca no diretorio licenca/licenca_en.txt
 *                                licenca/licenca_pt.txt
 */

/**
 * Class LicitacoesController
 * Controller para a estrutura de licitacoes
 */
class LicitacoesController extends AppController {
	public $helpers = array (
			'CakePtbr.Formatacao' 
	);
	public $name = 'Licitacoes';
	public $components = array (
			'RequestHandler' 
	);
	public function index() {
		$this->set ( "aExercicio", $this->getAnos () );
		$this->set ( "aModalidade", $this->getModalidades () );
		$this->set ( "aSituacao", $this->getSituacao () );
		$this->set ( "aInstituicao", $this->getInstituicoes () );
	}
	
	/**
	 * Responsável por pesquisar os dados e retorná-los para a grid
	 */
	public function pesquisar() {
	}
	
	/**
	 * Busca os dados para apresentar na grid
	 * 
	 * @return mixed
	 */
	public function pesquisarLicitacoes() {
		$this->autoRender = false;
		$aWhere = array ();
		if (! empty ( $_POST ['instituicao'] )) {
			$aWhere = array (
					"instituicao_id = {$_POST["instituicao"]}" 
			);
		}
		if (! empty ( $_POST ['exercicio'] )) {
			$aWhere [] = "anousu = {$_POST['exercicio']}";
		}
		if (! empty ( $_POST ["mes"] )) {
			$aWhere [] = "extract(month from datapublicacao) = {$_POST["mes"]}";
		}
		if (! empty ( $_POST ['modalidade'] )) {
			$aWhere [] = "tipocompra = '{$_POST['modalidade']}'";
		}
		if (! empty ( $_POST ['situacao'] )) {
			$aWhere [] = "situacao = '{$_POST['situacao']}'";
		}
		
		$aLicitacoes = $this->Licitacao->getLicitacoes ( ($aWhere) );
		$aDadosGrid = $this->prepararDadosGrid ( $aLicitacoes );
		
		return $this->json ( $aDadosGrid );
	}
	
	/**
	 * Retorna os anos disponíveis
	 * 
	 * @return array
	 */
	private function getAnos() {
		return $this->Licitacao->getAnos ();
	}
	
	/**
	 * Retorna as modalidades disponíveis
	 * 
	 * @return array
	 */
	private function getModalidades() {
		return $this->Licitacao->getModalidades ();
	}
	
	/**
	 * Retorna as situações disponíveis
	 * 
	 * @return array
	 */
	private function getSituacao() {
		return $this->Licitacao->getSituacoes ();
	}
	
	/**
	 * Busca as instituições disponíveis
	 * 
	 * @return array
	 */
	private function getInstituicoes() {
		return $this->Licitacao->getInstituicoes ();
	}
	
	/**
	 * Prepara os dados para serem apresentados na grid
	 * 
	 * @param
	 *        	$aLicitacoes
	 * @return mixed
	 */
	private function prepararDadosGrid($aLicitacoes) {
		$iTotalLicitacoes = count ( $aLicitacoes );
		$aDadosRetorno = array ();
		$aDadosRetorno ['total'] = $iTotalLicitacoes;
		$aDadosRetorno ['records'] = $iTotalLicitacoes;
		$aDadosRetorno ['page'] = 1;
		$aDadosRetorno ['rows'] = array ();
		if ($iTotalLicitacoes > 0) {
			
			foreach ( $aLicitacoes as $aResultadoLicitacao ) {
				
				foreach ( $aResultadoLicitacao as $aDadoLicitacao ) {
					
					$aDadoRetornoLicitacao = array ();
					$aDadoRetornoLicitacao ['id'] = $aDadoLicitacao ['id'];
					$aDadoRetornoLicitacao ['cell'] = array ();
					$aDadoRetornoLicitacao ['cell'] [] = $aDadoLicitacao ['tipocompra'];
					$aDadoRetornoLicitacao ['cell'] [] = $aDadoLicitacao ['numero'] . "/" . $aDadoLicitacao ['anousu'];
					$aDadoRetornoLicitacao ['cell'] [] = $aDadoLicitacao ['edital'];
					$aDadoRetornoLicitacao ['cell'] [] = $aDadoLicitacao ['objeto'];
					$aDadoRetornoLicitacao ['cell'] [] = $aDadoLicitacao ['situacao'];
					$aDadosRetorno ['rows'] [] = $aDadoRetornoLicitacao;
				}
			}
		}
		return $aDadosRetorno;
	}
	
	/**
	 * Retorna os dados de consulta da licitação
	 */
	public function consulta($iCodigoLicitacao) {
		$aLicitacao = $this->Licitacao->getLicitacaoPorCodigo ( $iCodigoLicitacao );
		
		$oStdDadoRetorno = new stdClass ();
		$oStdDadoRetorno->id = $aLicitacao ['id'];
		$oStdDadoRetorno->instituicao_id = $aLicitacao ['instituicao_id'];
		$oStdDadoRetorno->tipocompra = utf8_encode ( $aLicitacao ['tipocompra'] );
		$oStdDadoRetorno->numero = $aLicitacao ['numero'];
		$oStdDadoRetorno->datacriacao = $aLicitacao ['datacriacao'];
		$oStdDadoRetorno->horacriacao = $aLicitacao ['horacriacao'];
		$oStdDadoRetorno->dataabertura = $aLicitacao ['dataabertura'];
		$oStdDadoRetorno->datapublicacao = $aLicitacao ['datapublicacao'];
		$oStdDadoRetorno->horaabertura = $aLicitacao ['horaabertura'];
		$oStdDadoRetorno->local = utf8_encode ( $aLicitacao ['local'] );
		$oStdDadoRetorno->objeto = utf8_encode ( $aLicitacao ['objeto'] );
		$oStdDadoRetorno->processoadministrativo = $aLicitacao ['processoadministrativo'];
		$oStdDadoRetorno->situacao = $aLicitacao ['situacao'];
		$oStdDadoRetorno->edital = $aLicitacao ['edital'];
		$oStdDadoRetorno->anousu = $aLicitacao ['anousu'];
		
		$this->set ( "oLicitacao", $oStdDadoRetorno );
	}
	
	/**
	 * Busca os itens vinculados para a licitacao
	 * 
	 * @return string - json
	 */
	public function pesquisarItens() {
		$this->autoRender = false;
		$aItens = $this->Licitacao->getItens ( $_POST ['iCodigoLicitacao'] );
		$iTotalItens = count ( $aItens );
		$aItensRetorno = array ();
		$aItensRetorno ['total'] = $iTotalItens;
		$aItensRetorno ['records'] = $iTotalItens;
		$aItensRetorno ['page'] = 1;
		$aItensRetorno ['rows'] = array ();
		
		foreach ( $aItens as $aColecaoItem ) {
			
			foreach ( $aColecaoItem as $aDadoItem ) {
				
				$aDadoRetornoItem = array ();
				$aDadoRetornoItem ['id'] = $aDadoItem ['id'];
				$aDadoRetornoItem ['cell'] [] = $aDadoItem ['material'];
				$aDadoRetornoItem ['cell'] [] = $aDadoItem ['unidade_medida'];
				$aDadoRetornoItem ['cell'] [] = $aDadoItem ['quantidade'];
				$aDadoRetornoItem ['cell'] [] = $aDadoItem ['valor'];
				$aDadoRetornoItem ['cell'] [] = $aDadoItem ['resumo'];
				$aDadoRetornoItem ['cell'] [] = $aDadoItem ['fornecedor'];
				$aItensRetorno ['rows'] [] = $aDadoRetornoItem;
			}
		}
		return $this->json ( $aItensRetorno );
	}
	
	/**
	 * Retorna os documentos vinculados para a licitacao
	 * 
	 * @return string - json
	 */
	public function pesquisarDocumentos() {
		$this->autoRender = false;
		$aDocumentos = $this->Licitacao->getDocumentos ( $_POST ['iCodigoLicitacao'] );
		$iTotalDocumentos = count ( $aDocumentos );
		$aDocumentosRetorno = array ();
		$aDocumentosRetorno ['total'] = $iTotalDocumentos;
		$aDocumentosRetorno ['records'] = $iTotalDocumentos;
		$aDocumentosRetorno ['page'] = 1;
		$aDocumentosRetorno ['rows'] = array ();
		
		if ($iTotalDocumentos == 0) {
			return $this->json ( $aDocumentosRetorno );
		}
		
		foreach ( $aDocumentos as $aColecaoDocumentos ) {
			
			foreach ( $aColecaoDocumentos as $aDadoDocumento ) {
				
				switch ($aDadoDocumento ['tipo']) {
					
					case LicitacaoDocumento::TIPO_ATA :
						$sDescricaoDocumento = "Ata";
						break;
					case LicitacaoDocumento::TIPO_EDITAL :
						$sDescricaoDocumento = "Edital";
						break;
					case LicitacaoDocumento::TIPO_MINUTA :
						$sDescricaoDocumento = "Minuta";
						break;
					default :
						$sDescricaoDocumento = "Não Identificado";
				}
				$aDadoRetornoDocumento = array ();
				$aDadoRetornoDocumento ['id'] = $aDadoDocumento ['id'];
				$aDadoRetornoDocumento ['cell'] [] = $sDescricaoDocumento;
				$aDadoRetornoDocumento ['cell'] [] = $aDadoDocumento ['nome'];
				$aDocumentosRetorno ['rows'] [] = $aDadoRetornoDocumento;
			}
		}
		return $this->json ( $aDocumentosRetorno );
	}
	
	/**
	 * Executa o download do arquivo
	 * 
	 * @return mixed
	 */
	public function downloadArquivo() {
		$this->autoRender = false;
		$mArquivo = $this->Licitacao->downloadDocumento ( $_POST ['iCodigoDocumento'] );
		
		$sMensagem = "";
		if (! $mArquivo) {
			$sMensagem = "Não foi possível executar o download do arquivo.";
		}
		
		$aDadosRetorno = array (
				"mensagem" => $sMensagem,
				"arquivo" => $mArquivo 
		);
		return $this->json ( $aDadosRetorno );
	}
}