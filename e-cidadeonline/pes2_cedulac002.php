<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBSeller Servicos de Informatica
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

require_once("fpdf151/scpdf.php");
require_once("fpdf151/impcarne.php");
require_once("libs/db_utils.php");
require_once("classes/db_cfpess_classe.php");

$oDaoCfpess = new cl_cfpess;

/**
 * Tipo de relatório comprovante de rendimento
 * Retorna false caso der erro na consulta
 */
$iTipoRelatorio = $oDaoCfpess->buscaCodigoRelatorio('comprovanterendimentos', db_anofolha(), db_mesfolha());
if(!$iTipoRelatorio) {
  db_redireciona('db_erros.php?fechar=true&db_erro=Modelo de impressão invalido, verifique parametros.');
}

validaUsuarioLogado();

$oPost  = db_utils::postMemory($_POST);

$matric          = $oPost->iMatric;
$anobase         = $oPost->anobase;
$instituicao     = $oPost->iInstit;
$anofolha        = db_anofolha();
$mesfolha        = db_mesfolha();
$tipo            = 'm';
$resp            = '';
$ordem           = 'a';

  $sSqlDbConfig  = " select ender,                                              ";
  $sSqlDbConfig .= "        cgc,                                                ";
  $sSqlDbConfig .= "        nomeinst,                                           ";
  $sSqlDbConfig .= "        munic,                                              ";
  $sSqlDbConfig .= "        db21_codcli as codcli,                              ";
  $sSqlDbConfig .= "        telef                                               ";
  $sSqlDbConfig .= "   from db_config where codigo = $instituicao               ";

$rsSqlDbConfig    = db_query($sSqlDbConfig);
$iNumRowsDbConfig = pg_num_rows($rsSqlDbConfig);
if ($iNumRowsDbConfig > 0) {

	$oDbConfig  = db_utils::fieldsMemory($rsSqlDbConfig, 0);
	$prefeitura = db_translate($oDbConfig->nomeinst);
	$enderpref  = db_translate($oDbConfig->ender);
	$municpref  = db_translate($oDbConfig->munic);
	$telefpref  = $oDbConfig->telef;
	$cgcpref    = $oDbConfig->cgc;
}

$sWhere     = " where rhdirfgeracao.rh95_ano = {$anobase} ";
switch ($tipo) {

  /**
   * Filtro Matricula
   */
  case 'm':

      if (isset($matric) && !empty($matric)) {

        $sMatriculas = implode("', '", explode(",", $matric));
        $sWhere     .= " and rh99_regist in ('{$sMatriculas}') ";
    }
    break;
}

