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
class AcordoAditamento extends AppModel {
	public $name = "AcordoAditamento";
	public $belongsTo = array (
			"Acordo" => array (
					"className" => "Acordo",
					"foreignKey" => "acordo_id" 
			) 
	);
	public $hasMany = array (
			"AcordoAditamentoItem" => array (
					"className" => "AcordoAditamentoItem",
					"foreignKey" => "acordo_aditamento_id" 
			) 
	);
	
	/**
	 * Retorna todos os aditamentos que são diferentes do tipo Inclusão
	 * 
	 * @param integer $iIdAcordo        	
	 * @return array
	 */
	public function getAditamentosByAcordo($iIdAcordo) {
		return $this->find ( 'all', array (
				'recursive' => - 1,
				'conditions' => array (
						'AcordoAditamento.acordo_id' => $iIdAcordo,
						"AcordoAditamento.posicao_tipo <> 'Inclusão'" 
				),
				'order' => 'numero' 
		) );
	}
	
	/**
	 * Retorna o ultimo aditamento do acordo
	 * 
	 * @param integer $iIdAcordo        	
	 * @return integer
	 */
	public function getUltimoIdAditamentoByAcordo($iIdAcordo) {
		$aAditamento = $this->find ( 'first', array (
				'recursive' => - 1,
				'fields' => array (
						'AcordoAditamento.id' 
				),
				'conditions' => array (
						'AcordoAditamento.acordo_id' => $iIdAcordo,
						"AcordoAditamento.numero = (select max(numero) from acordo_aditamentos where acordo_id = {$iIdAcordo})" 
				) 
		) );
		
		if (! empty ( $aAditamento )) {
			return $aAditamento [$this->alias] ['id'];
		}
		
		return null;
	}
}
?>