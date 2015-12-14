<table>
  <tbody>
    <tr>
      <td>Ano / M&ecirc;s:</td>
      <th><?php 
          echo $aServidorMovimentacao['ServidorMovimentacao']['ano'] . ' / '
             . str_pad($aServidorMovimentacao['ServidorMovimentacao']['mes'], 2, '0', STR_PAD_LEFT);
        ?></th>

      <td colspan="4"></td>
    </tr>

    <tr>
      <td>Nome:</td>
      <th colspan="5"><?php echo utf8_encode($aServidorMovimentacao['Servidor']['nome']); ?></th>
    </tr>

    <tr>
      <td>CPF:</td>
      <th><?php 
        echo preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.***.***-**', $aServidorMovimentacao['Servidor']['cpf']); 
      ?></th>

      <td>Sal&aacute;rio Base:</td>
      <th><?php 
          echo number_format($aServidorMovimentacao['ServidorMovimentacao']['salario_base'], 2, ',', '.'); 
        ?></th>

      <td colspan="2"></td>
    </tr>

    <tr>
      <td>Cargo:</td>
      <th><?php echo utf8_encode($aServidorMovimentacao['ServidorMovimentacao']['cargo']); ?></th>

      <td>Admiss&atilde;o:</td>
      <th><?php 
          echo $aServidorMovimentacao['Servidor']['admissao'] ? 
            date("d/m/Y", strtotime( $aServidorMovimentacao['Servidor']['admissao'])) : ''; 
        ?></th>

      <td>Rescis&atilde;o:</td>
      <th><?php 
          echo $aServidorMovimentacao['Servidor']['rescisao'] ? 
            date("d/m/Y", strtotime( $aServidorMovimentacao['Servidor']['rescisao'])) : ''; 
        ?></th>
    </tr>

    <tr>
      <td>Lota&ccedil;&atilde;o:</td>
      <th><?php echo utf8_encode($aServidorMovimentacao['ServidorMovimentacao']['lotacao']); ?></th>

      <td>V&iacute;nculo:</td>
      <th><?php echo utf8_encode($aServidorMovimentacao['ServidorMovimentacao']['vinculo']); ?></th>
      <td colspan="2"></td>
    </tr>
  </tbody>
</table>