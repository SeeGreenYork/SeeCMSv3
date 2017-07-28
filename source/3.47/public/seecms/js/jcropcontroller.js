$(document).ready(function(){

  var seecmsimagepreview = 'original';
	var target = $('#jcrop-target');

	var sqaureApsectRatio = 1; // 1:1 square aspect ratio
	var landscapeAspectRatio = 15/9; // landscape
	var potraitASpectRatio = 10/13; // 1:1 potrait

	var image = $(target);

	var currentImage = $('.seecmspreviewimage');
	var width = currentImage.width();
	var height = currentImage.height();	
	var aspectRatio = width/height;
  
  var coords;
  var currentSize;


	$('#seecmsimagesize').on('change', function(){

		$( '.seecmspreviewimage' ).attr( 'src', $( '.seecmspreviewimage' ).attr('src').replace( '-' + seecmsimagepreview + '-', '-' + $(this).val() + '-'  ) );
    seecmsimagepreview = $(this).val();

		currentImage = $('.seecmspreviewimage');
    
    if( $(this).val() != 'original' && $('option:selected', this).attr('data-mode') == 'crop' ) {
      $('.recropimagebutton').show();
      currentSize = $(this).val();
    } else {
      $('.recropimagebutton').hide();
    }
	
		width = currentImage.width();
		height = currentImage.height();
		aspectRatio = width/height;
		$(currentImage).load(function(){
				width = currentImage.width();
				height = currentImage.height();
				aspectRatio = width/height;

				$(target).Jcrop({
					onSelect: showCoords,
					onChange: showCoords,
          allowMove: true,
					aspectRatio: aspectRatio,
          boxWidth: 930
				});
		});
	});

	$(target).Jcrop({
		onSelect: showCoords,
		onChange: showCoords,
    allowMove: true,
		aspectRatio: aspectRatio,
    boxWidth: 930
	});

  $('body').on( 'click', '.recropimagebutton', function( e ) {
    e.preventDefault();
    $('.recropimagewindow').fadeIn(200);
    $('.recropoverlay').show(200);
  });
  
  $('.close-window').on('click',function(e){
    e.preventDefault();
    $('.recropoverlay').hide();
    $('.recropimagewindow').fadeOut(200);
  });

  var popupwindowReCrop = $('recropimagewindow');
  
  $('.doneRecrop').on( 'click', function( e ) {
    e.preventDefault();
    
    $.ajax({
      type: "POST",
      url: "../../ajax/",
      data: { action: "media-resampleImage", id: $('input[name="id"]').val(), size: currentSize, sx: coords[0], sy: coords[1], sw: coords[5], sh: coords[4] }
    })
    .done(function( msg ) {
      if( msg ) {
        $('.recropoverlay').hide();
        $('.recropimagewindow').fadeOut(200);
        $( '.seecmspreviewimage' ).attr( 'src', $( '.seecmspreviewimage' ).attr('src') + '&r' + Math.floor((Math.random() * 1000) + 1) + "=1" );
      }
    });
  });

  function showCoords(c) {

      // variables can be accessed here as
      // c.x, c.y, c.x2, c.y2, c.w, c.h
      var firstXpos = c.x, firstYpos = c.y, secondXpos = c.x2, secondYpos = c.y2, height = c.h, width = c.w;
      
      coords = [firstXpos,firstYpos,secondXpos,secondYpos,height,width];
      return coords;
  };
});