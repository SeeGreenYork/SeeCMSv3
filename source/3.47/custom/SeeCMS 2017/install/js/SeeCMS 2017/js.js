$(document).ready(function(){
	
	$('.fade').slick({
	  slidesToShow: 1,
	  slidesToScroll: 1,
	  autoplay: true,
	  autoplaySpeed: 4000,
	  dots: true,
	  infinite: false,
	  arrows: false,
	  speed: 500,
	  fade: true,
	  cssEase: 'linear'
	});

	//$('div.content').filter(function() { return $.trim($(this).text()) === '' }).hide();

	$(document).on('click', '.nav-icon',function(){  
    $('.mobile-nav').slideDown(200);
    $('.nav-icon').addClass('open');
    return false;
  });
  $(document).on('click', '.nav-icon.open',function(){  
    $('.mobile-nav').slideUp(200);
    $('.nav-icon').removeClass('open');
    return false;
  });

  $(document).on('click', 'a.searchbutton',function(){  
    $('.search').fadeIn(200);
  	$('.search input').focus();
  	$(this).addClass('open');
    return false;
  });
  $(document).on('click', 'a.searchbutton.open',function(){  
    $('.search').fadeOut(200);
  	$(this).removeClass('open');
    return false;
  });

});

$(window).resize(function(){
  if ($(window).width() >= 670){
    $('.mobile-nav').hide(0);
    $('.nav-icon').removeClass('open');
  };
});