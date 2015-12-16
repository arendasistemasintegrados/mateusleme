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
 * Class Contribuinte_NotaController
 *
 * @package Contribuinte/Controllers
 * @author Everton Catto Heckler <everton.heckler@dbseller.com.br>
 */

class Contribuinte_NotaController extends Contribuinte_Lib_Controller_AbstractController {

  /**
   * Tipo de Nota
   */
  const TIPO_NOTA = NULL;

  /**
   * Construtor da classe
   *
   * @see Contribuinte_Lib_Controller_AbstractController::init()
   */
  public function init() {

    parent::init();
  }

  /**
   * Emissao de NFSE
   */
  public function indexAction() {

    $oContribuinte      = $this->_session->contribuinte;
    $sCodigoVerificacao = NULL;
    $oForm = $this->formNota($sCodigoVerificacao, $oContribuinte);

    try {

      $this->view->bloqueado_msg = FALSE;

      $aServicos          = Contribuinte_Model_Servico::getByIm($oContribuinte->getInscricaoMunicipal());
      $oForm->setListaServicos($aServicos);

      // Verifica se o contribuinte é prestador de serviços
      if ($aServicos == NULL || empty($aServicos)) {

        $this->view->bloqueado_msg = $this->translate->_('Empresa não prestadora de serviço.');

        return;
      }

      // Verifica se o contribuinte é emissor de NFSe
      if ($oContribuinte->getTipoEmissao() != Contribuinte_Model_ContribuinteAbstract::TIPO_EMISSAO_NOTA) {

        $this->view->bloqueado_msg = $this->translate->_('Empresa não emissora de NFS-E.');

        return;
      }

      // Dados do formulário
      $aDados = $this->getRequest()->getParams();

      // Preeenche da nota copiada
      if (isset($aDados['id_copia'])) {

        $iIdCopiaNota    = $aDados['id_copia'];
        $aCopiaDadosNota = Contribuinte_Model_Nota::getByAttribute(
          'id',
          $iIdCopiaNota,
          '=',
          Contribuinte_Model_Nota::TIPO_RETORNO_ARRAY
        );

        $aDados = $aCopiaDadosNota[0];
        $aDados['id_copia_nota'] = $iIdCopiaNota;

      }

      $oForm->preenche($aDados);
      $oForm->getElement('nota_substituida')->setRequired($oForm->getElement('nota_substituta')->isChecked());

      // Processa o formulário
      if ($this->getRequest()->isPost()) {


        // Verifica se é tomador é
        if (!$this->podeReterPessoaFisica($aDados)) {

          $sMensagemErro = 'Permitido reter apenas para Pessoa Jurídica.';

          $this->view->form       = $oForm;
          $this->view->messages[] = array('error' => $sMensagemErro);

          return;
        }

        // Limpa espaços no email
        $aDados['t_email'] = trim($aDados['t_email']);

        // Validadores
        $oValidaData = new Zend_Validate_Date(array('format' => 'yyyy-MM-dd'));

        // Bloqueia a emissão se possuir declarações sem movimento
        if (isset($aDados['dt_nota']) && $oValidaData->isValid($aDados['dt_nota'])) {

          $oDataSimples = new DateTime($aDados['dt_nota']);

          $aDeclaracaoSemMovimento = Contribuinte_Model_Competencia::getDeclaracaoSemMovimentoPorContribuintes(
                                                                   $oContribuinte->getInscricaoMunicipal(),
                                                                   $oDataSimples->format('Y'),
                                                                   $oDataSimples->format('m'));

          if (count($aDeclaracaoSemMovimento) > 0
           && $oContribuinte->getOptanteSimplesCategoria() !=
            Contribuinte_Model_ContribuinteAbstract::OPTANTE_SIMPLES_TIPO_MEI) {

            $sMensagemErro = 'Não é possível emitir um documento nesta data, pois a competência foi declarada como ';
            $sMensagemErro .= 'sem movimento.<br>Entre em contato com o setor de arrecadação da prefeitura.';

            $this->view->form       = $oForm;
            $this->view->messages[] = array('error' => $sMensagemErro);

            return;
          }
        }

        // Valida os dados do formulário
        if (!$oForm->isValid($aDados)) {

          $this->view->form       = $oForm;
          $this->view->messages[] = array('error' => $this->translate->_('Preencha os dados corretamente.'));
        } else {

          // Remova chaves inválidas
          unset($aDados['enviar'], $aDados['action'], $aDados['controller'], $aDados['module'], $aDados['estado']);


          // filtro para retornar somente numeros
          $aFilterDigits = new Zend_Filter_Digits();

          $aDados['p_im']       = $oContribuinte->getInscricaoMunicipal();
          $aDados['t_cnpjcpf']  = $aFilterDigits->filter($aDados['t_cnpjcpf']);
          $aDados['t_cep']      = $aFilterDigits->filter($aDados['t_cep']);
          $aDados['t_telefone'] = $aFilterDigits->filter($aDados['t_telefone']);
          $aDados['tipo_nota']  = self::TIPO_NOTA;

          // Recalcula valores para garantir a integridade dos cálculos
          $aServico = Contribuinte_Model_Servico::getByCodServico($aDados['s_dados_cod_tributacao']);
          $oServico = reset($aServico);

          /* caso a atividade sejá retida no municipio de acordo com a legislação
           * envia para o teste de guia um parametro = true
           */
          $aDados['s_tributacao_municipio'] = $oServico->attr('tributacao_municipio');

          $oParametros                          = new stdClass();
          $oParametros->vlr_servico             = $oForm->getElement('s_vl_servicos')->getValue();
          $oParametros->s_vl_deducoes           = $oForm->getElement('s_vl_deducoes')->getValue();
          $oParametros->perc_aliquota           = $oForm->getElement('s_vl_aliquota')->getValue();
          $oParametros->vlr_pis                 = $oForm->getElement('s_vl_pis')->getValue();
          $oParametros->vlr_cofins              = $oForm->getElement('s_vl_cofins')->getValue();
          $oParametros->vlr_inss                = $oForm->getElement('s_vl_inss')->getValue();
          $oParametros->vlr_ir                  = $oForm->getElement('s_vl_ir')->getValue();
          $oParametros->vlr_csll                = $oForm->getElement('s_vl_csll')->getValue();
          $oParametros->vlr_outras_retencoes    = $oForm->getElement('s_vl_outras_retencoes')->getValue();
          $oParametros->vlr_desc_condicionado   = $oForm->getElement('s_vl_condicionado')->getValue();
          $oParametros->vlr_desc_incondicionado = $oForm->getElement('s_vl_desc_incondicionado')->getValue();
          $oParametros->imposto_retido_tomador  = $oForm->getElement('s_dados_iss_retido')->getValue() == 1;
          $oParametros->deducao_editavel        = $oServico->attr('deducao') == 't';
          $oParametros->formatar_valores_ptbr   = TRUE;

          // Calcula os valores da nota
          $oCalculoValores = Contribuinte_Model_Nota::calcularValores($oParametros);

          // Valores calculados
          $aDados['s_vl_deducoes']            = $oCalculoValores->s_vl_deducoes;
          $aDados['s_vl_bc']                  = $oCalculoValores->vlr_base;
          $aDados['s_vl_iss']                 = $oCalculoValores->vlr_iss;
          $aDados['s_vl_pis']                 = $oCalculoValores->vlr_pis;
          $aDados['s_vl_cofins']              = $oCalculoValores->vlr_cofins;
          $aDados['s_vl_inss']                = $oCalculoValores->vlr_inss;
          $aDados['s_vl_ir']                  = $oCalculoValores->vlr_ir;
          $aDados['s_vl_csll']                = $oCalculoValores->vlr_csll;
          $aDados['s_vl_outras_retencoes']    = $oCalculoValores->vlr_outras_retencoes;
          $aDados['s_vl_condicionado']        = $oCalculoValores->vlr_desc_condicionado;
          $aDados['s_vl_desc_incondicionado'] = $oCalculoValores->vlr_desc_incondicionado;
          $aDados['s_vl_liquido']             = $oCalculoValores->vlr_liquido;
          $aDados['dt_nota']                  = Contribuinte_Model_Nota::getDateTime($aDados['dt_nota']);
          $aDados['id_contribuinte']          = $oContribuinte->getIdUsuarioContribuinte();
          $aDados['id_usuario']               = $this->usuarioLogado->getId();

          if ($oForm->getElement('nota_substituta')->isChecked()) {

            $aAtributosPesquisaNotaSubstituida = array(
              'id_contribuinte' => $oContribuinte->getIdUsuarioContribuinte(),
              'nota'            => $aDados['nota_substituida'],
              'cancelada'       => FALSE
            );

            $oNotaSubstituida = Contribuinte_Model_Nota::getByAttributes($aAtributosPesquisaNotaSubstituida);
            $oNotaSubstituida = reset($oNotaSubstituida);

            /*
             * Caso exista a nota a ser substituida, verifica:
             *  - Se já não possue nota substituta
             *  - Se não possui guia emitida
             */
            if ($oNotaSubstituida instanceof Contribuinte_Model_Nota &&
              !$oNotaSubstituida->getIdNotaSubstituta() &&
              !Contribuinte_Model_Guia::existeGuia($oContribuinte,
                                                   $oNotaSubstituida->getMes_comp(),
                                                   $oNotaSubstituida->getAno_comp(),
                                                   Contribuinte_Model_Guia::$DOCUMENTO_ORIGEM_NFSE)
            ) {

              // Seta os dados da nota
              $aDados['idNotaSubstituida'] = $oNotaSubstituida->getId();
            } else {

              $sMensagemErro  = '<b>Erro ao substituir a nota, verifique se:</b><br>';
              $sMensagemErro .= '- A nota informada já foi emitida no sistema.<br>';
              $sMensagemErro .= '- A nota informada não foi substituida.<br>';
              $sMensagemErro .= '- A nota informada não possui guia emitida.';

              $this->view->messages[] = array('error' => $this->translate->_($sMensagemErro));
              $this->view->form       = $oForm;

              return NULL;
            }

            unset($aDados['nota_substituta'], $aDados['nota_substituida']);
          }
          // Verifica se a nota é substituta

          // Persiste os dados na base de dados
          $oNota = new Contribuinte_Model_Nota();

          if ($oNota->persist($aDados)) {

            // Se for nota substituta
            if (isset($oNotaSubstituida) && $oNotaSubstituida instanceof Contribuinte_Model_Nota) {

              $sMensagemCancelamento = "Substituida pela Nota nº {$oNota->getNota()}";

              $oNotaSubstituida->setIdNotaSubstituta($oNota->getId());
              $oNotaSubstituida->setCancelada(TRUE);
              $oNotaSubstituida->setCancelamentoJustificativa($this->translate->_($sMensagemCancelamento));
              $oNotaSubstituida->setEmite_guia(FALSE);
              $oNotaSubstituida->persist($oNotaSubstituida->getEntity());
            }
          } else {

            $this->view->form       = $oForm;
            $this->view->messages[] = array('error' => $this->translate->_('Ocorreu um erro emitir a nota.'));

            return NULL;
          }

          // Envia email para o tomador
          $oValidaEmail = new Zend_Validate_EmailAddress();

          if ($oValidaEmail->isValid($aDados['t_email'])) {

            $iInscricaoMunicipal = $aDados['p_im'];
            $oContribuinte       = Contribuinte_Model_Contribuinte::getByInscricaoMunicipal($iInscricaoMunicipal);

            // Informações da view
            $this->view->nota          = $oNota;
            $this->view->tomadorNome   = $aDados['t_razao_social'];
            $this->view->prestadorNome = $oContribuinte->getNome('nome');
            $this->view->prestadorCnpj = DBSeller_Helper_Number_Format::maskCPF_CNPJ($oContribuinte->getCgcCpf());
            $this->view->nfseNumero    = $oNota->getNota();
            $this->view->nfseUrl       = $oNota->getUrlVerificacaoNota();

            // Renderiza o email com o texto diferente para notas substitutas
            if (isset($oNotaSubstituida) && $oNotaSubstituida instanceof Contribuinte_Model_Nota) {

              // Informações da View
              $this->view->sUrlNotaSubstituida = $oNotaSubstituida->getUrlVerificacaoNota();
              $this->view->oNotaSubstituida    = $oNotaSubstituida;
              $sMensagemEmail                  = $this->view->render('nota/email-emissao-substituida.phtml');
            } else {
              $sMensagemEmail = $this->view->render('nota/email-emissao.phtml');
            }

            $sArquivoPdfNfse = $this->getNotaImpressao($oNota->getCod_verificacao(), TRUE, TRUE);

            // Envia Email
            DBSeller_Helper_Mail_Mail::sendAttachment($aDados['t_email'],
                                                      "Nota Fiscal Eletrônica nº {$oNota->getNota()}",
                                                      $sMensagemEmail,
                                                      $sArquivoPdfNfse);
          }

          $this->view->messages[] = array('success' => $this->translate->_('Nota emitida com sucesso.'));
          $this->_redirector->gotoSimple('dadosnota', NULL, NULL, array('id' => $oNota->getId()));
        }
      } else {

        $this->view->form = $oForm;
        $this->view->form->setListaServicos($aServicos);
      }
    } catch (Exception $oError) {

      $this->view->form       = $oForm;
      $this->view->messages[] = array('error' => $this->translate->_($oError->getMessage()));

      return NULL;
    }

  }

