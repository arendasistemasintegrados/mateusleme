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
class Licitacao extends AppModel {
	public $useTable = 'licitacoes';
	public $hasMany = array (
			'LicitacaoItem' => array (
					'className' => 'LicitacaoItem',
					'foreignKey' => 'licitacao_id' 
			),
			'LicitacaoDocumento' => array (
					'className' => 'LicitacaoDocumento',
					'foreignKey' => 'licitacao_id' 
			) 
	);
	public $belongsTo = array (
			"Instituicao" => array (
					"className" => "Instituicao",
					"foreignKey" => "instituicao_id" 
			) 
	);
	
	/**
	 * Retorna os anos únicos das licitacoes
	 * 
	 * @return array
	 */
	public function getAnos() {
		$rsBuscaAnos = $this->find ( "all", array (
				"recursive" => - 1,
				"fields" => "anousu",
				"group" => "anousu",
				"order" => "anousu" 
		) );
		
		$aAnosRetorno = array ();
		foreach ( $rsBuscaAnos as $aDadoLicitacao ) {
			foreach ( $aDadoLicitacao as $aAno ) {
				$aAnosRetorno [$aAno ['anousu']] = $aAno ['anousu'];
			}
		}
		return $aAnosRetorno;
	}
	
	/**
	 * Retorna as modalidades disponíveis
	 * 
	 * @return array
	 */
	public function getModalidades() {
		$rsBuscaModalidades = $this->find ( "all", array (
				"recursive" => - 1,
				"fields" => "tipocompra",
				"group" => "tipocompra",
				"order" => "tipocompra" 
		) );
		
		$aModalidadeRetorno = array ();
		foreach ( $rsBuscaModalidades as $aDadoLicitacao ) {
			foreach ( $aDadoLicitacao as $aModalidade ) {
				$aModalidadeRetorno [utf8_encode ( $aModalidade ['tipocompra'] )] = utf8_encode ( $aModalidade ['tipocompra'] );
			}
		}
		return $aModalidadeRetorno;
	}
	
	/**
	 * Retorna as situacoes disponíveis para seleção
	 * 
	 * @return array
	 */
	public function getSituacoes() {
		$rsBuscaSituacao = $this->find ( "all", array (
				"recursive" => - 1,
				"fields" => "situacao",
				"group" => "situacao",
				"order" => "situacao" 
		) );
		
		$aSituacaoRetorno = array ();
		foreach ( $rsBuscaSituacao as $aDadoLicitacao ) {
			foreach ( $aDadoLicitacao as $aSituacao ) {
				$aSituacaoRetorno [utf8_encode ( $aSituacao ['situacao'] )] = utf8_encode ( $aSituacao ['situacao'] );
			}
		}
		return $aSituacaoRetorno;
	}
	
	/**
	 * Busca as instituicoes existentes para uma
	 * 
	 * @return array
	 */
	public function getInstituicoes() {
		$rsBuscaInstituicao = $this->find ( "all", array (
				'joins' => array (
						array (
								'table' => 'instituicoes',
								'type' => 'INNER',
								'alias' => 'Instituicao',
								'conditions' => array (
										'Instituicao.id = Licitacao.instituicao_id' 
								) 
						) 
				),
				"recursive" => - 1,
				"fields" => array (
						'"Instituicao"."id"',
						"Instituicao.descricao" 
				),
				"group" => array (
						"Instituicao.id" 
				),
				"order" => "Instituicao.id" 
		) );
		
		$aInstituicoesRetorno = array ();
		foreach ( $rsBuscaInstituicao as $aDadoInstituicoes ) {
			foreach ( $aDadoInstituicoes as $aInstituicao ) {
				$aInstituicoesRetorno [$aInstituicao ['id']] = utf8_encode ( $aInstituicao ['descricao'] );
			}
		}
		return $aInstituicoesRetorno;
	}
	
	/**
	 * Busca as licitações
	 * 
	 * @param
	 *        	$sWhere
	 * @return array
	 */
	public function getLicitacoes($sWhere) {
		$aLicitacoes = $this->find ( "all", array (
				"recursive" => - 1,
				"fields" => "*",
				"conditions" => $sWhere 
		) );
		return $aLicitacoes;
	}
	
	/**
	 * Retorna um objeto com os dados da licitacao
	 * 
	 * @param
	 *        	$iCodigoLicitacao
	 * @return array
	 */
	public function getLicitacaoPorCodigo($iCodigoLicitacao) {
		$aLicitacao = $this->find ( "first", array (
				"recursive" => - 1,
				"fields" => "*",
				"conditions" => "id = {$iCodigoLicitacao}" 
		) );
		return $aLicitacao ["Licitacao"];
	}
	
	/**
	 * Busca os itens de uma licitação
	 * 
	 * @param
	 *        	$iCodigoLicitacao
	 * @return array
	 */
	public function getItens($iCodigoLicitacao) {
		$aItens = $this->LicitacaoItem->find ( "all", array (
				"conditions" => "LicitacaoItem.licitacao_id = {$iCodigoLicitacao}" 
		) );
		return $aItens;
	}
	
	/**
	 * Retorna os documentos encontrados para a licitação
	 * 
	 * @param
	 *        	$iCodigoLicitacao
	 * @return mixed
	 */
	public function getDocumentos($iCodigoLicitacao) {
		$aDocumentos = $this->LicitacaoDocumento->find ( "all", array (
				"conditions" => "LicitacaoDocumento.licitacao_id = {$iCodigoLicitacao}" 
		) );
		return $aDocumentos;
	}
	
	/**
	 * Executa o download do documento da licitacao
	 * 
	 * @param
	 *        	$iCodigoDocumento
	 */
	public function downloadDocumento($iCodigoDocumento) {
		$sNomeArquivo = $this->LicitacaoDocumento->download ( $iCodigoDocumento );
		return $sNomeArquivo;
	}
}