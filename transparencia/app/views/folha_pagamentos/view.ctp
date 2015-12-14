<?php echo $this->Html->css('jquery.tabs'); ?>
<?php echo $this->Html->css('jquery.table'); ?>
<?php echo $this->Html->script('jquery.table'); ?>
<?php echo $this->Html->script('jquery.tabs'); ?>
<?php echo $this->Html->script('folha_pagamento_view'); ?>

<div class="main">

<h3>Dados do Servidor</h3>
<?php 
  echo $this->element('folha_pagamentos/dados_servidor', array('aServidorMovimentacao' => $aServidorMovimentacao)); 
?>

<div class="tabs">

  <ul id="tabs" data-tab="true">
    <li class="active"><a href="#dados_financeiros">Dados Financeiros</a></li>
  </ul>

  <div id="dados_financeiros" tab-pane="true">
    <?php echo $this->element('folha_pagamentos/dados_financeiros', array('aFolhaPagamento' => $aServidorMovimentacao['FolhaPagamento'])); ?>
  </div>

</div>

<?php echo $this->Html->tag('div', $this->Html->link('Voltar', "javascript: history.go(-1) ;"), array('class' => 'buttons')); ?>
</div>