  /**
   * Template Email Emissao NFSE
   */
  public function emailEmissaoAction() {

    parent::noTemplate();
  }

  /**
   * Responsável por atualizar a lista de atividades de acordo com as atividades finalizadas ou não
   * para a não prestação do serviço
   */
  public function defineListaAtividadesAction() {

    try {

      $sData              = $this->getRequest()->getParam('data_emissao');
      $oContribuinte      = $this->_session->contribuinte;
      $aServicos          = Contribuinte_Model_Servico::getByIm($oContribuinte->getInscricaoMunicipal(), true);
      $aListaAtividades   = array();

      foreach ($aServicos as $oServico) {

        $DataFim     = new DateTime($oServico->attr('dt_fim'));
        $DataEmissao = new DateTime($sData);

        if ($DataEmissao->getTimestamp() < $DataFim->getTimestamp()) {
          $aListaAtividades[$oServico->attr('cod_atividade')] = DBSeller_Helper_String_Format::wordsCap($oServico->attr('atividade'));
        }
      }

      echo $this->getHelper('json')->sendJson($aListaAtividades);

    } catch (Exception $oError) {

      $aRetorno['erro']     = TRUE;
      $aRetorno['mensagem'] = $oError->getMessage();

      echo $this->getHelper('json')->sendJson($aRetorno);
    }
  }

