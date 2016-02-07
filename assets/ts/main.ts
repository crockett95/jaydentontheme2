/// <reference path="../../typings/tsd.d.ts" />
(function($: JQueryStatic) {
  var headroom: Headroom;
  $(function() {
    let header = document.getElementById('site-header')
                          .querySelector('.navbar-brand');

    headroom = new Headroom(header, {
      offset: 200,
      tolerance: 5
    });

    let body = $('body');

    if (body.hasClass('home')) {
      body.stellar({
        horizontalScrolling: false
      });
    }

    headroom.init();
  });
})(jQuery);
