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
class LicitacaoDocumento extends AppModel {
	public $useTable = "licitacoes_documentos";
	const TIPO_EDITAL = 1;
	const TIPO_ATA = 2;
	const TIPO_MINUTA = 3;
	const PATH_ARQUIVO = 'files/';
	
	/**
	 * Configura o arquivo para download
	 * 
	 * @param integer $iCodigoDocumento        	
	 * @return array
	 */
	public function download($iCodigoDocumento) {
		$aDocumento = $this->find ( "first", array (
				"fields" => array (
						"documento",
						"nome" 
				),
				"conditions" => "id = {$iCodigoDocumento}" 
		) );
		
		$iOidDocumento = $aDocumento ["LicitacaoDocumento"] ['documento'];
		$sNomeDocumento = $aDocumento ["LicitacaoDocumento"] ['nome'];
		$sNomeDocumento = str_replace ( " ", "", $sNomeDocumento );
		$sNomeDocumento = str_replace ( "º", "", $sNomeDocumento );
		$sNomeDocumento = str_replace ( "ª", "", $sNomeDocumento );
		$oDataSource = $resDataSource = $this->getDataSource ();
		$oDataSource->begin ();
		$lExportArquivo = pg_lo_export ( $oDataSource->connection, $iOidDocumento, self::PATH_ARQUIVO . $sNomeDocumento );
		$oDataSource->commit ();
		if (! $lExportArquivo) {
			return false;
		}
		return self::PATH_ARQUIVO . $sNomeDocumento;
	}
}