  /**
   * View para reenvio de email (Modal)
   */
  public function emailEnviarAction() {

    parent::noTemplate();

    $aDados = $this->getRequest()->getParams();
    $oForm  = new Contribuinte_Form_NotaEmail();
    $oForm->populate($aDados);

    $this->view->form = $oForm;
  }

  /**
   * Execucao do reenvio de email via ajax
   */
  public function emailEnviarPostAction() {

    $aDados = $this->getRequest()->getParams();
    $oForm  = new Contribuinte_Form_NotaEmail();
    $oForm->populate($aDados);

    if (!$oForm->isValid($aDados)) {

      $aRetornoJson['status']  = FALSE;
      $aRetornoJson['fields']  = array_keys($oForm->getMessages());
      $aRetornoJson['error'][] = 'Preencha os dados corretamente.';
    } else {

      try {

        $oNota               = Contribuinte_Model_Nota::getById($aDados['id_nfse']);
        $iInscricaoMunicipal = $oNota->getP_im();
        $oContribuinte       = Contribuinte_Model_Contribuinte::getByInscricaoMunicipal($iInscricaoMunicipal);
        $aArquivoPdfNfse     = $this->getNotaImpressao($oNota->getCod_verificacao(), TRUE, TRUE);

        // Informações da View
        $this->view->nota          = $oNota;
        $this->view->tomadorNome   = $oNota->getT_razao_social();
        $this->view->prestadorNome = $oContribuinte->getNome();
        $this->view->prestadorCnpj = DBSeller_Helper_Number_Format::maskCPF_CNPJ($oContribuinte->getCgcCpf());
        $this->view->nfseNumero    = $oNota->getNota();
        $this->view->nfseUrl       = $oNota->getUrlVerificacaoNota();

        // Se for nota substituda exibe o texto do email diferente
        if ($oNota->getIdNotaSubstituida()) {

          $oNotaSubstituida = Contribuinte_Model_Nota::getById($oNota->getIdNotaSubstituida());

          // Informações da view
          $this->view->sUrlNotaSubstituida = $oNotaSubstituida->getUrlVerificacaoNota();
          $this->view->oNotaSubstituida    = $oNotaSubstituida;
          $sMensagemEmail                  = $this->view->render('nota/email-emissao-substituida.phtml');
        } else {
          $sMensagemEmail = $this->view->render('nota/email-emissao.phtml');
        }

        // Envia Email
        $lEmail = DBSeller_Helper_Mail_Mail::sendAttachment($aDados['email'],
                                                            "Nota Fiscal Eletrônica nº {$oNota->getNota()}",
                                                            $sMensagemEmail,
                                                            $aArquivoPdfNfse);

        if ($lEmail) {

          $aRetornoJson['status']  = TRUE;
          $aRetornoJson['success'] = 'Email enviado com sucesso';
        } else {
          throw new Exception('Erro ao enviar o email');
        }

        // Apaga o arquivo temporario gerado para envio do email
        unlink($aArquivoPdfNfse['location']);
      } catch (Exception $oErro) {

        $aRetornoJson['status']  = FALSE;
        $aRetornoJson['error'][] = $oErro->getMessage();
      }
    }

    echo $this->getHelper('json')->sendJson($aRetornoJson);
  }

