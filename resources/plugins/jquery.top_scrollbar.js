(function($) {
  $.fn.topScrollbar = function(activate) {
    if (activate == undefined) activate = true;

    return this.each(function() {
      var self = $(this);

      if (self.prev().hasClass('jquery-top-scrollbar')) self.prev().remove();

      if (!activate) return;

      var tmp = self.clone().css({
        'position': 'fixed',
        'width': 'auto',
        'visibility': 'hidden',
        'overflow-y': 'auto'
      });

      tmp.appendTo('body');

      var innerWidth = tmp.width();

      tmp.remove();

      if (self.width() >= innerWidth) return;

      var outer = $('<div class="jquery-top-scrollbar">');
      outer.css({width: self.width(), height: 15, 'overflow-y': 'hidden'});

      var inner = $('<div>');
      inner.css({width: innerWidth, height: 15});

      self.before(outer.append(inner));

      outer.scroll(function() {
        self.scrollLeft(outer.scrollLeft());
      });

      self.scroll(function() {
        outer.scrollLeft(self.scrollLeft());
      });

      self.scroll();
    });
  };
})(jQuery);
