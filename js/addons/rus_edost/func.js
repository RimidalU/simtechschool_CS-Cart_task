(function (_, $) {
  $.ceEvent('on', 'ce.commoninit', function (context) {
    var $pickpointSelectTerminal = $('#pickpoint_select_terminal', context);

    if (!$pickpointSelectTerminal.length) {
      return;
    }

    $pickpointSelectTerminal.on('click', function () {
      var elm = $(this);
      var pickpoint_select_state = elm.data('pickpoint-select-state');
      var pickpoint_select_city = elm.data('pickpoint-select-city');
      PickPoint.open(function (result) {
        $('#pickpoint_id').val(result.id);
        $('#pickpoint_name').val(result.name);
        $('#pickpoint_address').val(result.address);
        $('#pickpoint_name_terminal').text(result.name);
        $('#pickpoint_address_terminal').text(result.address);
        fn_calculate_total_shipping_cost(true);
      }, {
        fromcity: pickpoint_select_state,
        city: pickpoint_select_city
      });
      return false;
    });
  });
})(Tygh, Tygh.$);