  /**
   * Dados da NFSE Emitida
   */
  public Function dadosnotaAction() {

    $iCodigoNota          = $this->getRequest()->getParam('id');
    $oDadosNota           = Contribuinte_Model_Nota::getByAttribute('id', $iCodigoNota);
    $oDadosNota           = $oDadosNota->getEntity();
    $oFormDadoNotaEmitida = new Contribuinte_Form_DadosNota($oDadosNota->getCod_verificacao());

    // RPS
    if ($oDadosNota->getN_rps()) {

      $aTiposDocumento      = Contribuinte_Model_Nota::getTiposNota(Contribuinte_Model_Nota::GRUPO_NOTA_RPS);
      $this->view->rps      = TRUE;
      $this->view->sTipoRps = $aTiposDocumento[$oDadosNota->getTipo_nota()];

      $oFormDadoNotaEmitida->nova->setAttrib('url', $this->view->baseUrl('/contribuinte/rps/index'));
    }

    $this->view->oForm        = $oFormDadoNotaEmitida;
    $this->view->oDadosNota   = $oDadosNota;
    $this->view->notaImpressa = self::getNotaImpressao($oDadosNota->getCod_verificacao(), FALSE, FALSE);
  }

  /**
   * Busca os dados do servico [Json]
   */
  public function getServicoAction() {

    $iIdServico = $this->getParam('servico');

    try {

      $aServicos = Contribuinte_Model_Servico::getByIm($this->_session->contribuinte->getInscricaoMunicipal(), FALSE);
      $aDados    = array();

      foreach ($aServicos as $oServico) {

        if ($oServico->attr('cod_atividade') == $iIdServico) {

          $aDados = array(
            'item_servico'          => $oServico->attr('desc_item_servico'),
            'cod_item_servico'      => $oServico->attr('cod_item_servico'),
            'estrut_cnae'           => $oServico->attr('estrut_cnae'),
            'desc_cnae'             => $oServico->attr('desc_cnae'),
            'deducao'               => $oServico->attr('deducao'),
            'aliq'                  => $oServico->attr('aliq'),
            'tributacao_municipio'  => $oServico->attr('tributacao_municipio')
          );
          break;
        }
      }

      echo $this->getHelper('json')->sendJson($aDados);
    } catch (Exception $oError) {

      $aRetorno['erro'] = TRUE;
      if ($oError->getCode() == Global_Lib_Model_WebService::CODIGO_ERRO_CONSULTA_WEBSERVICE) {

        $aRetorno['mensagem'] = "E-cidade temporariamente insdisponível. Emissão bloqueada!";
        $aRetorno['servico']  = $iIdServico;
      } else {
        $aRetorno['mensagem'] = $oError->getMessage();
      }

      echo $this->getHelper('json')->sendJson($aRetorno);
    }
  }

