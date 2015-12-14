<?

// Salva o conteudo da sessao no banco de dados
function db_savesession($_conn, $_session) {
  
  // Cria tabela temporaria para a conexao corrente
  $sql  = "SELECT fc_startsession();";
  
  $result = pg_query($_conn, $sql) or die("Não foi possível criar sessão no banco de dados (Sql: $sql)!");
  
  if (pg_num_rows($result)==0) {
    return false;
  }

  // Insere as variaveis da sessao na tabela
  $sql   = "";

  foreach($_session as $key=>$val) {
   
    $key = strtoupper($key);

    // Intercepta "DB_DATAUSU" para ajustes
    if ($key == "DB_DATAUSU") {
      $time        = microtime(true);
      $micro_time  = sprintf("%06d",($time - floor($time)) * 1000000);
      $time_now    = date("H:i:s");

      $datahora = date("Y-m-d {$time_now}.{$micro_time}O", $val);
      
      // Cria timestamp "DB_DATAHORAUSU"
      $sql .= "SELECT fc_putsession('DB_DATAHORAUSU', '$datahora'); ";

      $val = date("Y-m-d", $val);
    }

    if (substr($key,0,2) == "DB"){ 
      $val = addslashes($val);

      $sql .= "SELECT fc_putsession('$key', '$val'); ";
    }
  } 
  
  pg_query($_conn, $sql) or die("Não foi possível criar sessão no banco de dados (Sql: $sql)!");
  
  return true;
} 


?>
