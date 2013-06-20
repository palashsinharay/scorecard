/* ==========================================================
 * Customizr various scripts
 * ========================================================== */

jQuery(document).ready(function($) {
!function ($) {

  //"use strict"; // jshint ;_;

  $(window).on('load', function () {
     
     /* Detect layout and reorder content divs
      * ============== */
    var $window = $(window);

    function checkWidthonload() {
    var windowsize = $window.width();
    if (windowsize < 767 && $("#main-wrapper .container .span3").length > 0) {
        //if the window is smaller than 767px wide then turn
        $("#main-wrapper .container .article-container").insertBefore("#main-wrapper .container .span3");
      }
    }

    function checkWidth() {
    var windowsize = $window.width();
    if (windowsize < 767) {
        //if the window is smaller than 767px wide then turn
        $("#main-wrapper .container .span3").insertAfter("#main-wrapper .container .article-container");
      }
    else {
      if ($("#main-wrapper .container .span3.left").length > 0) {
        $("#main-wrapper .container .span3.left").insertBefore("#main-wrapper .container .article-container");
        }
      if ($("#main-wrapper .container .span3.right").length > 0) {
        $("#main-wrapper .container .span3.right").insertAfter("#main-wrapper .container .article-container");
        }
      }
    }

     // Bind event listener on resize
    $(window).resize(checkWidth);

    // Check width on load and reorders block if necessary
    checkWidthonload();

    // Add hover class on front widgets
      $(".widget-front, article").hover(
        function () {
          $(this).addClass('hover');
        },
        function () {
          $(this).removeClass('hover');
        });


        //arrows bullet list effect
        $('.widget li').hover(function() {
          $(this).addClass("on");
        }, function() {
        $(this).removeClass("on");
      });
    })

}(window.jQuery);

});