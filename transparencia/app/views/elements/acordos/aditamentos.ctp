<?php if (empty($aAditamentos)): ?>
  <span>Nenhum Aditamento encontrado.</span>
<?php else: ?>
  <div class="table-container fixed-header">

    <table class="header">
      <thead>
        <tr>
          <th>Tipo</th>
          <th>Data</th>
          <th>Emergencial</th>
        </tr>
      </thead>
    </table>


    <div class="body-container">

      <table class="body">
        <tbody>

          <?php foreach ($aAditamentos as $aAditamento): ?>
            <tr>
              <td align="center"><?php echo utf8_encode($aAditamento['AcordoAditamento']['posicao_tipo']); ?></td>
              <td align="center"><?php echo $this->Formatacao->data($aAditamento['AcordoAditamento']['data']); ?></td>
              <td align="center"><?php echo ($aAditamento['AcordoAditamento']['emergencial'] ? 'SIM' : 'NÃO'); ?></td>
            </tr>
          <?php endforeach; ?>

        </tbody>
      </table>

    </div>

  </div>
<?php endif; ?>