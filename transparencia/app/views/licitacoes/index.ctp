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

echo $this->Html->script('licitacoes');
?>

<style>

</style>

<div id="breadcrumb">
  <?php

  $this->Crumb->addPage(0, 'Principal' , 'main');
  $this->Crumb->addPage(1, 'Licitações', 'licitacoes/index');
  echo $this->Crumb->getHtml();

  ?>
</div>
<div class="main">
  <h3>Consulta de Licitações</h3>
  <?php


  echo $this->Form->create('Licitacoes', array(
                             'url' => array(
                               'plugin' => false,
                               'controller' => 'licitacoes',
                               'action' => 'pesquisar'
                             ),
                             'type' => 'GET'
                           ));


  echo $this->Form->input(
    'instituicao',
    array(
      'options' => $aInstituicao,
      'empty' => 'SELECIONE...',
      'label' => 'Instituição',
      'class' => 'x6'
    )
  );

  echo $this->Form->input('exercicio', array("options" => $aExercicio, "label"=>"Exercício", "class"=>"x6"));

  $aMeses = array(
    1 => "Janeiro",
    2 => "Fevereiro",
    3 => "Março",
    4 => "Abril",
    5 => "Maio",
    6 => "Junho",
    7 => "Julho",
    8 => "Agosto",
    9 => "Setembro",
    10 => "Outubro",
    11 => "Novembro",
    12 => "Dezembro"
  );
  $aOpcoesMes = array("options" => $aMeses, 'empty' => "SELECIONE...", "label"=>"Mês", "class"=>"x6");
  echo $this->Form->input('mes', $aOpcoesMes);
  echo $this->Form->input('modalidade', array("options" => $aModalidade, 'empty' => "SELECIONE...", "class"=>"x6"));
  $aOpcoesSituacao = array("options" => $aSituacao, "empty" => "SELECIONE...", "label"=>"Situações", "class"=>"x6");
  echo $this->Form->input("situacao", $aOpcoesSituacao);

  echo $this->Html->tag('div', $this->Form->button('Pesquisar', array('type' => 'submit')), array('class' => 'buttons'));
  $this->Form->end();
  ?>
</div>