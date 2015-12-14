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

//echo $this->Html->script('pesquisar');
?>


<div class="main">
  <h3>Resultado da Busca de Licitações</h3>
  <table id="tableGridLicitacao">
  </table>
  <p style="text-align: center; padding-top: 10px;">
    <input type="button" id="btnRetornar" value="Voltar"/>
  </p>
</div>


<script>

  $('#btnRetornar').click(
    function () {
      history.back(-1);
    }
  );

  var oParametro = {
    "instituicao": '<?php echo $_GET['instituicao']; ?>',
    "exercicio"  : '<?php echo $_GET['exercicio']; ?>',
    "mes"        : '<?php echo $_GET['mes']; ?>',
    "modalidade" : '<?php echo $_GET['modalidade']; ?>',
    "situacao"   : '<?php echo $_GET['situacao']; ?>'
  };


  $("#tableGridLicitacao").jqGrid({

    url: '<?php echo $this->base;?>/licitacoes/pesquisarLicitacoes',
    datatype: "json",
    mtype: "POST",
    postData: oParametro,
    colNames:['Modalidade', 'Numeração', 'Edital', 'Objeto', 'Situação'],
    colModel:[
      {name:'modalidade' ,index:'modalidade' ,width:'20', align:'left' , sortable: false},
      {name:'numeracao'  ,index:'numeracao'  ,width:'15', align:'center', sortable: false},
      {name:'edital'     ,index:'edital'     ,width:'15', align:'center', sortable: false},
      {name:'objeto'     ,index:'objeto'     ,width:'50', align:'left', sortable: false},
      {name:'situacao'   ,index:'situacao'   ,width:'15', align:'left', sortable: false}
    ],
    altRows:   true,
    autowidth: true,
    height:    '350px',
    onCellSelect: function (id) {
      location.href = '<?php echo $this->base;?>/licitacoes/consulta/'+id;
    },
    loadComplete: function (oResult) {
      if (oResult.total == 0) {
        $("#gview_tableGridLicitacao > .ui-jqgrid-bdiv").html('<h4 class="no-records">Nenhum Registro Encontrado.</h4>')
      }
    }
  });
</script>