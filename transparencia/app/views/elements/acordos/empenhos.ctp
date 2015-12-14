<?php if (empty($aEmpenhos)): ?>
  <span>Nenhum Empenho encontrado.</span>
<?php else: ?>
  <div class="table-container fixed-header">

    <table class="header">
      <thead>
        <tr>
          <th>Empenho</th>
        </tr>
      </thead>
    </table>


    <div class="body-container">

      <table class="body">
        <tbody>

          <?php foreach ($aEmpenhos as $aEmpenho): ?>
            <tr>
              <td align="center"><?php 
                  $sEmpenho    = $aEmpenho['Empenho']['codigo'] . '/' .$aEmpenho['Empenho']['exercicio'];

                  echo $this->Form->create('Empenho' . $aEmpenho['Empenho']['codigo'], array(
                    'url' => array(
                      'plugin' => false,
                      'controller' => 'despesas',
                      'action' => 'loadLink',
                      3
                    ),
                    'type' => 'POST',
                    'target' => '_blank'
                  )); 

                  echo $this->Form->input( 'iExercicio', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'iExercicio', 
                                                  'value' => $aEmpenho['Empenho']['exercicio']) );

                  echo $this->Form->input( 'sViewAtual', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'sViewAtual', 
                                                  'value' => 'empenhos') );

                  echo $this->Form->input( 'iNivel', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'iNivel', 
                                                  'value' => '0') );

                  echo $this->Form->input( 'iIdLink', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'iIdLink', 
                                                  'value' => '3') );

                  echo $this->Form->input( 'iCredor', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'iCredor', 
                                                  'value' => $aEmpenho['Pessoa']['codpessoa']) );

                  echo $this->Form->input( 'iInstituicao', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'iInstituicao', 
                                                  'value' => $aEmpenho['Empenho']['instituicao_id']) );

                  echo $this->Form->input( 'iEmpenho', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'iEmpenho', 
                                                  'value' => $aEmpenho['Empenho']['id']) );

                  echo $this->Form->input( 'dtDataImportacao', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'dtDataImportacao', 
                                                  'value' => $sDataImportacao) );

                  echo $this->Form->input( 'aHistorico.descricao', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'aHistorico[0][descricao]', 
                                                  'value' => $sEmpenho) );

                  echo $this->Form->input( 'aHistorico.descricao', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'aHistorico[0][descricao]', 
                                                  'value' => 'Descrição') );

                  echo $this->Form->input( 'aHistorico.valor_pago', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'aHistorico[0][valor_pago]', 
                                                  'value' => 'Valor Pago') );

                  echo $this->Form->input( 'aHistorico.valor_liquidado', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'aHistorico[0][valor_liquidado]', 
                                                  'value' => 'Valor Liquidado') );

                  echo $this->Form->input( 'aHistorico.valor_anulado', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'aHistorico[0][valor_anulado]', 
                                                  'value' => 'Valor Anulado') );

                  echo $this->Form->input( 'aHistorico.valor_empenhado', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'aHistorico[0][valor_empenhado]', 
                                                  'value' => 'Valor Empenhado') );

                  echo $this->Form->input( 'aHistorico.descricao', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'aHistorico[1][descricao]', 
                                                  'value' => "Empenho: {$sEmpenho}") );

                  echo $this->Form->input( 'aHistorico.valor_pago', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'aHistorico[1][valor_pago]', 
                                                  'value' => $aEmpenho['Empenho']['valor_pago']) );

                  echo $this->Form->input( 'aHistorico.valor_liquidado', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'aHistorico[1][valor_liquidado]', 
                                                  'value' => $aEmpenho['Empenho']['valor_liquidado']) );

                  echo $this->Form->input( 'aHistorico.valor_anulado', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'aHistorico[1][valor_anulado]', 
                                                  'value' => $aEmpenho['Empenho']['valor_anulado']) );

                  echo $this->Form->input( 'aHistorico.valor_empenhado', 
                                           array( 'type' => 'hidden', 
                                                  'name' => 'aHistorico[1][valor_empenhado]', 
                                                  'value' => $aEmpenho['Empenho']['valor_empenhado']) );

                  echo $this->Form->button( $sEmpenho, array('type' => 'submit', 'class' => 'link-style class-button') );
                  
                  echo $this->Form->end();
                ?></td>
            </tr>
          <?php endforeach; ?>

        </tbody>
      </table>

    </div>

  </div>
<?php endif; ?>