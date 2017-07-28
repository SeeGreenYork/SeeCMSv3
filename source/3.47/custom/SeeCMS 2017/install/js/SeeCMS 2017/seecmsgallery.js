
$(document).ready(function() {
	
	$(".seecmsgallery").fancybox({
		openEffect  : 'none',
		closeEffect : 'none',
		prevEffect : 'none',
		nextEffect : 'none',
		closeBtn  : true,
		helpers : {
			title : {
				type : 'inside'
			},
			buttons	: true,
      overlay: {
        locked: false
      },
		},
		loop: false

	});

});

helpers: {
    overlay: {
      locked: false
    }
  }