$sSqlRendimento  = " select rh96_numcgm,                                                                                                                                                    \n";
$sSqlRendimento .= "        x.rh96_sequencial,                                                                                                                                              \n";
$sSqlRendimento .= "        z01_nome,                                                                                                                                                       \n";
$sSqlRendimento .= "        (select array_agg( distinct rh99_regist order by rh99_regist asc)                                                                                               \n";
$sSqlRendimento .= "           from rhdirfgeracaodadospessoal                                                                                                                               \n";
$sSqlRendimento .= "                inner join rhdirfgeracaodadospessoalvalor on rh96_sequencial = rh98_rhdirfgeracaodadospessoal                                                           \n";
$sSqlRendimento .= "                inner join rhdirfgeracaopessoalregist     on rh98_sequencial = rh99_rhdirfgeracaodadospessoalvalor                                                      \n";
$sSqlRendimento .= "          where rhdirfgeracaodadospessoal.rh96_sequencial =   x.rh96_sequencial                                                                                         \n";
$sSqlRendimento .= "          group by rh96_numcgm) as regist,                                                                                                                              \n";
$sSqlRendimento .= "        rh96_cpfcnpj,                                                                                                                                                   \n";
$sSqlRendimento .= "        x.r70_codigo,                                                                                                                                                   \n";
$sSqlRendimento .= "        x.r70_estrut,                                                                                                                                                   \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 1                                                                                                                      \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes between 1 and 12                                                                                                                     \n";
$sSqlRendimento .= "                  ),0) as rendimento,                                                                                                                                   \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 1                                                                                                                      \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes = 13                                                                                                                                 \n";
$sSqlRendimento .= "                  ),0) as rendimento_13,                                                                                                                                \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 2                                                                                                                      \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes between 1 and 12                                                                                                                     \n";
$sSqlRendimento .= "                  ),0) as prev_oficial,                                                                                                                                 \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 2                                                                                                                      \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes = 13                                                                                                                                 \n";
$sSqlRendimento .= "                  ),0) as prev_oficial_13,                                                                                                                              \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 3                                                                                                                      \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes between 1 and 12                                                                                                                     \n";
$sSqlRendimento .= "                ), 0) as prev_privada,                                                                                                                                  \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 3                                                                                                                      \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes = 13                                                                                                                                 \n";
$sSqlRendimento .= "                ), 0) as prev_privada_13,                                                                                                                               \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 4                                                                                                                      \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes between 1 and 12                                                                                                                     \n";
$sSqlRendimento .= "                ),0) as depend,                                                                                                                                         \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 4                                                                                                                      \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes = 13                                                                                                                                 \n";
$sSqlRendimento .= "                ),0) as depend_13,                                                                                                                                      \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 5                                                                                                                      \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes between 1 and 12                                                                                                                     \n";
$sSqlRendimento .= "                 ),0) as pensao,                                                                                                                                        \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 5                                                                                                                      \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes = 13                                                                                                                                 \n";
$sSqlRendimento .= "                 ),0) as pensao_13,                                                                                                                                     \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 6                                                                                                                      \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes between 1 and 12                                                                                                                     \n";
$sSqlRendimento .= "                ),0) as irrf,                                                                                                                                           \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 6                                                                                                                      \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes = 13                                                                                                                                 \n";
$sSqlRendimento .= "                ),0) as irrf_13,                                                                                                                                        \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 7                                                                                                                      \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes between 1 and 12                                                                                                                     \n";
$sSqlRendimento .= "                  ),0) as aposentadoria_65,                                                                                                                             \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 7                                                                                                                      \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes = 13                                                                                                                                 \n";
$sSqlRendimento .= "                  ),0) as aposentadoria_65_13,                                                                                                                          \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 8                                                                                                                      \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes between 1 and 12                                                                                                                     \n";
$sSqlRendimento .= "                 ),0) as diaria,                                                                                                                                        \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 9                                                                                                                      \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes between 1 and 12                                                                                                                     \n";
$sSqlRendimento .= "                 ),0) as ind_rescisao,                                                                                                                                  \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 10                                                                                                                     \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes between 1 and 12                                                                                                                     \n";
$sSqlRendimento .= "                 ),0) as abono,                                                                                                                                         \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 15                                                                                                                     \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes between 1 and 12                                                                                                                     \n";
$sSqlRendimento .= "                 ),0) as outros5,                                                                                                                                       \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 11                                                                                                                     \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes between 1 and 12                                                                                                                     \n";
$sSqlRendimento .= "                  ),0) as molestia_grave_inativos,                                                                                                                      \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor = 12                                                                                                                     \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes between 1 and 12                                                                                                                     \n";
$sSqlRendimento .= "                ),0) as molestia_grave_ativos,                                                                                                                          \n";
$sSqlRendimento .= "        coalesce(( select sum(rh98_valor)                                                                                                                               \n";
$sSqlRendimento .= "                     from rhdirfgeracaodadospessoalvalor                                                                                                                \n";
$sSqlRendimento .= "                    where rh98_rhdirftipovalor IN (13,14)                                                                                                               \n";
$sSqlRendimento .= "                      and rh98_rhdirfgeracaodadospessoal = x.rh96_sequencial                                                                                            \n";
$sSqlRendimento .= "                      and rh98_mes between 1 and 12                                                                                                                     \n";
$sSqlRendimento .= "                 ),0) as plano_saude                                                                                                                                    \n";
$sSqlRendimento .= "   from ( select distinct                                                                                                                                               \n";
$sSqlRendimento .= "                 rh96_sequencial,                                                                                                                                       \n";
$sSqlRendimento .= "                 rh96_numcgm,                                                                                                                                           \n";
$sSqlRendimento .= "                 z01_nome,                                                                                                                                              \n";
$sSqlRendimento .= "                 rh96_cpfcnpj,                                                                                                                                          \n";
$sSqlRendimento .= "                 rh96_regist,                                                                                                                                           \n";
$sSqlRendimento .= "                 r70_codigo,                                                                                                                                            \n";
$sSqlRendimento .= "                 r70_estrut,                                                                                                                                            \n";
$sSqlRendimento .= "                 r70_descr                                                                                                                                              \n";
$sSqlRendimento .= "            from rhdirfgeracao                                                                                                                                          \n";
$sSqlRendimento .= "                 inner join rhdirfgeracaodadospessoal      on rhdirfgeracaodadospessoal.rh96_rhdirfgeracao                  = rhdirfgeracao.rh95_sequencial             \n";
$sSqlRendimento .= "                 inner join rhdirfgeracaodadospessoalvalor on rhdirfgeracaodadospessoalvalor.rh98_rhdirfgeracaodadospessoal = rhdirfgeracaodadospessoal.rh96_sequencial \n";
$sSqlRendimento .= "                 inner join cgm                            on cgm.z01_numcgm                                                = rhdirfgeracaodadospessoal.rh96_numcgm     \n";
$sSqlRendimento .= "                 inner join rhdirfgeracaopessoalregist     on rhdirfgeracaodadospessoalvalor.rh98_sequencial                = rh99_rhdirfgeracaodadospessoalvalor       \n";
$sSqlRendimento .= "                 inner join rhpessoalmov                   on rh02_anousu                                                   = {$anofolha}                               \n";
$sSqlRendimento .= "                                                          and rh02_mesusu                                                   = {$mesfolha}                               \n";
$sSqlRendimento .= "                                                          and rh02_regist                                                   = rh99_regist                               \n";
$sSqlRendimento .= "                                                          and rh02_instit                                                   = ".db_getsession("DB_instit")."            \n";
$sSqlRendimento .= "                 inner join rhlota                         on rhlota.r70_codigo                                             = rhpessoalmov.rh02_lota                    \n";
$sSqlRendimento .= "                                                          and rhlota.r70_instit                                             = rhpessoalmov.rh02_instit                  \n";
$sSqlRendimento .= "         {$sWhere}                                                                                                                                                      \n";
$sSqlRendimento .= "        ) as x                                                                                                                                                          \n";

