(function (_, $) {
  $(_.doc).on('click', '.ty-terminal-radio', function (e) {
    fn_calculate_total_shipping_cost();
  });
})(Tygh, Tygh.$);