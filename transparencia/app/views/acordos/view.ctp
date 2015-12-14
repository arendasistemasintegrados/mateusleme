<?php echo $this->Html->css('jquery.tabs'); ?>
<?php echo $this->Html->css('jquery.table'); ?>
<?php echo $this->Html->script('jquery.table'); ?>
<?php echo $this->Html->script('jquery.tabs'); ?>

<div class="main">
  <h3>Dados do Contrato</h3>

  <table>
    <tbody>
      <tr>
        <td>Número:</td>
        <th><?php 
            echo $aAcordo['Acordo']['numero'] . ' - ' . $aAcordo['Acordo']['anousu'];
          ?></th>

        <td>Grupo:</td>
        <th><?php echo utf8_encode($aAcordo['Acordo']['grupo']); ?></th>
      </tr>
      <tr>
        <td>Situação:</td>
        <th><?php echo utf8_encode($aAcordo['Acordo']['situacao']); ?></th>

        <td>Data da Assinatura:</td>
        <th><?php echo $this->Formatacao->data($aAcordo['Acordo']['data_assinatura']); ?></th>
      </tr>
      <tr>
        <td>Lei:</td>
        <th><?php echo utf8_encode($aAcordo['Acordo']['lei']); ?></th>

        <td>Período de Vigência:</td>
        <th><?php 
            echo $this->Formatacao->data($aAcordo['Acordo']['data_inicio'])
                 . ' até '
                 . $this->Formatacao->data($aAcordo['Acordo']['data_fim']);
          ?></th>
      </tr>
      <tr>
        <td>Valor Total:</td>
        <th colspan="3"><?php echo $this->Formatacao->precisao($aAcordo['Acordo']['valor_total'], 2); ?></th>
      </tr>
      <tr>
        <td>Processo:</td>
        <th colspan="3"><?php echo utf8_encode($aAcordo['Acordo']['numero_processo']); ?></th>
      </tr>
      <tr>
        <td>Contratado:</td>
        <th colspan="3"><?php echo utf8_encode($aAcordo['Acordo']['contratado']); ?></th>
      </tr>
      <tr>
        <td>Objeto:</td>
        <th colspan="3"><?php echo utf8_encode($aAcordo['Acordo']['objeto']); ?></th>
      </tr>

    </tbody>
  </table>

  <div class="tabs">

    <ul id="tabs" data-tab="true">
      <li class="active"><a href="#itens">Itens</a></li> 
      <li><a href="#aditamentos">Aditamentos</a></li>
      <li><a href="#empenhos">Empenhos</a></li> 
      <li><a href="#documentos">Documentos</a></li> 
    </ul>

    <div id="itens" tab-pane="true">
      <?php echo $this->element('acordos/itens', array('aItens' => $aItens)); ?>
    </div>
    <div id="aditamentos" tab-pane="true">
      <?php echo $this->element('acordos/aditamentos', array('aAditamentos' => $aAditamentos)); ?>
    </div>
    <div id="empenhos" tab-pane="true">
      <?php echo $this->element( 'acordos/empenhos', 
                                 array( 'aEmpenhos' => $aEmpenhos, 
                                        'sDataImportacao' => $sDataImportacao ) ); ?>
    </div>
    <div id="documentos" tab-pane="true">
      <?php echo $this->element('acordos/documentos', array('aDocumentos' => $aDocumentos)); ?>
    </div>

  </div>

  <?php echo $this->Html->tag( 'div', 
                               $this->Html->link('Voltar', "javascript: history.go(-1) ;"), 
                               array('class' => 'buttons') ); ?>
</div>