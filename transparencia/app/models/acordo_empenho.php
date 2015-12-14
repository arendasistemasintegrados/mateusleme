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
class AcordoEmpenho extends AppModel {
	public $name = "AcordoEmpenho";
	public $belongsTo = array (
			"Acordo" => array (
					"className" => "Acordo",
					"foreignKey" => "acordo_id" 
			),
			"Empenho" => array (
					"className" => "Empenho",
					"foreignKey" => "empenho_id" 
			) 
	);
	
	/**
	 * Retorna os empenhos do acordo
	 * 
	 * @param integer $iIdAcordo        	
	 * @return array
	 */
	public function getEmpenhosByAcordo($iIdAcordo) {
		return $this->find ( 'all', array (
				'recursive' => - 1,
				'fields' => array (
						"Empenho.*",
						"Pessoa.codpessoa",
						'(select sum(valor) from empenhos_movimentacoes ep                                                          ' . '                 inner join empenhos_movimentacoes_tipos emt on ep.empenho_movimentacao_tipo_id = emt.id ' . 'where emt.codgrupo = 10 and "Empenho"."id" = ep.empenho_id ) as "Empenho__valor_empenhado"' 
				),
				'conditions' => array (
						'AcordoEmpenho.acordo_id' => $iIdAcordo 
				),
				'joins' => array (
						array (
								'table' => 'empenhos',
								'type' => 'inner',
								'alias' => 'Empenho',
								'conditions' => array (
										"Empenho.id = AcordoEmpenho.empenho_id" 
								) 
						),
						array (
								'table' => 'pessoas',
								'type' => 'inner',
								'alias' => 'Pessoa',
								'conditions' => array (
										"Empenho.pessoa_id = Pessoa.id" 
								) 
						) 
				) 
		) );
	}
}
?>