<div class="table-container fixed-header">
  <h2><?php echo mb_strtoupper($sTipoFolha); ?></h2>

  <table class="header">
    <thead>
      <tr>
        <th>Rubrica</th>       
        <th>Valor</th>        
      </tr>
    </thead>
  </table>

  <div class="body-container">

    <table class="body">
      <tbody>
        <?php 
          $lPutLine = true; 
          foreach ($aFolhaPagamento as $rubrica) : 
            if (in_array($rubrica['FolhaPagamento']['rubrica'], array('Z777', 'Z888', 'Z999')) && $lPutLine): 
        ?>
              <tr>
                <td colspan="4">&nbsp;</td>
              </tr>
        <?php 
              $lPutLine = false;
            endif; 

            /**
             * Não exibe as rubricas de desconto e a margem consignavel nem as bases
             */
            if ( (in_array(utf8_encode($rubrica['FolhaPagamento']['tiporubrica']), array( "desconto", "base", "provento" ))
                    && !in_array($rubrica['FolhaPagamento']['rubrica'], array('Z999'))
                  )
                 || $rubrica['FolhaPagamento']['rubrica'] == 'R803' ) {

              continue;
            }

        ?>
        <tr>
          <td>
            <?php if (!$lPutLine): ?>
              <strong>
            <?php endif; ?>
            <?php echo utf8_encode($rubrica['FolhaPagamento']['descr_rubrica']); ?>
            <?php if (!$lPutLine): ?>
              </strong>
            <?php endif; ?>
          </td>
          <td align="right">
            <?php if (!$lPutLine): ?>
              <strong>
            <?php endif; ?>
            <?php echo number_format($rubrica['FolhaPagamento']['valor'], 2, ',', '.'); ?>
            <?php if (!$lPutLine): ?>
              </strong>
            <?php endif; ?>
          </td>          
        </tr>

      <?php endforeach; ?>

      </tbody>

    </table>
  </div>
</div>