$rsSqlRendimento = db_query($sSqlRendimento);
$iNumRows        = pg_num_rows($rsSqlRendimento);

if ($iNumRows == 0) {

  db_redireciona('db_erros.php?fechar=true&db_erro=Registros não Processados para o Ano Calendário, entre em contato com o setor responsável.');
  exit;
}

$pdf = new scpdf();
$pdf->Open();

$pdf1 = new db_impcarne($pdf, $iTipoRelatorio);

for ($iInd = 0; $iInd < $iNumRows; $iInd++) {

	$oRendimento = db_utils::fieldsMemory($rsSqlRendimento, $iInd);

  $nome_pens = '';
  if ($oRendimento->pensao > 0) {

  	$sSqlPensao   = " select z01_nome as pensionista                    ";
    $sSqlPensao  .= "   from pensao                                     ";
    $sSqlPensao  .= "        inner join cgm on r52_numcgm = z01_numcgm  ";
    $sSqlPensao  .= "  where r52_anousu = {$anofolha}            ";
    $sSqlPensao  .= "    and r52_mesusu = {$mesfolha}            ";
    $sSqlPensao  .= "    and r52_regist in(".str_replace('}','',str_replace('{','',$oRendimento->regist)).") ";
    $rsSqlPensao  = db_query($sSqlPensao);
    $virg         = '';
    for ($iPensao = 0; $iPensao < pg_numrows($rsSqlPensao); $iPensao++) {

       $oPensao    = db_utils::fieldsMemory($rsSqlPensao, $iPensao);
       $nome_pens .= $virg.$oPensao->pensionista;
       $virg = ', ';
    }
  }

/**********************************************************************************
   Implementado por Jeferson Santos, quando for funcionarios do fundo municipal de
   saude das lotacoes abaixo, deve imprimir os dados da instituicao prefeitura,
   somente para Marica.
*/

  if ($oDbConfig->codcli == 19985){

     if ($oRendimento->r70_codigo == 222 || $oRendimento->r70_codigo == 223){
     
        $sSqlDbConfig  = " select ender,                                              ";
        $sSqlDbConfig .= "        cgc,                                                ";
        $sSqlDbConfig .= "        nomeinst,                                           ";
        $sSqlDbConfig .= "        munic,                                              ";
        $sSqlDbConfig .= "        telef                                               ";
        $sSqlDbConfig .= " from db_config                                             ";
        $sSqlDbConfig .= " where codigo = (select codigo from db_config where prefeitura = true) ";
        
        $rsSqlDbConfig    = db_query($sSqlDbConfig);
        $iNumRowsDbConfig = pg_num_rows($rsSqlDbConfig);
        if ($iNumRowsDbConfig > 0) {
              
              $oDbConfig2  = db_utils::fieldsMemory($rsSqlDbConfig, 0);
              $prefeitura  = db_translate($oDbConfig2->nomeinst);
              $enderpref   = db_translate($oDbConfig2->ender);
              $municpref   = db_translate($oDbConfig2->munic);
              $telefpref   = $oDbConfig2->telef;
              $cgcpref     = $oDbConfig2->cgc;
        }
     }
  }
/*=============================================================================================*/

  /**
   * Informações de Cabeçalho
   */
  $pdf1->prefeitura      = $prefeitura;
  $pdf1->enderpref       = $enderpref;
  $pdf1->municpref       = $municpref;
  $pdf1->telefpref       = $telefpref;
  $pdf1->cgcpref         = $cgcpref;

  /**
   * Informações Contribuintes
   */
  $pdf1->cpf             = $oRendimento->rh96_cpfcnpj;
  $pdf1->nome            = db_translate($oRendimento->z01_nome);
  $pdf1->resp            = db_translate($resp);
  $pdf1->pensionistas    = db_translate($nome_pens);
  $pdf1->ano             = $anobase;
  $pdf1->matricula       = str_replace('}','',str_replace('{','',$oRendimento->regist));
  $pdf1->lotacao         = $oRendimento->r70_codigo;
  $pdf1->num_comprovante = ($iInd+1);

  /**
   * Informações Bloco Rendimentos Isentos e Não Tributáveis
   */
  $oRendimento->rendimento -= ($oRendimento->aposentadoria_65 + $oRendimento->molestia_grave_inativos +
                               $oRendimento->molestia_grave_ativos
                              );
  $pdf1->w_salario       = ($oRendimento->rendimento < 0 ?0:$oRendimento->rendimento);
  $pdf1->w_contr         = $oRendimento->prev_oficial;
  $pdf1->w_privad        = $oRendimento->prev_privada;
  $pdf1->w_pensao        = $oRendimento->pensao + $oRendimento->pensao_13;
  $pdf1->w_irfonte       = $oRendimento->irrf;
  $pdf1->w_parte         = $oRendimento->aposentadoria_65 + $oRendimento->aposentadoria_65_13;
  $pdf1->w_diaria        = $oRendimento->diaria;
  $pdf1->w_aviso         = $oRendimento->molestia_grave_inativos+$oRendimento->molestia_grave_ativos;
  $pdf1->w_vlresc_ntrib  = $oRendimento->ind_rescisao;
  $pdf1->w_abono         = $oRendimento->abono;
  $pdf1->w_outros5       = $oRendimento->outros5;

  /**
   * Informações Bloco Rendimentos Sujeitos a Tributação Exclusiva
   * @var $n13Salario é a diferença entre os campos:
   *  1(rendimento),
   *  2(prev_oficial),
   *  3(prev_privada),
   *  4(depend),
   *  5(pensao),
   *  6(irrf).
   */
  $n13Salario            = ($oRendimento->rendimento_13
                            - $oRendimento->prev_oficial_13
                            - $oRendimento->prev_privada_13
                            - $oRendimento->depend_13
                            - $oRendimento->aposentadoria_65_13
                            - $oRendimento->pensao_13
                            - $oRendimento->irrf_13);
  if ($n13Salario < 0) {
  	$n13Salario = 0;
  }

  $pdf1->w_sal13         = $n13Salario;
  $pdf1->w_irrf13        = $oRendimento->irrf_13;
  $pdf1->w_outros6       = 0;

  /**
   * Informações Bloco Complementares
   */
  $pdf1->w_dmedic        = $oRendimento->plano_saude;

  $pdf1->imprime();
}

$pdf1->objpdf->Output();