  /**
   * Detalhes da NFSE
   */
  public function notaImpressaAction() {

    parent::noLayout();

    $sCodigoVerificacao = $this->getRequest()->getParam('codigo_verificacao');
    $lImprimir          = $this->getRequest()->getParam('print', FALSE);

    if ($lImprimir) {
      echo $this->getNotaImpressao($sCodigoVerificacao, FALSE);
    } else {
      $this->getNotaImpressao($sCodigoVerificacao, TRUE);
    }
  }

  /**
   * Lista das NFSE
   */
  public function consultaAction() {

    $oFormConsulta = new Contribuinte_Form_NotaConsulta();
    $oFormConsulta->populate($this->getRequest()->getParams());

    $this->view->oFormConsulta = $oFormConsulta;
  }

  /**
   * Processa a pesquisa e retorna o html com o resultado da consulta [Ajax]
   */
  public function consultaProcessarAction() {

    parent::noTemplate();

    $oFormConsulta = new Contribuinte_Form_NotaConsulta();
    $oFormConsulta->populate($this->getRequest()->getParams());

    $sCodigosContribuintes = NULL;
    $oContribuinte         = $this->_session->contribuinte;
    $aParametrosBusca      = $oFormConsulta->getValues();
    $oPaginatorAdapter     = new DBSeller_Controller_Paginator(Contribuinte_Model_Nota::getQuery(),
                                                               'Contribuinte_Model_Nota',
                                                               'Contribuinte\Nota');

    foreach ($oContribuinte->getContribuintes() as $iIdContribuinte) {

      if ($sCodigosContribuintes == NULL) {
        $sCodigosContribuintes .= $iIdContribuinte;
      } else {
        $sCodigosContribuintes .= ',' . $iIdContribuinte;
      }
    }

    $oPaginatorAdapter->where("e.id_contribuinte in ({$sCodigosContribuintes}) ");

    if (!empty($aParametrosBusca['numero_nota'])) {
      $oPaginatorAdapter->andWhere("e.nota = '{$aParametrosBusca['numero_nota']}' ");
    }

    if (!empty($aParametrosBusca['retido'])) {
      $oPaginatorAdapter->andWhere('e.s_dados_iss_retido = 2 ');
    }

    if (!empty($aParametrosBusca['cpfcnpj'])) {
      $oPaginatorAdapter->andWhere("e.t_cnpjcpf = '{$aParametrosBusca['cpfcnpj']}'");
    }

    if (!empty($aParametrosBusca['im'])) {
      $oPaginatorAdapter->andWhere("e.im = '{$aParametrosBusca['im']}'");
    }

    if (!empty($aParametrosBusca['data_emissao_inicial']) && !empty($aParametrosBusca['data_emissao_final'])) {

      $oPaginatorAdapter->andWhere(
                        "e.dt_nota BETWEEN '{$aParametrosBusca['data_emissao_inicial']}' AND
                        '{$aParametrosBusca['data_emissao_final']}'"
      );
    } else {

      if (!empty($aParametrosBusca['data_emissao_inicial'])) {
        $oPaginatorAdapter->andWhere("e.dt_nota >= '{$aParametrosBusca['data_emissao_inicial']}' ");
      } else if (!empty($aParametrosBusca['data_emissao_final'])) {
        $oPaginatorAdapter->andWhere("e.dt_nota <= '{$aParametrosBusca['data_emissao_final']}' ");
      }
    }

    if ($aParametrosBusca['ordenacao_campo'] == 'competencia') {

      $oPaginatorAdapter->orderBy('e.ano_comp', $aParametrosBusca['ordenacao_direcao']);
      $oPaginatorAdapter->orderBy('e.mes_comp', $aParametrosBusca['ordenacao_direcao']);
    } else {
      $oPaginatorAdapter->orderBy("e.{$aParametrosBusca['ordenacao_campo']}", $aParametrosBusca['ordenacao_direcao']);
    }

    $oResultado = new Zend_Paginator($oPaginatorAdapter);
    $oResultado->setItemCountPerPage(10);
    $oResultado->setCurrentPageNumber($this->_request->getParam('page'));

    $this->view->oFormConsulta = $oFormConsulta;
    $this->view->notas         = $oResultado;

    if (is_array($aParametrosBusca)) {

      foreach ($aParametrosBusca as $sParametro => $sParametroValor) {

        if ($sParametroValor) {

          $sParametroValor = str_replace('/', '-', $sParametroValor);
          $this->view->sBusca .= "{$sParametro}/{$sParametroValor}/";
        }
      }
    }
  }

