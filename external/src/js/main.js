if ($('.js-hamburger').length) {
      $('.js-hamburger').click(function(){
        var nav = $('.header-nav-mobile')
        $(this).toggleClass('is-active');
        nav.toggleClass('header-nav-mobile--open');
        $('body').toggleClass('body-menu-open');
    }); 
};
$(window).on('load',function() {
	$('.textarea-scrollbar').scrollbar();
});

$(window).scroll(function () {
    if ($(this).scrollTop() > 0) {
        $('.js-scroller').fadeIn();
    } else {
        $('.js-scroller').fadeOut();
    }
});
$('.js-scroller').click(function () {
    $('body,html').animate({
        scrollTop: 0
    }, 400);
    return false;
});

$('.js-priview-lg').slick({
    infinite: true,
    slidesToShow: 1,
    slidesToScroll: 1,
    dots: false,
    fade: true,
    asNavFor: '.js-priview-sm'
});

$('.js-priview-sm').slick({
    infinite: true,
    slidesToShow: 4,
    slidesToScroll: 1,
    dots: false,
    arrows: true,
    focusOnSelect: true,
    asNavFor: '.js-priview-lg'
});

$('.textarea-scrollbar').scrollbar();

$('.form-control-text-area').scrollbar();
    function qtySelectors() {
      $('.js-qty__adjust').on('click', function (e) {
        e.preventDefault();
        var $el = $(this),
          $qtySelector = $el.closest('.count').find('.js-qty__num'),
          qty = parseInt($qtySelector.val());
        if ($el.hasClass('js-qty__adjust--plus')) {
          qty += 1;
        } else {
          qty -= 1;
          if (qty <= 0) qty = 0;
        }
        $qtySelector.val(qty);
        console.log(qty)
      });
    };
    qtySelectors();

  $('.js-select').select2({
     language: 'ru',
     width: '100%',
     minimumResultsForSearch: Infinity,
  });