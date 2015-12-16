<?php

/**
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

/**
 * Model responsável pela manipulação dos dados no controller da importação do DES-IF
 * @author Luiz Marcelo Schmitt <luiz.marcelo@dbseller.com.br>
 */
class Contribuinte_Model_Desif {

  /**
   * Dados do contribuinte
   * @var array|object
   */
  private $oContribuinte       = NULL;

  /**
   * Dados de indentificação da desif(0000)
   * @var object
   */
  private $oDadosDesif         = NULL;

  /**
   * Dados de indentificação das contas(0100)
   * @var array
   */
  private $aDadosDesifContas   = array();

  /**
   * Dados de indentificação das tarifas(0200)
   * @var array
   */
  private $aDadosDesifTarifas  = array();

  /**
   * Dados de indentificação das receitas(0430)
   * @var array
   */
  private $aDadosDesifReceitas = array();

  /**
   * Constante criada para identificar o erro de conta do plano abrasf inexistente
   * @var int
   */
  const EXCEPTION_CONTA_INEXISTENTE_NO_PLANO_ABRASF = 654;

  /**
   * @param object $oContribuinte
   * @param array  $aDadosComuns
   * @param array  $aDadosApuracaoMensal
   */
  public function __construct($oContribuinte, array $aDadosComuns, array $aDadosApuracaoMensal) {

    $this->oContribuinte = Administrativo_Model_UsuarioContribuinte::getByAttribute('id', $oContribuinte->getContribuintes());
    $this->carregaIdentificador($aDadosComuns[0], $aDadosApuracaoMensal[0]);
    $this->carregarDadosContas($aDadosComuns);
    $this->carregarDadosReceitas($aDadosApuracaoMensal);
  }

  /**
   * Método responsável pela identificação das linhas identificadoras(0000) de cada arquivo
   *
   * @param $sIdentificadorComum
   * @param $sIdentificadorReceita
   *
   * @throws Exception
   */
  protected function carregaIdentificador ($sIdentificadorComum, $sIdentificadorReceita) {

    if ($sIdentificadorComum != $sIdentificadorReceita) {
      throw new Exception('Identificadores de competencia diferentes!');
    }

    $aDados          = explode('|', $sIdentificadorComum);
    $iCodigoRegistro = $aDados[1];

    if ($iCodigoRegistro == '0000') {

      $this->oDadosDesif->sCnpj               = trim($aDados[2]);
      $this->oDadosDesif->sCompetenciaInicial = $aDados[6];
      $this->oDadosDesif->sCompetenciaFinal   = $aDados[7];
      $this->oDadosDesif->sVersao             = $aDados[13];
      $this->oDadosDesif->sNomeArquivo        = "";
    }
  }

  /**
   * Carrega dos Dados das Receitas
   *
   * @param array $aDadosApuracaoMensal
   * @return bool
   */
  protected function carregarDadosReceitas (array $aDadosApuracaoMensal) {

    foreach ($aDadosApuracaoMensal as $iLinha => $sDados) {

      $aDados = explode('|', $sDados);

      if (is_array($aDados)) {

        $iCodigoRegistro = trim($aDados[1]);

        if ($iCodigoRegistro == '0430') {

          $oReceita = new StdClass();
          $oReceita->iIm           = (int) $aDados[2];
          $oReceita->sConta        = $aDados[3];
          $oReceita->sCodTribDesif = $aDados[4];
          $oReceita->fValrCredMens = DBSeller_Helper_Number_Format::toFloat($aDados[5]);
          $oReceita->fValrDebtMens = DBSeller_Helper_Number_Format::toFloat($aDados[6]);
          $oReceita->fReceDecl     = DBSeller_Helper_Number_Format::toFloat($aDados[7]);
          $oReceita->fDeduReceDecl = DBSeller_Helper_Number_Format::toFloat($aDados[8]);
          $oReceita->fDescDedu     = trim($aDados[9]);
          $oReceita->fBaseCalc     = DBSeller_Helper_Number_Format::toFloat($aDados[10]);
          $oReceita->fAliqIssqn    = DBSeller_Helper_Number_Format::toFloat($aDados[11]);
          $oReceita->fInctFisc     = DBSeller_Helper_Number_Format::toFloat($aDados[12]);

          $this->aDadosDesifReceitas[$iLinha] = $oReceita;
        }
      }
    }

    return TRUE;
  }

