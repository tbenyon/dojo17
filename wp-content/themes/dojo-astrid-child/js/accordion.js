(function ($) {
  $(function () {
    var accordionSelector = '.tb-accordion';
    var accordionItemSelector = '.tb-accordion-item';
    var accordionBarSelector = '.tb-accordion-bar';
    var accordionPanelSelector = '.tb-accordion-panel';
    var accordionActiveClass = 'active';
    var transition_duration = 300;

    $(accordionSelector).each(function () {
      $(accordionItemSelector).each(function () {
        var $accordionItem = $(this);
        $(accordionBarSelector, $accordionItem).click(function () {
          $(accordionPanelSelector, $accordionItem).slideToggle(transition_duration);
          $accordionItem.toggleClass(accordionActiveClass);
        })
      })

    })
  });
})(jQuery);