  /**
   * Cancela NFSE
   */
  public function cancelarAction() {

    parent::noTemplate();

    $aDados          = $this->getRequest()->getParams();
    $oNota           = Contribuinte_Model_Nota::getById($aDados['id']);
    $oContribuinte   = $this->_session->contribuinte;

    $oValidadorEmail = new Zend_Validate_EmailAddress();
    $oForm           = new Contribuinte_Form_NotaCancelar();
    $oForm->getElement('id')->setValue($aDados['id']);

    if ($oNota instanceof Contribuinte_Model_Nota && $oValidadorEmail->isValid($oNota->getT_email())) {
      $oForm->getElement('email')->setValue($oNota->getT_email());
    }

    $this->view->nota = $aDados['numero'];
    $this->view->form = $oForm;
  }

  /**
   * Execucao do cancelamento de NFSE via ajax
   */
  public function cancelarPostAction() {

    $aDados = $this->getRequest()->getParams();
    $oForm  = new Contribuinte_Form_NotaCancelar();
    $oForm->populate($aDados);

    if (!$oForm->isValid($aDados)) {

      $aRetornoJson['status']  = FALSE;
      $aRetornoJson['fields']  = array_keys($oForm->getMessages());
      $aRetornoJson['error'][] = 'Preencha os dados corretamente.';
    } else {

      try {

        $oContribuinte     = $this->_session->contribuinte;
        $oPrefeitura       = Administrativo_Model_Prefeitura::getDadosPrefeituraBase();
        $aLoginUsuario     = Zend_Auth::getInstance()->getIdentity();
        $oUsuario          = Administrativo_Model_Usuario::getByAttribute('login', $aLoginUsuario['login']);
        $oDataCancelamento = new DateTime();
        $oNota             = Contribuinte_Model_Nota::getById($aDados['id']);

        if ($oNota->podeCancelar($oContribuinte)) {

          //Retorna os usuarios do tipo fiscal
          $aUsuariosFiscal   = Administrativo_Model_Usuario::getByAttribute('tipo',
                                                                       Administrativo_Model_Usuario::USUARIO_TIPO_FISCAL);

          //Remove o usuário admin do array
          if ($aUsuariosFiscal[0]->getAdministrativo()) {
            unset($aUsuariosFiscal[0]);
          }

          $aEmailBCC = array();

          //Pega os emails cadastrados dos usuarios fiscais
          foreach ($aUsuariosFiscal as $oUsuarioFiscal) {

            $sEmail = $oUsuarioFiscal->getEmail();

            if (!is_null($sEmail) && $sEmail != '') {
              $aEmailBCC[] = $sEmail;
            }
          }

          if ($oPrefeitura->getSolicitaCancelamento()) {

            $oSolicitacaoCancelamento = new Contribuinte_Model_SolicitacaoCancelamento();
            $oSolicitacaoCancelamento->solicitar($oNota->getEntity(), $aDados, $oUsuario->getEntity());

            $sJustificativa   = $oSolicitacaoCancelamento->getJustificativa();
            $sRetornoEmail    = $this->enviarEmailCancelamento($oNota, $aDados, $aEmailBCC, TRUE, $sJustificativa);
            $sMensagemRetorno = "Cancelamento solicitado com sucesso!";
          } else {

            $oCancelamentoNota = new Contribuinte_Model_CancelamentoNota();
            $oCancelamentoNota->setUsuarioCancelamento($oUsuario);
            $oCancelamentoNota->setNotaCancelada($oNota);
            $oCancelamentoNota->setJustificativa($aDados['cancelamento_justificativa']);
            $oCancelamentoNota->setMotivoCancelmento($aDados['cancelamento_motivo']);
            $oCancelamentoNota->setDataHora($oDataCancelamento);
            $oCancelamentoNota->salvar();

            $sRetornoEmail    = $this->enviarEmailCancelamento($oNota, $aDados, $aEmailBCC, FALSE);
            $sMensagemRetorno = "Cancelamento efetuado com sucesso!";
          }

          $sMensagemRetorno        = (is_null($sRetornoEmail)) ? $sMensagemRetorno : $sRetornoEmail;
          $aRetornoJson['status']  = TRUE;
          $aRetornoJson['success'] = $sMensagemRetorno;
        } else {
          throw new Exception("Esta nota não pode mais ser cancelada!");
        }
      } catch (Exception $oErro) {

        $aRetornoJson['status']  = FALSE;
        $aRetornoJson['error'][] = $oErro->getMessage();
      }
    }

    echo $this->getHelper('json')->sendJson($aRetornoJson);
  }