  /**
   * Carrega os Dados das Contas e das Tarifas
   *
   * @param array $aDadosComuns
   * @return bool
   */
  protected function carregarDadosContas (array $aDadosComuns) {

    foreach ($aDadosComuns as $iLinha => $sDados) {

      $aDados = explode('|', $sDados);

      if (is_array($aDados)) {

        $iCodigoRegistro = trim($aDados[1]);

        if ($iCodigoRegistro == '0100') {

          $oDadosConta = new StdClass();
          $oDadosConta->sConta          = trim($aDados[2]);
          $oDadosConta->sDescricaoConta = trim($aDados[3]);
          $oDadosConta->sNome           = trim($aDados[4]);
          $oDadosConta->sContaPai       = trim($aDados[5]);
          $oDadosConta->sContaCosif     = trim($aDados[6]);

          $this->aDadosDesifContas[$iLinha] = $oDadosConta;
        } else if ($iCodigoRegistro == '0200') {

          $oImportacaoDesifTarifa = new StdClass();
          $oImportacaoDesifTarifa->sDescricao   = trim($aDados[3]);
          $oImportacaoDesifTarifa->sConta       = trim($aDados[4]);
          $oImportacaoDesifTarifa->sTarifaConta = str_replace(' ', '.', trim($aDados[2]));

          $this->aDadosDesifTarifas[$iLinha] = $oImportacaoDesifTarifa;
        }
      }
    }

    return TRUE;
  }

  /**
   * Método que verifica a integridade dos valores informados nos arquivos
   * @return bool
   * @throws Exception
   */
  public function validacao() {

    // Verifica o contribuinte
    if (empty($this->oContribuinte)) {
      throw new Exception("Contribuinte não informado!");
    }

    $sCnpjContribuinte = $this->oContribuinte->getEntity()->getCnpjCpf();

    if (substr($sCnpjContribuinte, 0, 8) != $this->oDadosDesif->sCnpj) {
      throw new Exception('O contribuinte informado inválido!');
    }

    if (empty($this->oDadosDesif->sCompetenciaInicial) || empty($this->oDadosDesif->sCompetenciaFinal)) {
      throw new Exception('Informe a competencia do arquivo DES-IF!');
    }

    if (empty($this->oDadosDesif->sVersao)) {
      throw new Exception('Informe o código da versão do DES-IF!');
    }

    // Verifica a competencia informada (0000)
    $oImportacaoDesif = Contribuinte_Model_ImportacaoDesif::getByAttributes(array(
                                                                              'competencia_inicial' => $this->oDadosDesif->sCompetenciaInicial,
                                                                              'competencia_final'   => $this->oDadosDesif->sCompetenciaFinal,
                                                                              'contribuinte'        => $this->oContribuinte->getEntity()
                                                                            ));

    if (!empty($oImportacaoDesif)) {
      throw new Exception("A competencia de {$this->oDadosDesif->sCompetenciaInicial} à {$this->oDadosDesif->sCompetenciaFinal} já foi importada!");
    }

    if (empty($this->aDadosDesifContas)) {
      throw new Exception('Dados de conta(0100) estão incorretos!');
    }

    if (empty($this->aDadosDesifTarifas)) {
      throw new Exception('Dados de receitas(0430) estão incorretos!');
    }

    if (empty($this->aDadosDesifReceitas)) {
      throw new Exception('Dados de tarifas bancárias(0200) estão incorretos!');
    }

    // Verifica os campos das contas
    foreach ($this->aDadosDesifContas as $iLinha => $oDesifConta) {

      if (empty($oDesifConta->sConta)) {
        throw new Exception('Código da conta não informada na linha ' . ( $iLinha + 1 ) . '!');
      }

      if (empty($oDesifConta->sDescricaoConta)) {
        throw new Exception('Descrição da conta não informada na linha ' . ( $iLinha + 1 ) . '!');
      }

      if (empty($oDesifConta->sNome)) {
        throw new Exception('Nome da conta não informada na linha ' . ( $iLinha + 1 ) . '!');
      }

      if (empty($oDesifConta->sContaCosif)) {
        throw new Exception('Código Conta COS-IF não informada na linha ' . ( $iLinha + 1 ) . '!');
      }
    }

    // Verifica campos das tarifas bancarias
    foreach ($this->aDadosDesifTarifas as $iLinha => $oDesifTarifas) {

      if (empty($oDesifTarifas->sDescricao)) {
        throw new Exception('Descrição da tarifa bancária não informada na linha ' . ( $iLinha + 1 ) . '!');
      }

      if (empty($oDesifTarifas->sConta)) {
        throw new Exception('Código da conta não informada na tarifa na linha ' . ( $iLinha + 1 ) . '!');
      }

      if (empty($oDesifTarifas->sTarifaConta)) {
        throw new Exception('Código da tarifa bancária não informada na linha ' . ( $iLinha + 1 ) . '!');
      }
    }

    // Verifica os valores das receitas
    foreach ($this->aDadosDesifReceitas as $iLinha => $oDesifReceita) {

      // Verifica se a Inscrição Municipal informada no arquivo é igual ao contribuinte da sessão
      if ($oDesifReceita->iIm != $this->oContribuinte->getIm()) {
        throw new Exception('Inscrição municipal informadana é diferente do contribuinte logado na linha ' . ( $iLinha + 1 ) . '!');
      }

      if (empty($oDesifReceita->sConta)) {
        throw new Exception('Código da conta(Sub_Titu) não informada na linha ' . ( $iLinha + 1 ) . '!');
      }

      if (empty($oDesifReceita->sCodTribDesif)) {
        throw new Exception('Código de tributação DES-IF(Cod_Trib_DES-IF) não informado na linha ' . ( $iLinha + 1 ) . '!');
      }

      if (empty($oDesifReceita->fValrCredMens)) {
        throw new Exception('Valor total dos lançamentos a crédito(Valr_Cred_Mens) não informado na linha ' . ( $iLinha + 1 ) . '!');
      }

      if (empty($oDesifReceita->fValrDebtMens)) {
        throw new Exception('Valor total dos lançamentos a débito(Valr_Debt_Mens) não informado na linha ' . ( $iLinha + 1 ) . '!');
      }

      if (empty($oDesifReceita->fReceDecl)) {
        throw new Exception('Valor da receita tributável pelo ISSQN(Rece_Decl) não informado na linha ' . ( $iLinha + 1 ) . '!');
      }

      if (empty($oDesifReceita->fBaseCalc)) {
        throw new Exception('Valor da base de cálculo do ISSQN(Base_Calc) não informado na linha ' . ( $iLinha + 1 ) . '!');
      }

      if (empty($oDesifReceita->fAliqIssqn)) {
        throw new Exception('Alíquota que se aplica à base de cálculo(Aliq_ISSQN) não informado na linha ' . ( $iLinha + 1 ) . '!');
      }
    }

    return TRUE;
  }

