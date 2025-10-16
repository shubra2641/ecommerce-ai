(function($) {
  "use strict"; // Start of use strict

  // Toggle the side navigation
  $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
    $("body").toggleClass("sidebar-toggled");
    $(".sidebar").toggleClass("toggled");
    if ($(".sidebar").hasClass("toggled")) {
      $('.sidebar .collapse').collapse('hide');
    };
  });

  // Close any open menu accordions when window is resized below 768px
  $(window).resize(function() {
    if ($(window).width() < 768) {
      $('.sidebar .collapse').collapse('hide');
    };
    
    // Toggle the side navigation when window is resized below 480px
    if ($(window).width() < 480 && !$(".sidebar").hasClass("toggled")) {
      $("body").addClass("sidebar-toggled");
      $(".sidebar").addClass("toggled");
      $('.sidebar .collapse').collapse('hide');
    };
  });

  // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
  $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
    if ($(window).width() > 768) {
      var e0 = e.originalEvent,
        delta = e0.wheelDelta || -e0.detail;
      this.scrollTop += (delta < 0 ? 1 : -1) * 30;
      e.preventDefault();
    }
  });

  // Scroll to top button appear
  $(document).on('scroll', function() {
    var scrollDistance = $(this).scrollTop();
    if (scrollDistance > 100) {
      $('.scroll-to-top').fadeIn();
    } else {
      $('.scroll-to-top').fadeOut();
    }
  });

  // Smooth scrolling using jQuery easing
  $(document).on('click', 'a.scroll-to-top', function(e) {
    var $anchor = $(this);
    $('html, body').stop().animate({
      scrollTop: ($($anchor.attr('href')).offset().top)
    }, 1000, 'easeInOutExpo');
    e.preventDefault();
  });

})(jQuery); // End of use strict

// Payment Gateways admin AJAX handlers
(function($){
  $(function(){
    // AJAX toggle
    $(document).on('click', 'a[data-toggle-gateway]', function(e){
      e.preventDefault();
      var $btn = $(this);
      var url = $btn.attr('href');
      $.get(url)
        .done(function(){
          // toggle badge text and button
          var $card = $btn.closest('.card');
          // simple approach: reload for consistency
          location.reload();
        })
        .fail(function(){
          alert('Failed to toggle gateway');
        });
    });

    // On create page, when selecting gateway type, reload with ?type=slug to render fields server-side
    $(document).on('change', 'select[name="type"]', function(){
      var val = $(this).val();
      var base = window.location.pathname;
      if(val){
        window.location.href = base + '?type=' + encodeURIComponent(val);
      } else {
        window.location.href = base;
      }
    });
  });
})(jQuery);

// Additional admin initializers: filemanager, summernote and parent-toggle
(function($){
  $(document).ready(function(){
    // initialize laravel-filemanager buttons if available
    if (typeof $.fn.filemanager === 'function') {
      $('[id^="lfm"]').each(function(){
        try {
          $(this).filemanager('image');
        } catch(e) {
          console && console.warn && console.warn('filemanager init failed', e);
        }
      });
    }

    // initialize summernote for language summary fields
    if (typeof $.fn.summernote === 'function') {
      $('.lang-summary').each(function(){
        var $el = $(this);
        if (!$el.data('summernote-initialized')) {
          $el.summernote({
            placeholder: "Write short description.....",
            tabsize: 2,
            height: 120
          });
          $el.data('summernote-initialized', true);
        }
      });
    }

    // toggle parent category visibility
    $(document).on('change', '#is_parent', function(){
      var is_checked = $('#is_parent').prop('checked');
      if(is_checked){
        $('#parent_cat_div').addClass('d-none');
        $('#parent_cat_div').val('');
      } else {
        $('#parent_cat_div').removeClass('d-none');
      }
    });
  });
})(jQuery);
