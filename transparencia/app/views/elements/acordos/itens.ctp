<?php if (empty($aItens)): ?>
  <span>Nenhum Item encontrado.</span>
<?php else: ?>
  <div class="table-container fixed-header">

    <table class="header">
      <thead>
        <tr>
          <th>Ordem</th>
          <th>Descrição</th>
          <th>Quantidade</th>
          <th>Unidade</th>
          <th>Valor Unitário</th>
          <th>Valor Total</th>
        </tr>
      </thead>
    </table>


    <div class="body-container">

      <table class="body">
        <tbody>

          <?php foreach ($aItens as $aItem): ?>
            <tr>
              <td align="center"><?php echo $aItem['AcordoAditamentoItem']['ordem']; ?></td>
              <td><?php echo utf8_encode($aItem['AcordoAditamentoItem']['material']); ?></td>
              <td align="center"><?php echo $aItem['AcordoAditamentoItem']['quantidade']; ?></td>
              <td align="center"><?php echo utf8_encode($aItem['AcordoAditamentoItem']['unidade']); ?></td>
              <td align="right"><?php echo $this->Formatacao->precisao($aItem['AcordoAditamentoItem']['valor_unitario'], 2); ?></td>
              <td align="right"><?php echo $this->Formatacao->precisao($aItem['AcordoAditamentoItem']['valor_total'], 2); ?></td>
            </tr>
          <?php endforeach; ?>

        </tbody>
      </table>

    </div>
  </div>
<?php endif; ?>