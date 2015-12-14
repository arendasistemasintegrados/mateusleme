;(function($){

  $(function($) {

    var base = $('#base').attr('href');

    $('#acordos').jqGrid({
      url: base + '/acordos/pesquisar',
      datatype: 'JSON',
      mtype: 'POST',
      postData: $.JSON.decode($('#parametro').val()),

      colNames:[
        'Número',
        'Data Assinatura',
        'Período de Vigência',
        'Objeto'
      ],

      colModel: [
        {name: 'numero', index:'Acordo.numero', align: 'center', width: '40px'},
        {name: 'data_assinatura', index: 'Acordo.data_assinatura' , align: 'center', width: '50px'},
        {name: 'periodo_vigencia', index: 'Acordo.periodo_vigencia' , align: 'center', width: '80px'},
        {name: 'objeto', index: 'Acordo.objeto'}
      ],

      sortname: 'Acordo.numero',
      autowidth: true,
      altRows: true,
      rowNum: 15,
      viewrecords: true,
      pager: '#pager',
      height: 'auto',
      width: 'auto',

      loadComplete: function(result) {
        if (result.total == 0) {
          $(".ui-jqgrid-bdiv").html('<h4 class="no-records">Nenhum Registro Encontrado.</h4>')
        }          
      },

      onCellSelect: function(rowid) {
        window.location.href = base + '/acordos/view/' + rowid
      }
    })

  })

})(jQuery);