  /**
   * Método responsável pela inserção dos dados no Banco de Dados
   * @throws Exception
   */
  public function processarArquivo() {

    if ($this->validacao()) {

      $oDoctrine = Zend_Registry::get('em');

      try {

        $oDoctrine->getConnection()->beginTransaction();

        $oDataSistema     = new DateTime();
        $oImportacaoDesif = new Contribuinte_Model_ImportacaoDesif();
        $oImportacaoDesif->setContribuinte($this->oContribuinte->getEntity());
        $oImportacaoDesif->setDataImportacao($oDataSistema);
        $oImportacaoDesif->setCompetenciaInicial($this->oDadosDesif->sCompetenciaInicial);
        $oImportacaoDesif->setCompetenciaFinal($this->oDadosDesif->sCompetenciaFinal);
        $oImportacaoDesif->setVersao($this->oDadosDesif->sVersao);
        $oImportacaoDesif->setNomeArquivo($this->oDadosDesif->sNomeArquivo);
        $oImportacaoDesif->persist();
		
        //Retornar o id para gerar o protocolo de sucesso
        $iImportacaoDesifId = $oImportacaoDesif->getId();

        // Grava as contas que ainda não estão salvas no plano de contas do DESIF
        foreach ($this->aDadosDesifContas as $oDadosConta) {

          $aDesifConta = Contribuinte_Model_ImportacaoDesifConta::getByAttributes(array(
          	                                                                             'conta'        => $oDadosConta->sConta,
                                                                                         'contribuinte' => $this->oContribuinte->getEntity()
                                                                                       ));
          $oDesifConta = array_shift($aDesifConta);

          if (empty($oDesifConta)) {

            $oPlanoContaAbrasf = Contribuinte_Model_PlanoContaAbrasf::getByAttribute('conta_abrasf', $oDadosConta->sContaCosif);

            //Verifica se existe conta no plano abrasf para fazer o vínculo
            if (empty($oPlanoContaAbrasf)) {

              throw new Exception('Conta informada ' . $oDadosConta->sContaCosif . ' inexistente no plano de conta da ABRASF.',
                                  self::EXCEPTION_CONTA_INEXISTENTE_NO_PLANO_ABRASF);
            }

            $oImportacaoDesifConta = new Contribuinte_Model_ImportacaoDesifConta();
            $oImportacaoDesifConta->setConta($oDadosConta->sConta);
            $oImportacaoDesifConta->setNome($oDadosConta->sNome);
            $oImportacaoDesifConta->setDescricaoConta($oDadosConta->sDescricaoConta);
            $oImportacaoDesifConta->setPlanoContaAbrasf($oPlanoContaAbrasf->getEntity());
            $oImportacaoDesifConta->setContribuinte($this->oContribuinte->getEntity());

            if (!empty($oDadosConta->sContaPai)) {

              $aContaPai = Contribuinte_Model_ImportacaoDesifConta::getByAttributes(array(
          	                                                                             'conta'        => $oDadosConta->sContaPai,
                                                                                         'contribuinte' => $this->oContribuinte->getEntity()
                                                                                       ));
              $oContaPai = array_shift($aContaPai);
              $oImportacaoDesifConta->setImportacaoDesifConta($oContaPai->getEntity());
            }

            $oImportacaoDesifConta->persist();
          }
        }

        // Adiciona os dados das tarifas do banco
        foreach ($this->aDadosDesifTarifas as $oDesifTarifa) {

          $aImportacaoDesifConta = Contribuinte_Model_ImportacaoDesifConta::getByAttributes(array(
          	                                                                             'conta'        => $oDesifTarifa->sConta,
                                                                                         'contribuinte' => $this->oContribuinte->getEntity()
                                                                                       ));
          $oImportacaoDesifConta = array_shift($aImportacaoDesifConta);

          if (!empty($oImportacaoDesifConta)) {

            $oImportacaoDesifTarifa = new Contribuinte_Model_ImportacaoDesifTarifa();
            $oImportacaoDesifTarifa->setImportacaoDesif($oImportacaoDesif->getEntity());
            $oImportacaoDesifTarifa->setImportacaoDesifConta($oImportacaoDesifConta->getEntity());
            $oImportacaoDesifTarifa->setTarifaBanco($oDesifTarifa->sTarifaConta);
            $oImportacaoDesifTarifa->setDescricao($oDesifTarifa->sDescricao);
            $oImportacaoDesifTarifa->persist();
          }
        }

        // Adiciona os dados das receitas informadas
        foreach ($this->aDadosDesifReceitas as $oReceita) {

          $aImportacaoDesifConta = Contribuinte_Model_ImportacaoDesifConta::getByAttributes(array(
          	                                                                             'conta'        => $oReceita->sConta,
                                                                                         'contribuinte' => $this->oContribuinte->getEntity()
                                                                                       ));
          $oImportacaoDesifConta = array_shift($aImportacaoDesifConta);

          if (empty($oImportacaoDesifConta)) {
            throw new Exception("Conta {$oReceita->sConta} é inválida!");
          }

          $oImportacaoDesifReceita = new Contribuinte_Model_ImportacaoDesifReceita();
          $oImportacaoDesifReceita->setImportacaoDesif($oImportacaoDesif->getEntity());
          $oImportacaoDesifReceita->setImportacaoDesifConta($oImportacaoDesifConta->getEntity());
          $oImportacaoDesifReceita->setSubTitu($oReceita->sConta);
          $oImportacaoDesifReceita->setCodTribDesif($oReceita->sCodTribDesif);
          $oImportacaoDesifReceita->setValrCredMens($oReceita->fValrCredMens);
          $oImportacaoDesifReceita->setValrDebtMens($oReceita->fValrDebtMens);
          $oImportacaoDesifReceita->setReceDecl($oReceita->fReceDecl);
          $oImportacaoDesifReceita->setDeduReceDecl($oReceita->fDeduReceDecl);
          $oImportacaoDesifReceita->setDescDedu($oReceita->fDescDedu);
          $oImportacaoDesifReceita->setBaseCalc($oReceita->fBaseCalc);
          $oImportacaoDesifReceita->setAliqIssqn($oReceita->fAliqIssqn);
          $oImportacaoDesifReceita->setInctFisc($oReceita->fInctFisc);
          $oImportacaoDesifReceita->persist();
        }

        $oDoctrine->getConnection()->commit();
        
        return $iImportacaoDesifId;
      } catch (Exception $oErro) {

        $oDoctrine->getConnection()->rollback();

        if ($oErro->getCode() == self::EXCEPTION_CONTA_INEXISTENTE_NO_PLANO_ABRASF) {
          throw $oErro;
        }

        throw new Exception('Erro ao processar os arquivos da DES-IF!');
      }
    }
  }
}