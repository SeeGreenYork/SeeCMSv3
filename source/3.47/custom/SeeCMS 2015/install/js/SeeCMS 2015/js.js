$(document).ready(function(){

	$('.navbar-collapse ul').addClass('nav navbar-nav');
	$('.snav li a').addClass('btn btn-default');
  $('.newsarchive li a').addClass('btn btn-default');
  $('.login input').addClass('form-control');
  $('.login input.submitbutton').addClass('btn btn-primary');

	$('.banners').slick({
  	infinite: true,
  	slidesToShow: 1,
  	slidesToScroll: 1,
  	autoplay: true,
  	autoplaySpeed: 5000,
  	dots: true,
  	arrows: false
	});

  $(".fancybox").fancybox({
      openEffect  : 'none',
      closeEffect : 'none'
  });

});