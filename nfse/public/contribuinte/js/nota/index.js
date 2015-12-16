/**
 * Executa ao carregar a pagina
 */
$(function() {

  var $oForm                      = $('#nota');
  var $oEmitir                    = $('#emitir');
  var $oNaturezaOperacao          = $('#natureza_operacao');
  var $oTomadorPais               = $('#t_cod_pais');
  var $oNotaSubstituta            = $('#nota_substituta');
  var $oNotaSubstituida           = $('#nota_substituida');
  var $oTomadorBuscador           = $('#buscador');
  var $oTomadorCpfCnpj            = $('#t_cnpjcpf');
  var $oTomadorNomeRazaoSocial    = $('#t_razao_social');
  var $oTomadorCodigoUf           = $('#t_uf');
  var $oTomadorCodigoMunicipio    = $('#t_cod_municipio');
  var $oTomadorEndereco           = $('#t_endereco');
  var $oTomadorEmail              = $('#t_email');
  var $oServicoIssRetido          = $('#s_dados_iss_retido');
  var $oServicoCodigoTributacao   = $('#s_dados_cod_tributacao');
  var $oServicoValorAliquota      = $('#s_vl_aliquota');
  var $oElementosEvitarEnter      = $('form input, form select');
  var $oElmentosSelectReadonly    = $('select[readonly] option:not(:selected)');
  var $oElementosLegend           = $('legend');
  var $oElementosCalculoTotal     = $('#natureza_operacao, #s_dados_iss_retido, #s_vl_servicos, #s_vl_deducoes, #s_vl_desc_incondicionado');
  var $oElementosCalculoParcial   = $('#s_vl_aliquota, #s_vl_bc, #s_vl_iss, #s_vl_inss, #s_vl_bc, #s_vl_pis, #s_vl_cofins, #s_vl_ir, #s_vl_csll, #s_vl_condicionado, #s_vl_outras_retencoes');
  var $oElementosNovoImprimirNota = $('#nova, #imprimir');
  var $oDataNota                  = $('#dt_nota');
  var $oValorDeducoes             = $('#s_vl_deducoes');
  var $oTributacaoMunicipio       = $('#s_tributacao_municipio');
  var $oReterPessoaFisica         = $('#reter_pessoa_fisica');
  var $oTomadorCep                = $('#t_cep');
  var $oTomadorIM                 = $('#t_im');
  var $oTomadorIE                 = $('#t_ie');

  /**
   * Método para liberar ou bloquear a opção de retido
   *
   * @param object data
   */
  var liberarServicoIssRetido = function(data) {

    if (data) {

      // Se a tributação for dpara o município bloqueia o retifdo
      if (typeof data.tributacao_municipio != 'undefined'
        && data.tributacao_municipio == 't'
        && $oNaturezaOperacao.val() == 2) {

        $oServicoIssRetido.attr('readonly', true).attr('disabled', true).attr('checked', false);
      } else if (typeof data.substituto_tributario != 'undefined' && data.substituto_tributario) {

        // Se for um substituto tributário esconde o checkbox
        $oServicoIssRetido.attr('readonly', true).attr('disabled', true).attr('checked', true);
        $oServicoIssRetido.parent().parent().hide();
      } else {

        $oServicoIssRetido.attr('readonly', false).attr('disabled', false).attr('checked', false);
        $oServicoIssRetido.parent().parent().show();

        // Verifica se é um CPF e se o parametro está liberado
        if (data.isCpf) {

          if ($oReterPessoaFisica.val() == '1' && $oNaturezaOperacao.val() == '1') {
            $oServicoIssRetido.attr('readonly', false).attr('disabled', false).attr('checked', false);
          } else {
            $oServicoIssRetido.attr('readonly', true).attr('disabled', true).attr('checked', false);
          }
        }
      }
    }
  }

  /**
   * Funcoes Auxiliares
   */
  var limpaDadosTomador = function() {

    $('#t_razao_social').val('');
    $('#t_nome_fantasia').val('');
    $('#t_im').val('');
    $('#t_ie').val('');
    $('#t_cod_pais').val('01058'); //valor padrão 01058 Brasil
    $('#t_uf').val(0).change();
    $('#t_cep').val('');
    $('#t_endereco').val('');
    $('#t_endereco_numero').val('');
    $('#t_endereco_comp').val('');
    $('#t_bairro').val('');
    $('#t_telefone').val('');
    $('#t_email').val('');

    $oServicoIssRetido.attr('readonly', true).attr('disabled', true).attr('checked', false);
  }

  /**
   * Função auxiliar para showCamposPessoaJuridica()
   *
   * @param str
   */
  var showCamposPessoaJuridicaAux = function(str) {

    var iTamanho = $().returnNumbers(str.toString());
    showCamposPessoaJuridica(iTamanho.length);
  }

  /**
   * Exibe os campos de Pessoa Juridica
   */
  var showCamposPessoaJuridica = function(iTamanho) {

    var $oServicoIssRetido        = $('#s_dados_iss_retido');
    var $oElementosPessoaJuridica = $('.pessoa_juridica');

    if (iTamanho <= 11) {
      $oElementosPessoaJuridica.closest('.control-group').hide();
    } else {
      $oElementosPessoaJuridica.closest('.control-group').show();
    }
  }

  /**
   * Define a lista de atividades
   *
   * @param string $sData
   * @param integer $iServicoCodigoTributacao
   */
  var defineListaAtividades = function($sData, $iServicoCodigoTributacao) {

    $.ajax({
      'url'    : '/contribuinte/nota/define-lista-atividades/',
      'data'   : { 'data_emissao': $sData },
      'error'  : function(xhr){
        $().bloqueiaEmissao(xhr, $oForm);
      },
      'success': function(data) {

        oListaAtividades                = document.getElementById('s_dados_cod_tributacao');
        oListaAtividades.options.length = 0;

        var opt       = new Option('', '', false, false);
        opt.innerHTML = '- Escolha um Serviço -';
        oListaAtividades.appendChild(opt);

        $.each(data, function(campo, valor) {

          var bSelected = false;
          if ($iServicoCodigoTributacao == campo) {

            // Ajusta o valor do serviço selecionado na copia da nota
            $('#s_dados_cod_tributacao_copia').val($iServicoCodigoTributacao);
            bSelected = true;
          }

          var opt       = new Option(null, campo, false, bSelected);
          opt.innerHTML = valor;
          oListaAtividades.appendChild(opt);
        });

        // Ajusta o valor do serviço selecionado na copia da nota
        if ($('#s_dados_cod_tributacao_copia').val() != '') {
          $('#s_dados_cod_tributacao').val($('#s_dados_cod_tributacao_copia').val());
        }
      }
    });
  }

  /**
   * Calcula os valores do serviço do documento
   *
   * @param lCarregarParametrosContribuinte [boolean]
   */
  var calculaImpostos = function(lCarregarParametrosContribuinte) {

    var $oValorServicos   = $('#s_vl_servicos');
    var $oAliquota        = $('#s_vl_aliquota');
    var $oDeducoes        = $('#s_vl_deducoes');
    var $oInss            = $('#s_vl_inss');
    var $oPis             = $('#s_vl_pis');
    var $oCofins          = $('#s_vl_cofins');
    var $oIr              = $('#s_vl_ir');
    var $oCsll            = $('#s_vl_csll');
    var $oCondicionado    = $('#s_vl_condicionado');
    var $oIncondicionado  = $('#s_vl_desc_incondicionado');
    var $oOutrasRetencoes = $('#s_vl_outras_retencoes');
    var $oBaseCalculo     = $('#s_vl_bc');
    var $oIss             = $('#s_vl_iss');
    var $oValorLiquido    = $('#s_vl_liquido');

    // Flag para verificar se o prestador desconta o ISS na nota
    var lTomadorPagaIss = $('#s_dados_iss_retido').is(':checked') ? true : false;

    // Percentuais
    var perc_deducao  = ajustaValoresCalculo($oDeducoes.attr('perc'));
    var perc_inss     = ajustaValoresCalculo($oInss.attr('perc'));
    var perc_pis      = ajustaValoresCalculo($oPis.attr('perc'));
    var perc_cofins   = ajustaValoresCalculo($oCofins.attr('perc'));
    var perc_ir       = ajustaValoresCalculo($oIr.attr('perc'));
    var perc_csll     = ajustaValoresCalculo($oCsll.attr('perc'));
    var perc_aliquota = $oAliquota.val().replace(/\./g, '').replace(/,/g, '.');

    perc_aliquota = ajustaValoresCalculo(perc_aliquota);

    // Valores
    var vlr_servico             = parseFloat($oValorServicos.val().replace(/\./g, '').replace(/,/g, '.'));
    var vlr_desc_condicionado   = parseFloat($oCondicionado.val().replace(/\./g, '').replace(/,/g, '.'));
    var vlr_desc_incondicionado = parseFloat($oIncondicionado.val().replace(/\./g, '').replace(/,/g, '.'));
    var vlr_outras_retencoes    = parseFloat($oOutrasRetencoes.val().replace(/\./g, '').replace(/,/g, '.'));

    // Calculos
    var vlr_deducao = 0;
    var vlr_liquido = 0;

    if ($oDeducoes.attr('habilita_deducao') == 'true') {

      vlr_deducao = $oDeducoes.val().replace(/\./g, '').replace(/,/g, '.');
      vlr_deducao = parseFloat(vlr_deducao.replace(',', '.'));
    } else {
      vlr_deducao = parseFloat(ajustaValoresCalculo(vlr_servico) * (ajustaValoresCalculo(perc_deducao) / 100));
    }

    // Valida se o valor de dedução é menor que o valor do serviço
    if (vlr_deducao < 0 || vlr_deducao >= vlr_servico) {
      vlr_deducao = 0;
    }

    vlr_servico             = ajustaValoresCalculo(vlr_servico);
    vlr_deducao             = ajustaValoresCalculo(vlr_deducao);
    vlr_desc_incondicionado = ajustaValoresCalculo(vlr_desc_incondicionado);

    var vlr_base    = parseFloat(vlr_servico - vlr_deducao - vlr_desc_incondicionado);
    var vlr_iss     = parseFloat($oIss.val().replace(/\./g, '').replace(/,/g, '.'));
    var vlr_inss    = parseFloat($oInss.val().replace(/\./g, '').replace(/,/g, '.'));
    var vlr_pis     = parseFloat($oPis.val().replace(/\./g, '').replace(/,/g, '.'));
    var vlr_cofins  = parseFloat($oCofins.val().replace(/\./g, '').replace(/,/g, '.'));
    var vlr_ir      = parseFloat($oIr.val().replace(/\./g, '').replace(/,/g, '.'));
    var vlr_csll    = parseFloat($oCsll.val().replace(/\./g, '').replace(/,/g, '.'));

    if (perc_aliquota > 0) {
      vlr_iss = ajustaValoresCalculo(vlr_base) * (ajustaValoresCalculo(perc_aliquota) / 100);
    }

    // Considera os parâmetros do contribuinte para calcular os valores dos impostos
    if (lCarregarParametrosContribuinte) {

      if (perc_inss > 0) {
        vlr_inss = ajustaValoresCalculo(vlr_base) * (ajustaValoresCalculo(perc_inss) / 100);
      }

      if (perc_pis > 0) {
        vlr_pis = ajustaValoresCalculo(vlr_servico) * (ajustaValoresCalculo(perc_pis) / 100);
      }

      if (perc_cofins > 0) {
        vlr_cofins = ajustaValoresCalculo(vlr_servico) * (ajustaValoresCalculo(perc_cofins) / 100);
      }

      if (perc_ir > 0) {
        vlr_ir = ajustaValoresCalculo(vlr_servico) * (ajustaValoresCalculo(perc_ir) / 100);
      }

      if (perc_csll > 0) {
        vlr_csll = ajustaValoresCalculo(vlr_servico) * (ajustaValoresCalculo(perc_csll) / 100);
      }
    }

    // Round
    vlr_deducao             = vlr_deducao.toFixed(2);
    vlr_base                = vlr_base.toFixed(2);
    vlr_iss                 = vlr_iss.toFixed(2);
    vlr_inss                = vlr_inss.toFixed(2);
    vlr_pis                 = vlr_pis.toFixed(2);
    vlr_cofins              = vlr_cofins.toFixed(2);
    vlr_ir                  = vlr_ir.toFixed(2);
    vlr_csll                = vlr_csll.toFixed(2);
    vlr_liquido             = vlr_liquido.toFixed(2);
    vlr_desc_condicionado   = vlr_desc_condicionado.toFixed(2);
    vlr_desc_incondicionado = vlr_desc_incondicionado.toFixed(2);
    vlr_outras_retencoes    = vlr_outras_retencoes.toFixed(2);

    // Calcula o valor liquido
    if (vlr_servico > 0) {

      vlr_liquido = ajustaValoresCalculo(vlr_servico);
      vlr_liquido = vlr_liquido - ajustaValoresCalculo(vlr_pis);
      vlr_liquido = vlr_liquido - ajustaValoresCalculo(vlr_cofins);
      vlr_liquido = vlr_liquido - ajustaValoresCalculo(vlr_inss);
      vlr_liquido = vlr_liquido - ajustaValoresCalculo(vlr_ir);
      vlr_liquido = vlr_liquido - ajustaValoresCalculo(vlr_csll);
      vlr_liquido = vlr_liquido - ajustaValoresCalculo(vlr_outras_retencoes);
      vlr_liquido = vlr_liquido - ajustaValoresCalculo(vlr_desc_condicionado);
      vlr_liquido = vlr_liquido - ajustaValoresCalculo(vlr_desc_incondicionado);

      // Desconta o ISS do valor liquido se o tomador for o responsavel pelo ISS
      if (lTomadorPagaIss) {
        vlr_liquido -= vlr_iss;
      }
    }

    // Verifica se tem valores negativos (apos os calculos)
    if (vlr_liquido < 0) {
      vlr_liquido = 0;
    }

    // Formatação
    vlr_deducao             = $().number_format(vlr_deducao.toString(), 2, ',', '.');
    vlr_base                = $().number_format(vlr_base.toString(), 2, ',', '.');
    vlr_iss                 = $().number_format(vlr_iss.toString(), 2, ',', '.');
    vlr_inss                = $().number_format(vlr_inss.toString(), 2, ',', '.');
    vlr_pis                 = $().number_format(vlr_pis.toString(), 2, ',', '.');
    vlr_cofins              = $().number_format(vlr_cofins.toString(), 2, ',', '.');
    vlr_ir                  = $().number_format(vlr_ir.toString(), 2, ',', '.');
    vlr_csll                = $().number_format(vlr_csll.toString(), 2, ',', '.');
    vlr_liquido             = $().number_format(vlr_liquido.toString(), 2, ',', '.');
    vlr_desc_condicionado   = $().number_format(vlr_desc_condicionado.toString(), 2, ',', '.');
    vlr_desc_incondicionado = $().number_format(vlr_desc_incondicionado.toString(), 2, ',', '.');
    vlr_outras_retencoes    = $().number_format(vlr_outras_retencoes.toString(), 2, ',', '.');

    // Preenche os campos
    $oDeducoes.val(vlr_deducao);
    $oBaseCalculo.val(vlr_base);
    $oIss.val(vlr_iss);
    $oPis.val(vlr_pis);
    $oCofins.val(vlr_cofins);
    $oInss.val(vlr_inss);
    $oIr.val(vlr_ir);
    $oCsll.val(vlr_csll);
    $oOutrasRetencoes.val(vlr_outras_retencoes);
    $oCondicionado.val(vlr_desc_condicionado);
    $oIncondicionado.val(vlr_desc_incondicionado);
    $oValorLiquido.val(vlr_liquido);
  }

  /**
   * Método para ajustar os valores de calculo nulos, vazios e NaN
   *
   * @param integer $iValue
   * @returns integer
   */
  var ajustaValoresCalculo = function($iValue) {

    // Verifica se tem valores indefinidos
    $iValue = (isNaN($iValue)) ? 0 : $iValue;

    // Verifica se tem valores vazios
    $iValue = ($iValue == '') ? 0 : $iValue;

    // Verifica se tem valores negativos
    $iValue = ($iValue > 0) ? $iValue : 0;

    return $iValue;
  }

  /**
   * Calcula os valores dos serviços conforme os dados de cada serviço selecionado
   *
   * @param object $data
   */
  var calculaValoresServico = function($data) {

    if ($data) {

      // Altera a aliquota somente quando for campo de texto e não um select (optante simples) e natureza no município
      if (($data.aliq
       && $oServicoValorAliquota.is('input')
       && $oNaturezaOperacao.val() == '1' )
       || ($oServicoValorAliquota.is('input')
       && $data.tributacao_municipio == 't')) {

        /** @namespace data.aliq */
        var valor_aliquota = $().number_format($data.aliq, 2, ',', '.');
        $oServicoValorAliquota.val(valor_aliquota);
      }

      // Valida se o servico é de construção civil habilita a edição de dedução
      $oValorDeducoes.attr('readonly', true).attr('habilita_deducao', false);

      /** @namespace data.deducao */
      if ($data.deducao == 't') {
        $oValorDeducoes.attr('readonly', false).attr('habilita_deducao', true);
      }

      // Tributado dentro do municipio ou fora do municipio com a flag habilitada, bloqueia a aliquota
      if ($oNaturezaOperacao.val() == '1' || $data.tributacao_municipio == 't') {

        if ($oServicoValorAliquota.is('input')) {
          $oServicoValorAliquota.attr('readonly', true);
        }
      } else {
        $oServicoValorAliquota.attr('readonly', false);
      }

      // Recalcula valores
      calculaImpostos(true);
    }
  }

  /**
   * Consulta os dados do servço selecionado
   *
   * @param integer $iServico
   */
  var verificaServico = function($iServico) {

    if ($iServico) {

      $.ajax({
        'url'    : '/contribuinte/nota/get-servico/',
        'data'   : { 'servico' : $iServico},
        'error'  : function(xhr){
        	$().bloqueiaEmissao(xhr, $oForm);
        },
        'success': function(data) {

          if (data) {

          	if(data.erro){
          		$().bloqueiaEmissao(data, $oForm);
          	} else {
              var verificaImunidadeDom = document.querySelector("#veim").innerHTML;
              if(verificaImunidadeDom === "Imunidade"){
                data.aliq = 0;
              }
	            $oTributacaoMunicipio.val(data.tributacao_municipio);
              liberarServicoIssRetido(data);
              calculaValoresServico(data);

            }
          }
        }
      });
    }
  }

  // Bloqueia tela no submit do formulario
  $oEmitir.click(function() {
    $oForm.submit(function() {
      $oForm.find('input').attr('readonly', true);
      $oForm.find('select').attr('readonly', true);
      $oForm.find('textarea').attr('readonly', true);
      $oEmitir.html('Aguarde...').attr('disabled', true);
    });
  });

  // Previne submit ao pressionar 'Enter'
  $oElementosEvitarEnter.keypress(function(e) {
    if (e.which == 13) {
      e.preventDefault();
    }
  });

  // Abre/Fecha Legends
  $oElementosLegend.click(function() {

    $(this).find('~ *').each(function(i, div) {
      $(div).toggle();
    });
  });

  // Bloqueia a seleção de options readonly
  $oElmentosSelectReadonly.attr('disabled', true);

  // Exibe o campo para nota substituta
  $oNotaSubstituta.change(function() {

    if ($(this).attr('checked')) {

      $oNotaSubstituida.closest('.control-group').show();
      $oNotaSubstituida.show().focus();
    } else {

      $oNotaSubstituida.closest('.control-group').hide();
      $oNotaSubstituida.hide();
    }
  });

  $oTomadorPais.change(function() {
    if(this.value == '01058')
    {
      $oTomadorCpfCnpj.attr('readonly', false);
      $oTomadorCodigoUf.attr('readonly', false);
      $oTomadorCodigoMunicipio.attr('readonly', false);
      $oTomadorBuscador.attr('readonly', false);
      $oTomadorCep.attr('readonly', false);
      $oTomadorIM.attr('readonly', false);
      $oTomadorIE.attr('readonly', false);
      $oTomadorCodigoUf.attr('disabled', false);
    } else {
      $oTomadorCpfCnpj.attr('readonly', true);
      $oTomadorCodigoUf.attr('readonly', true);
      $oTomadorCodigoMunicipio.attr('readonly', true);
      $oTomadorBuscador.attr('readonly', true);
      $oTomadorCep.attr('readonly', true);
      $oTomadorIM.attr('readonly', true);
      $oTomadorIE.attr('readonly', true);
      $oTomadorCodigoUf.attr('disabled', true);
    }
  });

  // Busca Tomador
  $oTomadorCpfCnpj.buscador({

    statusInput: 'input#t_razao_social',
    url        : $oTomadorCpfCnpj.attr('url'),
    data       : { 'substituto': $oServicoIssRetido },
    success    : function(data) {

      limpaDadosTomador();

      if (data) {

        if (!data.uf) {
          data = data[0];
        }

        if (!!data.uf) {

          $oTomadorCodigoUf.val(data.uf);
          $oTomadorCodigoUf.change();
        }

        /** @namespace data.cod_ibge */
        if (!!data.cod_ibge) {

          $oTomadorCodigoMunicipio.val(data.cod_ibge);
          $oTomadorCodigoMunicipio.change();
        }

        /** @namespace data.endereco */
        if (data.endereco) {
          $oTomadorEndereco.val(data.endereco);
        }

        if (data.cpf) {
          showCamposPessoaJuridicaAux(data.cpf);
        }

        verificaServico($oServicoCodigoTributacao.val());
        liberarServicoIssRetido(data);
      }

      $.each(data, function(campo, valor) {

        var $oCampo = $('[campo-ref=' + campo + ']');
        var id1     = $oCampo.attr('id');
        var id2     = $oTomadorCpfCnpj.attr('id');

        if (valor != null && id1 != id2) {
          $oCampo.val(valor);
        }
      });

      // Reprocessa as máscaras no campos
      $().addMascarasCampos();

      // Posiciona o foco no campo de email
      $oTomadorEmail.focus();
    },
    not_found  : function() {

      limpaDadosTomador();
      showCamposPessoaJuridicaAux($oTomadorCpfCnpj.attr('value'));
    }
  });

  // Serviço retido
  $oServicoIssRetido.change(function(e) {

    var sCnpjTomador = $().returnNumbers($oTomadorCpfCnpj.val());

    if ($(this).attr('checked') === true && $oTomadorCpfCnpj.val() != '') {

      if ($oTomadorNomeRazaoSocial.val() == '') {
        $oTomadorBuscador.click();
      }
    }
  });

  // Calculos
  $oElementosCalculoTotal.live('change', function() {
    calculaImpostos(true);
  });

  $oElementosCalculoParcial.live('change', function() {
    calculaImpostos(false);
  });

  // Botoes da nota emitida
  $oElementosNovoImprimirNota.click(function() {
    window.location.href = $(this).attr('url');
  });

  // Alteracao do Servico
  $oServicoCodigoTributacao.change(function() {
    verificaServico($(this).val());
  });

  // Alteracao Natureza Operacao
  $oNaturezaOperacao.change(function() {

    var $oFilds = $('#fieldset-tomador, #fieldset-grp_servico, #fieldset-valores');

    $oFilds.find('input').attr('disabled', false);
    $oFilds.find('select').attr('disabled', false);
    $oFilds.find('textarea').attr('disabled', false);
    $oFilds.find('button').attr('disabled', false);
    $oEmitir.attr('disabled', false);

    // Tributado dentro do municipio bloqueia a aliquota
    if ($(this).val() == '1') {

      if ($oServicoValorAliquota.is('input')) {
        $oServicoValorAliquota.attr('readonly', true);
      }
    } else if ($(this).val() == '2') {
      $oServicoValorAliquota.attr('readonly', false);
    } else {

      $oFilds.find('input').attr('disabled', true);
      $oFilds.find('select').attr('disabled', true);
      $oFilds.find('textarea').attr('disabled', true);
      $oFilds.find('button').attr('disabled', true);
      $oEmitir.attr('disabled', true);
    }

    limpaDadosTomador();
    verificaServico($oServicoCodigoTributacao.val());
  });

  // Verifica se o contribuinte é optante pelo simples e habilita o campo com alíquotas do simples nacional
  $oDataNota.change(function() {

    var sValorAliquota = $oServicoValorAliquota.val();
    var sDataNota      = $(this).val();

    // Campo padrão (preenchido conforme serviço)
    $oServicoValorAliquota.closest('div').html(
      '<input type="text" name="s_vl_aliquota" id="s_vl_aliquota" value="" class="span1 mask-porcentagem" ' +
        'readonly="readonly" style="text-align:right">' +
        '<span class="add-on">%</span></div>'
    );

    $oServicoValorAliquota = $('#s_vl_aliquota');
    $oServicoValorAliquota.val(sValorAliquota);

    // Se informada a data, verifica via webservice se o contribuinte é optante pelo simples
    if (sDataNota.length == 10) {

      $.ajax({
        dataType : 'json',
        url      : $oDataNota.attr('data-url'),
        data     : { 'data': sDataNota },
        error   : function(xhr){
        	$().bloqueiaEmissao(xhr, $oForm);
        },
        success  : function(data) {

        	if (data.erro) {
        		$().bloqueiaEmissao(data, $oForm);
        	} else {

	        	sValorAliquota = data.valor_iss_fixo;

	          // Mostra select com opções de aliquota
	          if (data.optante_simples_nacional) {

	            var sCategorias = '<select name="s_vl_aliquota" id="s_vl_aliquota" class="input-small">';

	            /**
	             * Optante pelo simples - mei
	             */
	            if (data.optante_simples_categoria == 3) {
	            	sValorAliquota  = "";
	              sCategorias    += '<option value="0,00">0</option>';
	            }

	            sCategorias    += '<option value="2,00">2</option>';
	            sCategorias    += '<option value="2,79">2,79</option>';
	            sCategorias    += '<option value="3,50">3,5</option>';
	            sCategorias    += '<option value="3,84">3,84</option>';
	            sCategorias    += '<option value="3,87">3,87</option>';
	            sCategorias    += '<option value="4,23">4,23</option>';
	            sCategorias    += '<option value="4,26">4,26</option>';
	            sCategorias    += '<option value="4,31">4,31</option>';
	            sCategorias    += '<option value="4,61">4,61</option>';
	            sCategorias    += '<option value="4,65">4,65</option>';
	            sCategorias    += '<option value="5,00">5</option>';
	            sCategorias    += '</select>';
	            sCategorias    += '<span class="add-on">%</span>';

	            $oServicoValorAliquota.closest('div').html(sCategorias);
	            $oServicoValorAliquota = $('#s_vl_aliquota');
	            $oNaturezaOperacao.change();
	          }

	          $oServicoValorAliquota.val(sValorAliquota);
        	}
        }
      });

      defineListaAtividades(sDataNota, $oServicoCodigoTributacao.val());
    }

    // Recalcula os valores
    $oServicoCodigoTributacao.change();
    $oNaturezaOperacao.change();

    // Readiciona as máscaras no campo
    $().addMascarasCampos();
  });

  $oNotaSubstituta.change();
  $oDataNota.change();
});