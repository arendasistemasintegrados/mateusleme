<?php echo $this->Html->script('acordos/pesquisar'); ?>

<div class="main">
  
  <h3>Resultados da busca de Contratos</h3>
  
  <?php 
    echo $this->Form->input('parametro', array('value' => $this->Javascript->object($aParametros), 'type' => 'hidden')); 
  ?>
  
  <table id="acordos"></table>
  <div id="pager"></div> 
  
  <?php 

    $aNewParametros = array();

    foreach ($aParametros as $key => $value) {
      $aNewParametros[] = $key .'='.$value;
    }

    echo $this->Html->tag('div', $this->Html->link('Voltar', 'index?'.implode('&',$aNewParametros), array()), array('class' => 'buttons')); 

  ?>

</div>