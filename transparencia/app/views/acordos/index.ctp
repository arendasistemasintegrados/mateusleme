<?php echo $this->Html->script('acordos/index'); ?>

<div id="breadcrumb">
  <?php 
    
    $this->Crumb->addPage(0,'Principal'     ,'main');
    $this->Crumb->addPage(1,'Contratos','acordos/index');
  
    echo $this->Crumb->getHtml();    
  ?>
</div>

<br/>

<div class="main">
  <h3>Consulta de Contratos</h3>

  <?php 
    echo $this->Form->create('Filtro', array(
      'url' => array(
        'plugin' => false,
        'controller' => 'acordos',
        'action' => 'pesquisar'
      ),
      'type' => 'GET'
    )); 

    echo $this->Form->input('instituicao', array(
        'options' => $aInstituicoes, 
        'empty' => 'Selecione...', 
        'label' => 'Instituição', 
        'class' => 'required x6'
      ));

    echo $this->Form->input('exercicio', array(
        'options' => $aExercicios,
        'label' => 'Exercício',
        'empty' => 'Selecione...', 
        'default' => date('Y'), 
        'class' => 'required'
      ));

    echo $this->Form->input('mes', array(
        'options' => $aMeses,
        'label' => 'Mês',
        'empty' => 'Todos'
      ));

    echo $this->Html->tag( 'div', 
                           $this->Form->button('Pesquisar', array('type' => 'submit')), 
                           array('class' => 'buttons') );

    echo $this->Form->end(); 

  ?>
</div>