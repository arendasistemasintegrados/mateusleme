;(function($) {

  $(function($) {
    var sBaseUrl = $('#base').attr('href');

    $(document).ajaxSend(function() {
      $('#ajax-loader').show()
    })

    $(document).ajaxStop(function() {
      $('#ajax-loader').hide()
    })

    $('#FiltroIndexForm').live('submit', function() {
      var lPass = true;

      // Verifica se os campos obrigatórios foram preenchidos
      $('select.required').each(function() {

        if (this.value == '') {

          alert("Campo " + $(this).parent().find('label').text() + " é de preenchimento obrigatório.");

          lPass = false;
          return false;
        }
      })

      return lPass;
    })

    $('#FiltroInstituicao').live('change', function() {
      var oExercicio = $('#FiltroExercicio');

      $.getJSON( sBaseUrl + '/acordos/buscarExercicios/' + this.value, function(data) {

        if (!Object.keys(data).length) {
          alert('Nenhum exercício encontrado para a instituição selecionada.')
          return false
        }

        oExercicio.find('option:not(:first)').remove()

        $.each(data, function(iValor, sLabel) {
          oExercicio.append('<option value="' + iValor + '">' + sLabel + '</option>')
        })
      })
    })

  });
})(jQuery);