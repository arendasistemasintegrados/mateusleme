<?php if (empty($aDocumentos)): ?>
  <span>Nenhum Documento encontrado.</span>
<?php else: ?>
  <div class="table-container fixed-header">

    <table class="header">
      <thead>
        <tr>
          <th>Nome</th>
          <th>Descrição</th>
          <th>&nbsp;</th>
        </tr>
      </thead>
    </table>


    <div class="body-container">

      <table class="body">
        <tbody>

          <?php foreach ($aDocumentos as $aDocumento): ?>
            <tr>
              <td><?php echo utf8_encode($aDocumento['AcordoDocumento']['nome']); ?></td>
              <td><?php echo utf8_encode($aDocumento['AcordoDocumento']['descricao']); ?></td>
              <td align="center"><?php
                  echo $this->Html->link( 'Baixar', 
                                           array( 'controller' => 'acordos', 
                                                  'action' => 'baixarDocumento', $aDocumento['AcordoDocumento']['id'] ),
                                           array('class' => 'class-button'))
                ?></td>
            </tr>
          <?php endforeach; ?>

        </tbody>
      </table>

    </div>

  </div>
<?php endif; ?>