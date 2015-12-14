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
class Acordo extends AppModel {
	public $name = "Acordo";
	public $behaviors = "CakePtbr.AjusteData";
	public $hasMany = array (
			"AcordoAditamento" => array (
					"className" => "AcordoAditamento",
					"foreignKey" => "acordo_id" 
			),
			"AcordoDocumento" => array (
					"className" => "AcordoDocumento",
					"foreignKey" => "acordo_id" 
			),
			"AcordoEmpenho" => array (
					"className" => "AcordoEmpenho",
					"foreignKey" => "acordo_id" 
			) 
	);
	public $belongsTo = array (
			"Instituicao" => array (
					"className" => "Instituicao",
					"foreignKey" => "instituicao_id" 
			) 
	);
	
	/**
	 * Retorna os exercicios utilizados nos acordos
	 * 
	 * @param integer $iInstituicao        	
	 * @return array
	 */
	public function getExercicios($iInstituicao) {
		$aExercicios = $this->find ( 'all', array (
				'recursive' => - 1,
				'conditions' => array (
						'instituicao_id' => $iInstituicao 
				),
				'fields' => array (
						'anousu' 
				),
				'group' => "anousu",
				'order' => "anousu" 
		) );
		
		if (empty ( $aExercicios )) {
			return array ();
		}
		
		return Set::extract ( '/Acordo/anousu', $aExercicios );
	}
	
	/**
	 * Retorna os dados do acordo
	 * 
	 * @param integer $iId        	
	 * @return array
	 */
	public function getAcordoById($iId) {
		return $this->find ( 'first', array (
				'recursive' => - 1,
				'conditions' => array (
						'Acordo.id' => $iId 
				) 
		) );
	}
}

?>