  /**
   * Método responsável pelo envio de email no cancelamento ou na solicitação do mesmo
   * @param  Contribuinte_Model_Nota $oNota
   * @param  array                   $aDados
   * @param  array                   $aEmailBCC
   * @param  boolean                 $lSolicitacao
   * @param  string                  $sJustificativa
   * @return string|null
   * @throws Exception
   */
  private function enviarEmailCancelamento($oNota, $aDados, $aEmailBCC, $lSolicitacao = FALSE, $sJustificativa = null) {

    try {

      $oValidadorEmail  = new Zend_Validate_EmailAddress();
      $emailTO          = $oNota->getT_email();
      $sMensagemRetorno = NULL;

      if ($oValidadorEmail->isValid($emailTO)
        || (!empty($aDados['email']) && $oValidadorEmail->isValid($aDados['email']))
        || (count($aEmailBCC) > 0)) {


        $iInscricaoMunicipal = $oNota->getP_im();
        $oContribuinte       = Contribuinte_Model_Contribuinte::getByInscricaoMunicipal($iInscricaoMunicipal);

        $this->view->justificativa = $sJustificativa;
        $this->view->solicitacao   = $lSolicitacao;
        $this->view->nota          = $oNota;
        $this->view->tomadorNome   = $oNota->getT_razao_social();
        $this->view->prestadorNome = $oContribuinte->getNome();
        $this->view->prestadorCnpj = DBSeller_Helper_Number_Format::maskCPF_CNPJ($oContribuinte->getCgcCpf());
        $this->view->nfseNumero    = $oNota->getNota();
        $this->view->nfseUrl       = $oNota->getUrlVerificacaoNota();
        $this->mensagem            = $this->view->render('nota/email-emissao.phtml');

        $aArquivoPdfNfse = $this->getNotaImpressao($oNota->getCod_verificacao(), TRUE, TRUE);

        // Verifica se foi mudado o e-mail do Tomador para enviar uma cópia oculta do cancelamento
        if (!empty($aDados['email'])
          && $aDados['email'] != $oNota->getT_email()
          && $oValidadorEmail->isValid($aDados['email'])) {

          $emailTO  = $aDados['email'];

          if ($oValidadorEmail->isValid($oNota->getT_email())) {
            $aEmailBCC[] = $oNota->getT_email();
          }

          $sMensagemRetorno = "Cancelamento efetuado com sucesso.<br>Email foi enviado para {$emailTO}";
        }

        /*Caso não haja email cadastrado na nota e nem email informado no cancelamento,
          ou se for uma solicitação de cancelamento, o primeiro email de fiscal é colocado
          como destinatário principal para que seja possível o envio*/
        if (is_null($emailTO) || empty($emailTO) || $lSolicitacao) {
          $emailTO = $aEmailBCC[0];
          unset($aEmailBCC[0]);

          if ($lSolicitacao) {
            $sMensagemRetorno = "Cancelamento solicitado com sucesso.<br>Email foi enviado para {$emailTO}";
          }
        }

        // Envia Email
        DBSeller_Helper_Mail_Mail::sendAttachment($emailTO,
                                                  "Nota Fiscal Eletrônica nº {$oNota->getNota()}",
                                                  $this->mensagem,
                                                  $aArquivoPdfNfse,
                                                  $aEmailBCC);

        // Apaga o arquivo temporario gerado para envio do email
        unlink($aArquivoPdfNfse['location']);

        return $sMensagemRetorno;
      }
    } catch (Exception $oError) {
      throw $oError;
    }
  }

  /**
   * Gera o PDF da NFSE
   *
   * @param string  $sCodigoVerificacao
   * @param boolean $lPdf
   * @param boolean $lEmail
   * @return string
   */
  private function getNotaImpressao($sCodigoVerificacao, $lPdf = TRUE, $lEmail = FALSE) {

    // Flag para retirar as tags body e css
    if (!$lPdf) {
      $this->view->lHtmlEmbutido = TRUE;
    }

    $oNota                  = Contribuinte_Model_Nota::getByAttribute('cod_verificacao', $sCodigoVerificacao);
    $oPrefeitura            = Administrativo_Model_Prefeitura::getDadosPrefeituraBase();
    $this->view->aDadosNota = Contribuinte_Model_Nota::getDadosEmissao($sCodigoVerificacao, $oNota, $oPrefeitura);

    $sHtml         = "pdf/nota_modelo_{$oPrefeitura->getModeloImpressaoNfse()}.phtml";
    $sHtml         = $this->view->render($sHtml);
    $sNomeArquivo  = "nfse_{$oNota->getNota()}";
    $sLocalArquivo = APPLICATION_PATH . "/../public/tmp/{$sNomeArquivo}";

    // Verifica se gera o PDF ou retorna apenas o HTML
    if ($lPdf) {

      // Verifica se deve retornar os parametros do documento para envio por email
      if ($lEmail) {

        DBSeller_Helper_Pdf_Pdf::renderPdf($sHtml,
                                           $sLocalArquivo,
                                           array('format' => 'A4', 'output' => 'F'));

        return array(
          'location' => "{$sLocalArquivo}.pdf",
          'filename' => "{$sNomeArquivo}.pdf",
          'type'     => 'application/pdf'
        );
      } else {
        return DBSeller_Helper_Pdf_Pdf::renderPdf($sHtml, $sNomeArquivo, array('format' => 'A4', 'output' => 'D'));
      }
    } else {
      return $sHtml;
    }
  }

