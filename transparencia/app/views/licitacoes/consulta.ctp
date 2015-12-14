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

<style>
  .w150 {
    width: 150px;
  }
</style>

<div class="main">
  <h3>Consulta Licita&ccedil;&atilde;o</h3>

  <table border="0">
    <tr>
      <td class="w150">Edital:</td>
      <th><?php echo $oLicitacao->edital; ?></th>
      <td class="w150">Exerc&iacute;cio:</td>
      <th><?php echo $oLicitacao->anousu; ?></th>
    </tr>
    <tr>
      <td>Modalidade:</td>
      <th><?php echo $oLicitacao->tipocompra; ?></th>
      <td>Numera&ccedil;&atilde;o:</td>
      <th><?php echo $oLicitacao->numero; ?></th>
    </tr>
    <tr>
      <td>Data da Cria&ccedil;&atilde;o:</td>
      <th><?php echo $this->Formatacao->data($oLicitacao->datacriacao); ?></th>
      <td>Hora da Cria&ccedil;&atilde;o:</td>
      <th><?php echo $oLicitacao->horacriacao; ?></th>
    </tr>
    <tr>
      <td>Data de Abertura</td>
      <th><?php echo $this->Formatacao->data($oLicitacao->dataabertura); ?></th>
      <td>Hora de Abertura</td>
      <th><?php echo $oLicitacao->horaabertura; ?></th>
    </tr>

    <tr>
      <td>Data da Publica&ccedil;&atilde;o:</td>
      <th><?php echo $this->Formatacao->data($oLicitacao->datapublicacao); ?></th>
      <td>Situa&ccedil;&atilde;o:</td>
      <th><?php echo $oLicitacao->situacao; ?></th>
    </tr>
    <tr>
      <td>Local da Licita&ccedil;&atilde;o:</td>
      <th colspan="3"><?php echo $oLicitacao->local; ?></th>
    </tr>
    <tr>
      <td>Objeto:</td>
      <th colspan="3"><?php echo $oLicitacao->objeto; ?></th>
    </tr>
  </table>
  <hr />
  <table id="tableGridItens">
  </table>
  <br />
  <table id="tableGridDocumentos">
  </table>
  <br />
  <p>
    <input type="button" id="btnVoltar" value="Voltar" />
    <input type="button" id="btnNovaConsulta" value="Nova Consulta" />
  </p>
</div>

<script>

  $("#btnVoltar").click(function() {
    history.back(-1);
  });
  $("#btnNovaConsulta").click(
    function() {
      location.href = '<?php echo $this->base;?>/licitacoes/index';
    }
  );

  var oParametro = {"iCodigoLicitacao" : <?php echo $oLicitacao->id; ?>};

  $("#tableGridItens").jqGrid({

    url: '<?php echo $this->base;?>/licitacoes/pesquisarItens',
    datatype: "json",
    mtype: "POST",
    postData: oParametro,
    colNames:['Material', 'Medida', 'Quantidade', 'Valor', 'Resumo', 'Fornecedor'],
    colModel:[
      {name:'material'      ,index:'material'      ,width:'30', align:'left', sortable: false},
      {name:'unidademedida' ,index:'unidademedida' ,width:'15', align:'center', sortable: false},
      {name:'quantidade'    ,index:'quantidade'    ,width:'15', align:'right',  sortable: false},
      {name:'valor'         ,index:'valor'         ,width:'15', align:'right',  formatter:'currency', sortable: false},
      {name:'resumo'        ,index:'resumo'        ,width:'15', align:'left', sortable: false},
      {name:'fornecedor'    ,index:'fornecedor'    ,width:'35', align:'left', sortable: false}
    ],
    altRows:   true,
    autowidth: true,
    height:    '150px'
  });

  $("#tableGridDocumentos").jqGrid({

    url: '<?php echo $this->base;?>/licitacoes/pesquisarDocumentos',
    datatype: "json",
    mtype: "POST",
    postData: oParametro,
    colNames:['Tipo', 'Documento'],
    colModel:[
      {name:'tipo'      ,index:'material'      ,width:'15', align:'left', sortable: false},
      {name:'documento' ,index:'unidademedida' ,width:'40', align:'left', sortable: false}
    ],
    altRows:   true,
    autowidth: true,
    height:    '150px',
    onCellSelect: function (id) {

      $.ajax( {
        async:true,
        url: '<?php echo $this->base;?>/licitacoes/downloadArquivo',
        type:'post',
        dataType: 'json',
        data: {"iCodigoDocumento" : id},
        beforeSend:function(request) {
          $('#ajax-loader').show();
        },
        complete:  function(oRetorno) {

          var oRetorno = $.JSON.decode(oRetorno.responseText);
          window.open("<?php echo $this->base;?>/app/"+oRetorno.arquivo);
          $('#ajax-loader').hide();

        }

      } );
    },
    loadComplete: function (oResult) {
      if (oResult.total == 0) {
        $("#gview_tableGridDocumentos > .ui-jqgrid-bdiv").html('<h4 class="no-records">Nenhum Registro Encontrado.</h4>')
      }
    }
  });
</script>
