<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2012  DBselller Servicos de Informatica             
 *                            www.dbseller.com.br                     
 *                         e-cidade@dbseller.com.br                   
 *                                                                    
 *  Este programa e software livre; voce pode redistribui-lo e/ou     
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme  
 *  publicada pela Free Software Foundation; tanto a versao 2 da      
 *  Licenca como (a seu criterio) qualquer versao mais nova.          
 *                                                                    
 *  Este programa e distribuido na expectativa de ser util, mas SEM   
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de              
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM           
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais  
 *  detalhes.                                                         
 *                                                                    
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU     
 *  junto com este programa; se nao, escreva para a Free Software     
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA          
 *  02111-1307, USA.                                                  
 *  
 *  Copia da licenca no diretorio licenca/licenca_en.txt 
 *                                licenca/licenca_pt.txt 
 */

require_once("libs/db_stdlib.php");
require_once("libs/db_sql.php");
require_once("libs/db_utils.php");
require_once("classes/db_proctransferproc_classe.php");
$oGet = db_utils::postMemory($_GET);
$clProcTransferProc = new cl_proctransferproc();
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" type="text/css" href="include/estilodai.css" >
	<script language="JavaScript" src="scripts/db_script.js"></script>
	<script language="JavaScript" src="scripts/prototype.js"></script>
	<style type="text/css">
    <?php
      db_estilosite();
  	?>
  	
	</style>
</head>
<body bgcolor="<?=$w01_corbody?>" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table align="center" valign="top">
  <tr> 
    <td> 
      <?php
      
        $sCampos  = "p61_dtandam,";
        $sCampos .= "p61_hora,   ";
        $sCampos .= "descrdepto, ";
        $sCampos .= "login,      ";
        $sCampos .= "p61_despacho";
        $sSql     = $clProcTransferProc->sql_query_andam(null,$oGet->iCodProcesso,$sCampos,"p62_codtran,p61_dtandam,p61_hora");
        
        db_lovrot($sSql,15);

      ?>
     </td>
   </tr>
</table>
</body>
</html>