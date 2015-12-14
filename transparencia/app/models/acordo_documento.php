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
class AcordoDocumento extends AppModel {
	public $name = "AcordoDocumento";
	public $belongsTo = array (
			"Acordo" => array (
					"className" => "Acordo",
					"foreignKey" => "acordo_id" 
			) 
	);
	
	/**
	 * Busca os documentos do acordo
	 * 
	 * @param integer $iIdAcordo        	
	 * @return array
	 */
	public function getDocumentosByAcordo($iIdAcordo) {
		return $this->find ( 'all', array (
				'recursive' => - 1,
				'conditions' => array (
						'AcordoDocumento.acordo_id' => $iIdAcordo 
				) 
		) );
	}
	
	/**
	 * Busca os dados do documento
	 * 
	 * @param integer $iIdDocumento        	
	 * @return array
	 */
	public function getDocumentoById($iIdDocumento) {
		return $this->find ( 'first', array (
				'recursive' => - 1,
				'conditions' => array (
						'AcordoDocumento.id' => $iIdDocumento 
				) 
		) );
	}
	
	/**
	 * Exporta o arquivo para o tmp
	 * 
	 * @param integer $iOid
	 *        	OID do arquivo no banco
	 * @return mixed - Nome do arquivo ou false em caso de erro
	 */
	public function baixarDocumento($iOid) {
		$oDataSource = $this->getDataSource ();
		
		$oDataSource->begin ( $this );
		$lArquivo = pg_lo_export ( $oDataSource->connection, $iOid, DS . "tmp" . DS . $iOid );
		$oDataSource->commit ( $this );
		
		if (! $lArquivo) {
			return false;
		}
		
		return array (
				'path' => DS . 'tmp' . DS,
				'file' => $iOid 
		);
	}
}
?>