  /**
   * Formulário NFSE
   *
   * @param string $sCodigoVerificacao
   * @param string $oContribuinte
   * @return Contribuinte_Form_Nota
   */
  private function formNota($sCodigoVerificacao, $oContribuinte = NULL) {

    $oContribuinte = $oContribuinte ? : $this->_session->contribuinte;
    $iMaxNota      = 0;
    $iMaxGuia      = 0;

    // Calcula quantos dias no passado a nota pode ser emitida
    $oParametrosPrefeitura       = Administrativo_Model_Prefeitura::getDadosPrefeituraBase();
    $iDiasRetroativosEmissaoNota = $oParametrosPrefeitura->getNotaRetroativa();
    $oDataCorrente               = new DateTime();
    $oUltimaGuia                 = Contribuinte_Model_Guia::getUltimaGuiaNota($oContribuinte);
    $uDataUltimaNota             = Contribuinte_Model_Nota::getUltimaNotaEmitidaByContribuinte(
                                                          $oContribuinte->getContribuintes());

    if ($uDataUltimaNota != NULL) {
      $oDiff    = $oDataCorrente->diff(new DateTime($uDataUltimaNota), TRUE);
      $iMaxNota = ($oDiff->days < $iDiasRetroativosEmissaoNota) ? $oDiff->days : $iDiasRetroativosEmissaoNota;
    }

    if (!empty($oUltimaGuia)) {

      $iMes = $oUltimaGuia->getMesComp();
      $iAno = $oUltimaGuia->getAnoComp();

      if (($oUltimaGuia->getMesComp() + 1) > 12) {
        $iMes = 1;
      }

      $iMes = str_pad($iMes, 2, '0', STR_PAD_LEFT);

      $uDataUltimoDiaCompetencia = new Zend_Date("01/{$iMes}/{$iAno}");

      $uDataUltimoDiaCompetencia->sub(-1, Zend_date::MONTH);

      $oDiff    = $oDataCorrente->diff(new DateTime($uDataUltimoDiaCompetencia->get('Y-M-d')), TRUE);
      $iMaxGuia = ($oDiff->days < $iDiasRetroativosEmissaoNota) ? $oDiff->days : $iDiasRetroativosEmissaoNota;
    }

    if ($iMaxNota > $iMaxGuia && $iMaxGuia > 0) {
      $iDiasRetroativosEmissaoNota = $iMaxGuia;
    } else if ($iMaxNota > 0) {
      $iDiasRetroativosEmissaoNota = $iMaxNota;
    } else if (!$iDiasRetroativosEmissaoNota || $iMaxNota == 0) {
      $iDiasRetroativosEmissaoNota = 0;
    }

    $oDataCorrente = new DateTime();
    $oDataCorrente = $oDataCorrente->sub(date_interval_create_from_date_string("{$iDiasRetroativosEmissaoNota} days"));
    $oForm         = new Contribuinte_Form_Nota($sCodigoVerificacao, $oDataCorrente);

    if ($oContribuinte !== NULL) {

      $oParametros = Contribuinte_Model_ParametroContribuinte::getById($oContribuinte->getIdUsuarioContribuinte());

      if ($oParametros instanceof Contribuinte_Model_ParametroContribuinte) {
        $oForm->preencheParametros($oParametros);
      }
    }

    return $oForm;
  }

  /**
   * Verifica se o contribuinte é optante pelo simples na data especificada
   * Utiliza o mesmo método utilizado no DMS
   *
   * @see Contribuinte_Lib_Controller_AbstractController::verificarContribuinteOptanteSimplesAction()
   * @throws Exception
   */
  public function verificarContribuinteOptanteSimplesAction() {

    try {

      $sData = $this->getRequest()->getParam('data');

      if (!$sData) {
        throw new Exception('Informe a data para verificar.');
      }

      $oDataSimples = new DateTime(DBSeller_Helper_Date_Date::invertDate($sData, '-'));

      if (!$oDataSimples instanceof DateTime) {
        throw new Exception('Data inválida');
      }

      $oContribuinte = $this->_session->contribuinte;
      $oParametros   = Contribuinte_Model_ParametroContribuinte::getById($oContribuinte->getIdUsuarioContribuinte());

      $aRetorno['optante_simples_nacional']  = $oContribuinte->isOptanteSimples($oDataSimples) ? TRUE : FALSE;
      $aRetorno['optante_simples_categoria'] = $oContribuinte->getOptanteSimplesCategoria();

      if ($oParametros instanceof Contribuinte_Model_ParametroContribuinte) {

        $fAliquota = $oParametros->getEntity()->getValorIssFixo();
        $aRetorno['valor_iss_fixo'] = DBSeller_Helper_Number_Format::toMoney($fAliquota);
      }

      echo $this->getHelper('json')->sendJson($aRetorno);
    } catch (Exception $oError) {

      $aRetorno['erro'] = TRUE;
      $aRetorno['mensagem'] = $oError->getMessage();

      echo $this->getHelper('json')->sendJson($aRetorno);
    }
  }

  /**
   * Verifica se esta na regra do parametro da prfeitura para reter tomador pessoa fisica na emissão de nota
   * @param  array   $aDados array de dados do formulário
   * @return boolean true|false
   */
  private function podeReterPessoaFisica(array $aDados) {

    if ($aDados['s_dados_iss_retido'] == 0) {
      return TRUE;
    }

    if (strlen($aDados['t_cnpjcpf']) > 14) {
      return TRUE;
    }

    $oParametrosPrefeitura = Administrativo_Model_ParametroPrefeitura::getAll();
    if ($aDados['natureza_operacao'] == 1 && $oParametrosPrefeitura[0]->getReterPessoaFisica() == 1) {
      return TRUE;
    }

    return FALSE;
  }
}