$(document).ready(function(){

  var $oServicoValorAliquota = $('#valor_iss_fixo');

  $("form[name=busca] input#im").buscador({
    statusInput: "span#status",
    url: "/contribuinte/parametro/busca-contribuinte/",
    success: function(data) {

      $("span#status").text("");
      $("input[name=im]").val(data.dados.inscricao);
      $("input#nome_contribuinte").val(data.dados.nome);

      $.each(data.parametros,function(i,v) {
        var elmnt = $("#" + i);
        if(elmnt.attr('type') == 'checkbox') {
          elmnt.attr('checked',v);
        }else {
          elmnt.val(v);
        }
      });
    }
  });

  // Verifica se o contribuinte é optante pelo simples e habilita o campo com alíquotas do simples nacional
  var sValorAliquota = $oServicoValorAliquota.val();
  var sData      = new Date().toLocaleFormat('%Y-%m-%d')

    $.ajax({
      dataType : 'json',
      url      :  '/contribuinte/nota/verificar-contribuinte-optante-simples',
      data     : { 'data': sData },
      error   : function(xhr){
        $().bloqueiaEmissao(xhr, $oForm);
      },
      success  : function(data) {

        if (data.erro) {
          $().bloqueiaEmissao(data, $oForm);
        } else {

          // Mostra select com opções de aliquota
          if (data.optante_simples_nacional) {

            var sCategorias = '<select name="valor_iss_fixo" id="valor_iss_fixo" class="input-small">';

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
          }

          $oServicoValorAliquota = $('#valor_iss_fixo');
          $oServicoValorAliquota.val(sValorAliquota);
          $oServicoValorAliquota.change();
        }
      }
    });
});