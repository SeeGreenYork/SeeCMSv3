
$(document).ready(function(){

  $( '.productnav a' ).on('click', function ( e ) {
  
    e.preventDefault();
  
    var id = $(this).attr('class');
    $( '.productnav li' ).removeClass( 'selected' );
    $(this).parent().addClass( 'selected' );
    
    $( '.producttab' ).hide();
    $( '.' + id + 'tab' ).show();
    
  });
  
  $('.catop').on( 'change', function(){ 
    
    var cat = $(this).attr('id').replace('cat','');

    if ($(this).prop("checked")) {
      $('.parent' + cat ).show();
    } else {
      $('.parent' + cat + ' .catop' ).attr( 'checked', false );
      $('.parent' + cat + ' .catop' ).prop( 'checked', false );
      $('.parent' + cat + ' .catop' ).parent().children('.checked').removeClass( 'checked' );
      $('.parent' + cat ).hide();
      $('.parent' + cat + ' div' ).hide();
    }
  });
  
  $('#addnewcategoryoptionbutton').on( 'click', function( e ) { 
  
    e.preventDefault();
    $( "#seecmsshopcategoryoptionslist table.sortable tbody" ).append( '<tr id="newoption"><td class="name"><input type="text" value="' + $('#addnewcategoryoption').val() + '" /></td><td><a class=\"seecmsremoveoption\" href=\"#\">Remove</a></td></tr>' );
    $('#addnewcategoryoption').val('');
  });
  
  $('#seecmsshopcategoryoptionslist').on( 'click', '.seecmsremoveoption', function( e ) { 
  
    e.preventDefault();
    $(this).parent().parent().remove();
  });
  
  $( '.seecmsshopcategoryeditsave' ).on( 'click', function( e ) { 
    
    var indexing = '';
  
    $( "#seecmsshopcategoryoptionslist table.sortable tbody tr" ).each(function( index ) {
    
      indexing += index + "," + $( this ).attr('id') + "," + $( this ).children('.name').children('input').val() + "|";
    });
  
    $('#seecmsshopcategoryoptionindexing').val( indexing );
      
  });
  
  $('#seecmsshopmedia').on("click", function( e ){
      
    e.preventDefault();
    $('#selectproductimage').dialog('open');
  });


  $(".selectproductimage").dialog({
      resizable: true,
      autoOpen:false,
      modal: true,
      width:800,
      height:600,
      buttons: {
        'Done': function() {
            $(this).dialog('close');
        }, 
        Cancel: function() {
          $(this).dialog('close');
        } 
      }
    });  

  $('body').on( 'click', '.adfimages .image', function( e ) {
    e.preventDefault();
    id = $(this).attr('id').replace('i','');
    $('#media_id').val( id );
    $('#seecmsshopmedia').html( $(this).html() );
    $(".selectproductimage").dialog('close');
  });

});