(function() {
  $(function() {
    $(".js-expand").click(function(event) {
      var $clicked, $details, expandId, offset;
      $clicked = $(event.target).closest('a');
      expandId = $clicked.attr('data-expand-id');
      $details = $("#" + expandId);
      if ($clicked.hasClass('expanded')) {
        $clicked.removeClass('expanded');
        $details.hide();
      } else if ($details.length) {
        $(".package__feature-details").hide();
        $details.toggle();
        $(".js-expand").removeClass('expanded');
        $clicked.toggleClass('expanded');
        offset = $clicked.offset();
        $details.css({
          top: offset.top + $clicked.outerHeight() + 10,
          left: 0
        });
      }
      event.preventDefault();
      return event.stopPropagation();
    });
    $(".js-close").click(function(event) {
      var $clicked;
      $clicked = $(event.target).closest('a');
      $(".package__feature-details").hide();
      $(".js-expand").removeClass('expanded');
      return event.preventDefault();
    });
    $("body").click(function(event) {
      if (!$(event.target).parents('.package__feature-details').length) {
        $(".package__feature-details").hide();
        return $(".js-expand").removeClass('expanded');
      }
    });
    $(".videoWrapper").each(function() {
      var attr;
      attr = $(this).attr('data-image-src');
      if (typeof attr !== typeof void 0 && attr !== false) {
        $(this).css('background-image', 'url(' + attr + ')');
        return $(this).css('background-size', 'cover');
      }
    });
    return $(".videoWrapper").click(function(event) {
      var char, currentSrc;
      event.preventDefault();
      $(this).addClass('clicked');
      currentSrc = $(this).find('iframe')[0].src;
      if (currentSrc.match(/youtube/)) {
        char = "?";
        if (currentSrc.match(/\?/)) {
          char = "&";
        }
        return $(this).find('iframe')[0].src += char + "autoplay=1";
      }
    });
  });

}).call(this);
