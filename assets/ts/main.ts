/// <reference path="../../typings/tsd.d.ts" />
jQuery(($) => {
  let header = document.getElementById('site-header')
                        .querySelector('.navbar-brand');

  let headroom = new Headroom(header, {
    offset: 200,
    tolerance: 5
  });
  headroom.init();

  if (document.body.classList.contains('home')) {
    $(document.body).stellar({
      horizontalScrolling: false,
      verticalOffset: 70
    });
  }

  var footer = document.getElementById('footer');

  function adjustFooterHeight() {
    fastdom.measure(() => {
      let footerOffset = footer.offsetTop,
          windowHeight = window.innerHeight;
      var minHeight = Math.max(windowHeight - footerOffset, 0);

      if (minHeight) {
        fastdom.mutate(() => {
          footer.style.minHeight = `${minHeight.toString(10)}px`;
        });
      }
    });
  }

  window.addEventListener('resize', adjustFooterHeight);
  adjustFooterHeight();
});
