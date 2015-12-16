<?php
/**
 * E-cidade Software Publico para Gest�o Municipal
 *   Copyright (C) 2014 DBSeller Servi�os de Inform�tica Ltda
 *                          www.dbseller.com.br
 *                          e-cidade@dbseller.com.br
 *   Este programa � software livre; voc� pode redistribu�-lo e/ou
 *   modific�-lo sob os termos da Licen�a P�blica Geral GNU, conforme
 *   publicada pela Free Software Foundation; tanto a vers�o 2 da
 *   Licen�a como (a seu crit�rio) qualquer vers�o mais nova.
 *   Este programa e distribu�do na expectativa de ser �til, mas SEM
 *   QUALQUER GARANTIA; sem mesmo a garantia impl�cita de
 *   COMERCIALIZA��O ou de ADEQUA��O A QUALQUER PROP�SITO EM
 *   PARTICULAR. Consulte a Licen�a P�blica Geral GNU para obter mais
 *   detalhes.
 *   Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral GNU
 *   junto com este programa; se n�o, escreva para a Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *   02111-1307, USA.
 *   C�pia da licen�a no diret�rio licenca/licenca_en.txt
 *                                 licenca/licenca_pt.txt
 */

/**
 *
 * Classe respons�vel pela pagina��o de arrays
 *
 * @package DBSeller/Controller
 * @implements Zend_Paginator_Adapter_Interface
 */
class DBSeller_Controller_PaginatorArray implements Zend_Paginator_Adapter_Interface {

  private $aItens = array();

  /**
   * Constroi o objeto da classe e adiciona a quantidade total dos itens
   *
   * @param $aItens
   * @return \DBSeller_Controller_PaginatorArray
   */
  public function __construct($aItens) {

    $this->aItens = $aItens;
  }

  /**
   * Retorna os itens pagionados de acordo com o offset informado
   * @param int $offset quantidade de itens iniciais do array
   * @param int $itemCountPerPage quantidade de itens por p�gina
   * @return array
   */
  public function getItems($offset, $itemCountPerPage) {

     $aItens          = $this->aItens;
     $aItensPaginados = array_slice($aItens, $offset, $itemCountPerPage);
     return $aItensPaginados;
  }

  /**
   * Retorna o Total de itens no paginator
   * @return int
   */
  public function count() {
    return count($this->aItens);
  }
}