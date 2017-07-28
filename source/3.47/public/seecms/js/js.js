// SeeCMS is a website content management system
// @author See Green <http://www.seegreen.uk>
// @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
// @copyright 2015 See Green Media Ltd

var item;
var id;
var mode;
var selectedItem;
var selectedItemLabel;

var skipInitialMediaLoad = 0;
var onthemove = 0;

$(document).ready(function(){

  $( document ).tooltip();

    /*$(function() {
	    $('ul.draggable').nestedSortable({
	      handle: 'div',
	      items: 'li',
	      toleranceElement: '> div',
	      snap: "ul.draggable",
	      maxLevels: 10
	    });
  	});
  */
  	//$('ul.draggable li').has('ul').children().children('.name').addClass('expand');

  $(document).on("click", ".draggable div", function( e ){
    e.preventDefault();
    // if hasChildren or insert points showing
    if (($(this).parent().hasClass('hasChildren')) ) {
    
      if ($(this).parent().hasClass('open')){
        $(this).parent().children('ul').slideUp(200).fadeOut(200);
        $(this).parent().removeClass('open');
        $(this).children('a.expand').children('i').removeClass('fa-rotate-180');
        $.ajax({ type: "POST", url: "../ajax/", data: { action: "page-adminTreeSession", id: $(this).parent().attr('id').replace('p',''), status: 0 } });
      } else {
        $(this).parent().children('ul').slideDown(200).fadeIn(200);
        $(this).parent().addClass('open');
        $(this).children('a.expand').children('i').addClass('fa-rotate-180');
        $.ajax({ type: "POST", url: "../ajax/", data: { action: "page-adminTreeSession", id: $(this).parent().attr('id').replace('p',''), status: 1 } });
      }
    }

  });
  
  $( "table.sortable tbody" ).sortable();
  
  $('.savewithtinymce').on( 'click', function() { 
    tinyMCE.triggerSave();
  });
      
  $(document).on("click", ".addnewroute", function(e){
    e.preventDefault();
    
    if( nextroute == 1 ) {
      $( routeHTMLHead ).appendTo(".pageurlsinner");
    }
    
    $( routeHTML.replace(new RegExp('XXX', 'g'),nextroute) ).appendTo(".pageurlsinner");
    nextroute++;
  });
    
  $(document).on("click", ".name, .cloned, .preview", function(e){
    e.stopPropagation();
  });
    
  //$(document).on("click", ".draggable .open div", function(){
  //});

  var $this = $(this);

  //$('.folders ul li').has('ul').addClass('child');

  // Media overlay windows
  $('.medialistinner').on( 'mouseenter', '.thumb', function(){   
  	$(this).children('.overlay').fadeIn(200);
  });

  $('.medialistinner').on( 'mouseleave', '.thumb', function(){   
  	$(this).children('.overlay').stop(true,true).fadeOut(200);
  });

  $('.medialistinner').on( 'mouseleave', '.thumb', function(){   
    $(this).children('.overlay.onthemove').stop(true,true).fadeIn(0);
  });

  $(document).on("click", ".adf h3", function(){
    if( !$(this).hasClass('adfpopup') ) {
      $(this).parent().children('div').slideDown(200).fadeIn(200);
      $(this).addClass('open');
    }
  });

  $(document).on("click", "h3.open", function(){ 
    $(this).parent().children('div').slideUp(200).fadeOut(200);
    $(this).removeClass('open');
  });

  // set up datepicker
  $( ".datepicker" ).datepicker({dateFormat: "dd M yy"});
  $( ".datepicker.current" ).datepicker("setDate", new Date());

  // clears date from inputs
  $('.cleardate').click(function(){   
    $(this).parent().children('.datepicker').datepicker( "setDate" , null );
    $(this).parent().children('.time').val( '' );
  });

  // resets date to todays date
  $('.resetdate').click(function(){   
    $(this).parent().children('.current').datepicker( "setDate" , new Date() );
  });

  // default times if date entered
  $('#commencement').change(function(){
    if ($('#commencement').length) {
      $(this).parent().children('.time').val('00:00');
    } 
    else {
      $(this).parent().children('.time').val('');
    }
  });
  
  $('#expiry').change(function(){
    if ($('#expiry').length) {
     $(this).parent().children('.time').val('23:59');
   }
   else {
     $(this).parent().children('.time').val('');
    }
  });

  // New page modal window
  $("#newpagetitle").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:170,
    buttons: {
      'Create': function() {
      $(this).dialog('close');
      mode = 'create';
      $('.insertpoint').css( 'display', 'block' );
        if ( $('.insertpoint').is(':visible') ) {
          $('li .page').addClass('nohover');
        }
        else{
          $('li .page').removeClass('nohover');
        }
      }, 
      Cancel: function() {
      $(this).dialog('close');
      } 
    }
  }); 

  $("#newadminuser").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:230,
    buttons: {
      'Create': function() {
      $(this).dialog('close');
      mode = 'create';
      $('.insertpoint').css( 'display', 'block' );
        if ( $('.insertpoint').is(':visible') ) {
          $('li .page').addClass('nohover');
        }
        else{
          $('li .page').removeClass('nohover');
        }
      }, 
      Cancel: function() {
      $(this).dialog('close');
      } 
    }
  });   

  // Delete page modal window
  $('.deletepage').click(function(){
    $('#deletepagepopup').dialog('open');
  });

  // Add new post title modal window
  $("#newposttitle").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:240,
    buttons: {
      'Create': function() {
      
        var posttitle = $('#posttitle').val();
        var posttype = $('#posttype').val();
        $.ajax({
          type: "POST",
          url: "../ajax/",
          data: { action: "post-create", parentid: postfolder, title: posttitle, posttype: posttype }
        })
        .done(function( msg ) {
          if( msg ) {
            var ret = jQuery.parseJSON( msg );
            if( ret.done == 1 ) {
              $('.newslistinner').html( ret.data );
              return;
            }
          }
          alert( 'Error' );
        });
        $(this).dialog('close');
      }, 
      Cancel: function() {
        $(this).dialog('close');
      } 
    }
  });  

  // Add new folder title modal window
  $("#newfoldertitle").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:170,
    buttons: {
      'Create': function() {
        var foldertitle = $('#foldertitle').val();
        $.ajax({
          type: "POST",
          url: "../ajax/",
          data: { action: "post-create", parentid: postfolder, title: foldertitle, isfolder: 1 }
        })
        .done(function( msg ) {
          if( msg ) {
            var ret = jQuery.parseJSON( msg );
            if( ret.done == 1 ) {
              // Load folder tree
              $.ajax({
                type: "POST",
                url: "../ajax/",
                data: { action: "post-folderTree" }
              })
              .done(function( msg ) {
                $('.folders').html( msg );
                return;
              });
            }
            return;
          }
          alert( 'Error' );
        });
        $(this).dialog('close');
      }, 
      Cancel: function() {
        $(this).dialog('close');
      } 
    }
  }); 

  $("#newgrouptitle").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:170,
    buttons: {
      'Create': function() {
        var foldertitle = $('#foldertitle').val();
        $.ajax({
          type: "POST",
          url: "../ajax/",
          data: { action: "post-create", parentid: postfolder, title: foldertitle, isfolder: 1 }
        })
        .done(function( msg ) {
          if( msg ) {
            var ret = jQuery.parseJSON( msg );
            if( ret.done == 1 ) {
              $.ajax({
                type: "POST",
                url: "../ajax/",
                data: { action: "post-folderTree" }
              })
              .done(function( msg ) {
                $('.folders').html( msg );
                return;
              });
            }
            return;
          }
          alert( 'Error' );
        });
        $(this).dialog('close');
      }, 
      Cancel: function() {
        $(this).dialog('close');
      } 
    }
  }); 

  // Add media folder modal window
  $("#newmediafoldertitle").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:170,
    buttons: {
      'Create': function() {
        var foldertitle = $('#foldertitle').val();
        $.ajax({
          type: "POST",
          url: "../ajax/",
          data: { action: "media-create", parentid: mediafolder, title: foldertitle, isfolder: 1 }
        })
        .done(function( msg ) {
          if( msg ) {
            var ret = jQuery.parseJSON( msg );
            if( ret.done == 1 ) {
              $.ajax({
                type: "POST",
                url: "../ajax/",
                data: { action: "media-folderTree" }
              })
              .done(function( msg ) {
                $('.folders').html( msg );
                return;
              });
            }
            return;
          }
          alert( 'Error' );
        });
        $(this).dialog('close');
      }, 
      Cancel: function() {
        $(this).dialog('close');
      } 
    }
  }); 

  // Add Downloads folder modal window
  $("#newdownloadfoldertitle").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:170,
    buttons: {
      'Create': function() {
        var foldertitle = $('#foldertitle').val();
        $.ajax({
          type: "POST",
          url: "../ajax/",
          data: { action: "download-create", parentid: downloadfolder, title: foldertitle, isfolder: 1 }
        })
        .done(function( msg ) {
          if( msg ) {
            $('.folders').html( msg );
            return;
          }
          alert( 'Error' );
        });
        $(this).dialog('close');
      }, 
      Cancel: function() {
        $(this).dialog('close');
      } 
    }
  }); 

  // Add new folder title modal window
  $("#newcreategroup").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:170,
    buttons: {
      'Create': function() {
        var foldertitle = $('#grouptitle').val();
        $.ajax({
          type: "POST",
          url: "../ajax/",
          data: { action: "group-create", parentid: postfolder, title: foldertitle, isfolder: 1 }
        })
        .done(function( msg ) {
          if( msg ) {
            var ret = jQuery.parseJSON( msg );
            if( ret.done == 1 ) {
              $.ajax({
                type: "POST",
                url: "../ajax/",
                data: { action: "group-folderTree" }
              })
              .done(function( msg ) {
                $('body').html( msg );
                return;
              });
            }
            return;
          }
          alert( 'Error' );
        });
        $(this).dialog('close');
      }, 
      Cancel: function() {
        $(this).dialog('close');
      } 
    }
  }); 

  // Delete posts
  $('.newslist').on("click", '.deletepost:not(.accessdisabled)', function(){
    
    item = $(this).attr('id').replace( 'deletepost-', '' );
    
    $('#deletepostpopup').html( '<p>Are you sure you want to delete this post?</p>' );
    $('#deletepostpopup').dialog('open');
  });
  
  // Delete downloads
  $('.doclist').on("click", '.delete:not(.accessdisabled)', function(){
  
    item = $(this).attr('id').replace( 'deletedoc-', '' );
  
    $('#deletedocpopup').html( '<p>Are you sure you want to delete this file?</p>' );
    $('#deletedocpopup').dialog('open');
  }); 

  // Delete media files
  $('.medialistinner').on("click", '.deletemedia:not(.accessdisabled)', function(){
  
    item = $(this).attr('id').replace( 'deletemedia-', '' );
  
    $('#deletemediapopup').html( '<p>Are you sure you want to delete this file?</p>' );
    $('#deletemediapopup').dialog('open');      
  });

  // Delete media folders
  $('.folders').on('click', 'span.delete:not(.accessdisabled)', function(){
    $('#deletemediafolderpopup').dialog();
    $('.dialog').dialog('open');
    $('#deletemediafolderpopup').html( '<p>Are you sure you want to delete this folder?</p>' );
    $('#deletemediafolderpopup').dialog('open');
  });

  // Delete post folders
  $('.folders').on('click', 'span.delete:not(.accessdisabled)', function(){
    $('#deletepostfolderpopup').dialog();
    $('.dialog').dialog('open');
    $('#deletepostfolderpopup').html( '<p>Are you sure you want to delete this folder?</p>' );
    $('#deletepostfolderpopup').dialog('open');
  });
    
  // Delete users 
  $('table.users').on('click', 'a.delete', function(){
    selectedItem = $(this).attr( 'data-siteuserid' );
    $('#deleteuserpopup').dialog();
    $('.dialog').dialog('open');
    $('#deleteuserpopup').html( '<p>Are you sure you want to delete this user?</p>' );
    $('#deleteuserpopup').dialog('open');
  });
  
  $('table.sitegroups').on('click', 'a.delete', function(){
    selectedItem = $(this).attr( 'data-sitegroupid' );
    $('#deletesitegrouppopup').dialog();
    $('.dialog').dialog('open');
    $('#deletesitegrouppopup').html( '<p>Are you sure you want to delete this group?</p>' );
    $('#deletesitegrouppopup').dialog('open');
  });
  
  // Delete admin users
  $('table.adminusers').on('click', 'a.delete', function(){
    selectedItem = $(this).attr( 'data-adminuserid' );
    $('#deleteadminuserpopup').dialog();
    $('.dialog').dialog('open');
    $('#deleteadminuserpopup').html( '<p>Are you sure you want to delete this admin user?</p>' );
    $('#deleteadminuserpopup').dialog('open');
  });
  
  // Delete admin user group
  $('table.adminusergroups').on('click', 'a.delete', function(){
    selectedItem = $(this).attr( 'data-admingroupid' );
    $('#deleteadmingrouppopup').dialog();
    $('.dialog').dialog('open');
    $('#deleteadmingrouppopup').html( '<p>Are you sure you want to delete this admin group?</p>' );
    $('#deleteadmingrouppopup').dialog('open');
  });

  $('.folders').on('click', 'span.delete:not(.accessdisabled)', function(){
    $('#deletedownloadfolderpopup').dialog();
    $('.dialog').dialog('open');
    $('#deletedownloadfolderpopup').html( '<p>Are you sure you want to delete this folder?</p>' );
    $('#deletedownloadfolderpopup').dialog('open');
  });
    
  // Media folder edit
  $('.folders').on('click', 'span.viewedit:not(.accessdisabled)', function( e ) {
    e.preventDefault();

    $('#editmediafolderpopup').dialog();
    mediafolder = $(this).parent().attr('id').replace('folder', '');
    $('#foldertitle2').val( $(this).data('title') );

    // Adminusergroup permissions
    $('.admingrouppermission').val( 0 ); // reset
    var adminusergrouppermissions = $(this).data('adminusergrouppermissions');

    if( typeof adminusergrouppermissions == 'object' ) {
      $.each( adminusergrouppermissions, function( k, v ) { $( '[name="adminusergrouppermissions[' + k + ']"]' ).val( v ); } );
    }

    $('#editmediafolderpopup').dialog('open');
  });
  
  // Download folder edit
  $('.folders').on('click', 'span.viewedit:not(.accessdisabled)', function( e ){
    e.preventDefault();
    
    $('#editdownloadfolderpopup').dialog();
    downloadfolder = $(this).parent().attr('id').replace('folder', '');
    $('#foldertitle2').val( $(this).data('title') );
    
    // Websiteusergroup permissions
    $('.security input:checkbox').prop('checked', false); // reset
    var websiteusergrouppermissions = $(this).data('websiteusergrouppermissions');

    if( typeof websiteusergrouppermissions == 'object' ) {
      $.each( websiteusergrouppermissions, function( k, v ) { $( '[name="websiteusergrouppermissions[]"][value="' + k + '"]' ).prop('checked', v ); } );
    }
    
    if( !$('.security-group:checked').length ) {
      $('[name="websiteusergrouppermissions"]').prop('checked', true);
    } 
    
    // Adminusergroup permissions
    $('.admingrouppermission').val( 0 ); // reset
    var adminusergrouppermissions = $(this).data('adminusergrouppermissions');

    if( typeof adminusergrouppermissions == 'object' ) {
      $.each( adminusergrouppermissions, function( k, v ) { $( '[name="adminusergrouppermissions[' + k + ']"]' ).val( v ); } );
    }
    
    $('#editdownloadfolderpopup').dialog('open');
  });

  // Post folder edit
  $('.folders').on('click', 'span.viewedit:not(.accessdisabled)', function(e){
    e.preventDefault();
    
    $('#editpostfolderpopup').dialog();
    mediafolder = $(this).parent().attr('id').replace('folder', '');
    $('#foldertitle2').val( $(this).data('title') );
    
    // Adminusergroup permissions
    $('.admingrouppermission').val( 0 );
    var adminusergrouppermissions = $(this).data('adminusergrouppermissions');

    if( typeof adminusergrouppermissions == 'object' ) {
      $.each( adminusergrouppermissions, function( k, v ) { $('#adminusergroup-' + k ).val( v ); } );
    }
    
    $('#editpostfolderpopup').dialog('open');
  });

  // Dialogue popups
  $("#deletemediafolderpopup").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:170,
    buttons: {
      Yes: function() {
        $.ajax({
          type: "POST",
          url: "../ajax/",
          data: { action: "media-delete", id: mediafolder }
        })
        .done(function( msg ) {
          if( msg ) {
            var ret = jQuery.parseJSON( msg );
            $('.medialistinner').html( ret.media );
            $('.folders').html( ret.folderTree );
            return;
          }
          alert( 'Error' );
        });
        $(this).dialog('close');
      }, 
      No: function() {
        $(this).dialog('close');
      } 
    }
  });

  $("#deletedownloadfolderpopup").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:170,
    buttons: {
      Yes: function() {
        $.ajax({
          type: "POST",
          url: "../ajax/",
          data: { action: "download-delete", id: downloadfolder }
        })
        .done(function( msg ) {
          if( msg ) {
            var ret = jQuery.parseJSON( msg );
            $('.doclistinner').html( ret.downloads );
            $('.folders').html( ret.folderTree );
            return;
          }
          alert( 'Error' );
        });
        $(this).dialog('close');
      }, 
      No: function() {
        $(this).dialog('close');
      } 
    }
  });
  
  $("#deletepostfolderpopup").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:170,
    buttons: {
      Yes: function() {
        $.ajax({
          type: "POST",
          url: "../ajax/",
          data: { action: "post-delete", id: postfolder }
        })
        .done(function( msg ) {
          if( msg ) {
            var ret = jQuery.parseJSON( msg );
            $('.newslistinner').html( ret.posts );
            $('.folders').html( ret.folderTree );
            return;
          }
          alert( 'Error' );
        });
        $(this).dialog('close');
      }, 
      No: function() {
        $(this).dialog('close');
      } 
    }
  });

    $("#deletesitegrouppopup").dialog({
      resizable: true,
      autoOpen:false,
      modal: true,
      width:400,
      height:170,
      buttons: {
        Yes: function() {
          $.ajax({
            type: "POST",
            url: "../../ajax/",
            data: { action: "websiteUser-deleteGroup", id: selectedItem }
          })
          .done(function( msg ) {
            if( msg ) {
              $('a[data-sitegroupid='+selectedItem+']').parent().parent().remove();
            } else {
              alert( 'Error' );
            }
          });
          $(this).dialog('close');
        }, 
        No: function() {
          $(this).dialog('close');
        } 
      }
    });

  $("#deleteuserpopup").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:170,
    buttons: {
      Yes: function() {
        $.ajax({
          type: "POST",
          url: "../../ajax/",
          data: { action: "websiteUser-delete", id: selectedItem }
        })
        .done(function( msg ) {
          if( msg ) {
            $('a[data-siteuserid='+selectedItem+']').parent().parent().remove();
          } else {
            alert( 'Error' );
          }
        });
        $(this).dialog('close');
      }, 
      No: function() {
        $(this).dialog('close');
      } 
    }
  });

  $("#deleteadminuserpopup").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:170,
    buttons: {
      Yes: function() {
        $.ajax({
          type: "POST",
          url: "../../ajax/",
          data: { action: "adminAuthentication-delete", id: selectedItem }
        })
        .done(function( msg ) {
          if( msg ) {
            $('a[data-adminuserid='+selectedItem+']').parent().parent().remove();
          } else {
            alert( 'Error' );
          }
        });
        $(this).dialog('close');
      }, 
      No: function() {
        $(this).dialog('close');
      } 
    }
  });

  $("#deleteadmingrouppopup").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:170,
    buttons: {
      Yes: function() {
        $.ajax({
          type: "POST",
          url: "../../ajax/",
          data: { action: "adminAuthentication-deleteGroup", id: selectedItem }
        })
        .done(function( msg ) {
          if( msg ) {
            $('a[data-admingroupid='+selectedItem+']').parent().parent().remove();
          } else {
            alert( 'Error' );
          }
        });
        $(this).dialog('close');
      }, 
      No: function() {
        $(this).dialog('close');
      } 
    }
  });
  
  $("#editmediafolderpopup").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    // height:170,
    buttons: {
      Save: function() {
        var form = $( "#editmediafolderpopup form" ).serialize();

        $.ajax({
          type: "POST",
          url: "../ajax/",
          data: { action: "media-savefolder", id: mediafolder, form: form }
        })
        .done(function( msg ) {
          if( msg ) {
            $('.folders').html( msg );
            return;
          }
          alert( 'Error' );
        });
        $(this).dialog('close');
      }, 
      Cancel: function() {
        $(this).dialog('close');
      } 
    }
  });
  
  $("#editdownloadfolderpopup").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:470,
    buttons: {
      Save: function() {
        var form = $( "#editdownloadfolderpopup form" ).serialize();
        
        $.ajax({
          type: "POST",
          url: "../ajax/",
          data: { action: "download-savefolder", id: downloadfolder, form: form }
        })
        .done(function( msg ) {
          if( msg ) {
            $('.folders').html( msg );
            return;
          }
          alert( 'Error' );
        });
        $(this).dialog('close');
      }, 
      Cancel: function() {
        $(this).dialog('close');
      } 
    }
  });
  
  $("#editpostfolderpopup").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    // height:170,
    buttons: {
      Save: function() {
        var form = $( "#editpostfolderpopup form" ).serialize();

        $.ajax({
          type: "POST",
          url: "../ajax/",
          data: { action: "post-savefolder", id: mediafolder, form: form }
        })
        .done(function( msg ) {
          if( msg ) {
            $('.folders').html( msg );
            return;
          }
          alert( 'Error' );
        });
        $(this).dialog('close');
      }, 
      Cancel: function() {
        $(this).dialog('close');
      } 
    }
  });

  //sort tables by heading
  if( jQuery().tablesorter ) {
    
    $('.order').tablesorter({
      widgets        : ['zebra', 'columns'],
      usNumberFormat : false,
      sortReset      : true,
      sortRestart    : true
    });
  }

  //clear dates
  $('.cleardate').on( 'click', function( e ) { 
    e.preventDefault();
    $(this).parent().children('input').val('');
  });

  //character count on inputs
  $('input.count').keyup(function count(){
    counter = $("input.count").val().length;
    $("#count").html(counter);
  });

  $('.folders').on( 'click', 'span.move:not(.accessdisabled)', function(e){
    e.stopPropagation();
    $('span.delete').fadeOut(200);
    $('span.viewedit').fadeOut(200);
    $('span.move').fadeOut(200);
    $('span.secure').fadeOut(200);
    $(this).parent().addClass('onthemove');
    onthemove = $(this).parent().attr('id').replace('folder','');
    $('span.target').fadeIn(0);
    $('.onthemove').parent().find('span.target').fadeOut(0);
  });

  $('.medialistinner').on( 'click', '.thumb a.move:not(.accessdisabled)', function(e){
    e.stopPropagation();
    if(onthemove) {
      $('.folders span.delete').fadeIn(200);
      $('.folders span.delete').fadeIn(200);
      $('.folders span.viewedit').fadeIn(200);
      $('.folders span.move').fadeIn(200);
      $(this).parent().removeClass('onthemove');
      onthemove = 0;
      $('.folders span.target').fadeOut(0);
      $('.folders .selected').children().children().next('span.target').fadeIn(0);
      $('.folders .selected').children().removeClass('exclude');
      $('.thumb a.delete').fadeIn(0);
      $('.thumb a.viewedit').fadeIn(0);
      $('.thumb a.move').fadeIn(0);
      
    } else {
      $('.folders span.delete').fadeOut(200);
      $('.folders span.viewedit').fadeOut(200);
      $('.folders span.move').fadeOut(200);
      $(this).parent().addClass('onthemove');
      onthemove = $(this).attr('id').replace('move','');
      $('.folders span.target').fadeIn(0);
      $('.folders .selected').children().children().next('span.target').fadeOut(0);
      $('.folders .selected').children().addClass('exclude');
      $('.thumb a.delete').fadeOut(0);
      $('.thumb a.viewedit').fadeOut(0);
      $('.thumb a.move').fadeOut(0);
      $(this).fadeIn(0);
    }
  });

  $('.newslist').on( 'click', '.page a.move:not(.accessdisabled)', function(e){
    e.stopPropagation();
    if(onthemove) {
      $('.folders span.delete').fadeIn(200);
      $('.folders span.delete').fadeIn(200);
      $('.folders span.viewedit').fadeIn(200);
      $('.folders span.move').fadeIn(200);
      $(this).parent().removeClass('onthemove');
      onthemove = 0;
      $('.folders span.target').fadeOut(0);
      $('.folders .selected').children().children().next('span.target').fadeIn(0);
      $('.folders .selected').children().removeClass('exclude');
      $('.page a.delete').fadeIn(0);
      $('.page a.published').fadeIn(0);
      $('.page a.notpublished').fadeIn(0);
      $('.page a.viewedit').fadeIn(0);
      $('.page a.move').fadeIn(0);
      
    } else {
      $('.folders span.delete').fadeOut(200);
      $('.folders span.viewedit').fadeOut(200);
      $('.folders span.move').fadeOut(200);
      $(this).parent().addClass('onthemove');
      onthemove = $(this).attr('id').replace('move','');
      $('.folders span.target').fadeIn(0);
      $('.folders .selected').children().children().next('span.target').fadeOut(0);
      $('.folders .selected').children().addClass('exclude');
      $('.page a.delete').fadeOut(0);
      $('.page a.published').fadeOut(0);
      $('.page a.notpublished').fadeOut(0);
      $('.page a.viewedit').fadeOut(0);
      $('.page a.move').fadeOut(0);
      $(this).fadeIn(0);
    }
  });

  $('.doclist').on( 'click', '.page a.move:not(.accessdisabled)', function(e){
    e.stopPropagation();
    if(onthemove) {
      $('.folders span.delete').fadeIn(200);
      $('.folders span.delete').fadeIn(200);
      $('.folders span.viewedit').fadeIn(200);
      $('.folders span.move').fadeIn(200);
      $(this).parent().removeClass('onthemove');
      onthemove = 0;
      $('.folders span.target').fadeOut(0);
      $('.folders .selected').children().children().next('span.target').fadeIn(0);
      $('.folders .selected').children().removeClass('exclude');
      $('.page a.delete').fadeIn(0);
      $('.page a.published').fadeIn(0);
      $('.page a.notpublished').fadeIn(0);
      $('.page a.viewedit').fadeIn(0);
      $('.page a.move').fadeIn(0);
      
    } else {
      $('.folders span.delete').fadeOut(200);
      $('.folders span.viewedit').fadeOut(200);
      $('.folders span.move').fadeOut(200);
      $(this).parent().addClass('onthemove');
      onthemove = $(this).attr('id').replace('move','');
      $('.folders span.target').fadeIn(0);
      $('.folders .selected').children().children().next('span.target').fadeOut(0);
      $('.folders .selected').children().addClass('exclude');
      $('.page a.delete').fadeOut(0);
      $('.page a.published').fadeOut(0);
      $('.page a.notpublished').fadeOut(0);
      $('.page a.viewedit').fadeOut(0);
      $('.page a.move').fadeOut(0);
      $(this).fadeIn(0);
    }
  });

  $('a.import').click(function(){
    $(this).next('input').trigger("click");
  });

});

$(document).ready(function(){

  $('a.createpage').on( 'click', function( e ) {
    e.preventDefault();
    $('#newpagetitle').dialog('open');
    $("a.createpage").hide();
    $("a.cancel").show();
  });

  $('#docreatepage').on( 'click', function( e ) {
    e.preventDefault();
    $(".insertpoint span.move").hide();
    $(".insertpoint span.create").show();
    $('.insertpoint').css( 'display', 'block' );
    $('#newpagetitle').css( 'display', 'none' );
    mode = 'create';
  });
  
  $(".pageTree").on("click", ".move:not(.accessdisabled)", function( e ) {
    e.preventDefault();
    e.stopPropagation();
    
    if( mode != 'move' ) {
      $(this).parent().addClass("onthemove");
      $('.insertpoint').css( 'display', 'block' );
        
      if( $(this).parent().parent().prev().hasClass("ip") ) {
        $(this).parent().parent().prev().find('.insertpoint').hide();
      }
      
      if( $(this).parent().parent().next().hasClass("ip") ) {
        $(this).parent().parent().next().find('.insertpoint').hide();
      }

      mode = 'move';
      id = $(this).attr('id').replace('movepage-','');
        
      $(this).parent().parent().children('ul').children('li').find('.insertpoint').css( 'display', 'none' );
      $('.onthemove').parent().find('.insertpoint').hide();
      if ( $('.insertpoint').is(':visible') ) {
        $(".insertpoint span.move").show();
        $(".insertpoint span.create").hide();
        $('li .page').addClass('nohover');
      }
    } else {
    
      newid = $(this).attr('id').replace('movepage-','');
      if( id == newid ) {
        $(this).parent().removeClass("onthemove");
        $('.insertpoint').css( 'display', 'none' );
        $('li .page').removeClass('nohover');
        mode = '';
        id = '';
      }
      else {
        $(this).parent().addClass("onthemove");
        $('#movepage-'+id).parent().removeClass("onthemove");
        $('.insertpoint').css( 'display', 'block' );
        
        if( $(this).parent().parent().prev().hasClass("ip") ) {
          $(this).parent().parent().prev().find('.insertpoint').hide();
        }
        
        if( $(this).parent().parent().next().hasClass("ip") ) {
          $(this).parent().parent().next().find('.insertpoint').hide();
        }
        
        $(this).parent().parent().children('ul').children('li').find('.insertpoint').css( 'display', 'none' );
        id = newid;
      }
    }
  });

  $('.pageTree').on( 'click', '.deletepage:not(.accessdisabled)', function( e ) {
    e.preventDefault();
    var title = $(this).parent().children('.name').html();
    id = $(this).attr('id').replace('deletepage-','');
    item = $(this).parent();
    
    //hidePageStuff();
    
    $('#deletepagepopup').html( '<p>Are you sure you want to delete \''+title+'\'?</p>' );

  });

  $("#deletepagepopup").dialog({
      resizable: true,
      autoOpen:false,
      modal: true,
      width:400,
      height:170,
      buttons: {
        'Yes': function() {
          $(this).dialog('close');
          if( item ) {
            $.ajax({
              type: "POST",
              url: "../ajax/",
              data: { action: "page-delete", id: id }
            })
            .done(function( msg ) {
              if( msg ) {
                window.location.href = './';
              }
            });
          }
        }, 
        No: function() {
        $(this).dialog('close');
        } 
      }
    });

  $("#deletepostpopup").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:170,
    buttons: {
      'Yes': function() {
        $(this).dialog('close');
        if( item ) {
          $.ajax({
            type: "POST",
            url: "../ajax/",
            data: { action: "post-delete", id: item }
          })
          .done(function( msg ) {
            if( msg ) {
              var ret = jQuery.parseJSON( msg );
              $('.newslistinner').html( ret.posts );
            }
          });
        }
      }, 
      No: function() {
      $(this).dialog('close');
      } 
    }
  });

  $("#deletemediapopup").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:170,
    buttons: {
      'Yes': function() {
        $(this).dialog('close');
        if( item ) {
          $.ajax({
            type: "POST",
            url: "../ajax/",
            data: { action: "media-delete", id: item }
          })
          .done(function( msg ) {
            if( msg ) {
              var ret = jQuery.parseJSON( msg );
              $('.medialistinner').html( ret.media );
            }
          });
        }
      }, 
      No: function() {
      $(this).dialog('close');
      } 
    }
  });
  
  $("#deletedocpopup").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:170,
    buttons: {
      'Yes': function() {
        if( item ) {
          $.ajax({
            type: "POST",
            url: "../ajax/",
            data: { action: "download-delete", id: item }
          })
          .done(function( msg ) {
            if( msg ) {
              var ret = jQuery.parseJSON( msg );
              $('.doclistinner').html( ret.downloads );
            }
          });
          $(this).dialog('close');
        }
      }, 
      No: function() {
      $(this).dialog('close');
      } 
    }
  });

  $("#deletedownloadpopup").dialog({
    resizable: true,
    autoOpen:false,
    modal: true,
    width:400,
    height:170,
    buttons: {
      'Yes': function() {
        $(this).dialog('close');
        if( item ) {
          $.ajax({
            type: "POST",
            url: "../ajax/",
            data: { action: "download-delete", id: item }
          })
          .done(function( msg ) {
            if( msg ) {
              $('.doclistinner').html( msg );
            }
          });
        }
      }, 
      No: function() {
      $(this).dialog('close');
      } 
    }
  });

  $('.pageTree').on( 'click', '.insertpoint', function( e ) {
    e.preventDefault();
    if( mode == 'create' ) {
      $("a.createpage").show();
      $("a.cancel").hide();
      $.ajax({
        type: "POST",
        url: "../ajax/",
        data: { action: "page-create", title: $('#pagetitle').val(), at: $(this).attr('id') }
      })
      .done(function( msg ) {
        var ret = jQuery.parseJSON( msg );
        if( ret.done == 1 ) {
          $('.pageTree .draggable').html( ret.data );
          $('#pagetitle').val('');
          return;
        }
        alert('Error');
      });
    } else {

      var movingPages = ( $('#p'+id+" li div.page").length - $('#p'+id+" li div.insertpoint").length );
      $('.loadingoverlay').show();
      $('.loading').show();
      $('.loading .loading-text').html('<p>Currently moving '+movingPages+' pages...</p>');

      mode = '';
      $.ajax({
        type: "POST",
        url: "../ajax/",
        data: { action: "page-move", id: id, at: $(this).attr('id') }
      })
      .done(function( msg ) {
        var ret = jQuery.parseJSON( msg );
        if( ret.done == 1 ) {
          $('.pageTree .draggable').html( ret.data );
          $('.loadingoverlay').hide();
          $('.loading').hide();
          return;
        }
        alert('Error');
      });
    }
  });
  
  $('.pageTree').on( 'click', '.togglepagestatus:not(.accessdisabled)', function( e ) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: "../ajax/",
        data: { action: "page-status", id: $(this).attr('id').replace('statuspage-','') }
      })
      .done(function( msg ) {
        var ret = jQuery.parseJSON( msg );
        if( ret.done == 1 ) {
          $('.pageTree .draggable').html( ret.data );
          return;
        }
        alert('Error');
      });
  });
  
  // Posts
  $('.createpage').on( 'click', '.createpost', function( e ) {
    e.preventDefault();
    $('#newposttitle').dialog('open');
  });
  
  $('.createpage').on( 'click', '.createfolder', function( e ) {
    e.preventDefault();
    $('#newfoldertitle').dialog('open');
  });

  $('.creategroup').on( 'click', function( e ) {
    e.preventDefault();
    $('#newgrouptitle').dialog('open');
  });

  foldername = $('.folders .selected .postfolder span.name').html();
  $('.foldername').html( foldername );
  
  $('.folders').on( 'click', '.postfolder', function() {

    foldername = $(this).children('span.name').html();

    if(typeof foldername == 'undefined'){
      $('.foldername').html( '&nbsp;' );
    } else {
      $('.foldername').html( foldername );
    }
  
    if( onthemove ) {
      postfolder = $(this).attr('id').replace('folder','');
      if( postfolder == onthemove ) {
        $('span.target').fadeOut(0);
        $('.onthemove').removeClass('onthemove');
        onthemove = 0;
        $('span.delete').fadeIn(200);
        $('span.viewedit').fadeIn(200);
        $('span.move').fadeIn(200);
      } else if( $( '#folder'+onthemove ).parent().has( '#'+$(this).attr('id') ).length >= 1 ) {
        alert('You can\'t move the item there');
      } else {
        $.ajax({
          type: "POST",
          url: "../ajax/",
          data: { action: "post-move", id: onthemove, at: postfolder }
        })
        .done(function( msg ) {
          onthemove = 0;
          if( msg ) {
            var ret = jQuery.parseJSON( msg );
            $('.folders').html( ret.folderTree );
            $('.newslistinner').html( ret.posts );
          } else {
            alert('Error');
          }
        });
      }
    } else {
      $('.folders a').parent().removeClass('selected');
      $(this).parent().addClass('selected');
      postfolder = $(this).attr('id').replace('folder','');
      
      // Load posts for folder
      $.ajax({
        type: "POST",
        url: "../ajax/",
        data: { action: "post-loadByFolder", id: postfolder }
      })
      .done(function( msg ) {
        $('.newslistinner').html( msg );
      });
      
      // Load create buttons for folder
      $.ajax({
        type: "POST",
        url: "../ajax/",
        data: { action: "post-loadCreateButtons", id: postfolder }
      })
      .done(function( msg ) {
        $('.createpage').html( msg );
        return;
      });
    }
  });
  
  $('.newslist').on( 'click', '.togglepoststatus:not(.accessdisabled)', function( e ) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: "../ajax/",
        data: { action: "post-status", id: $(this).attr('id').replace('status','') }
      })
      .done(function( msg ) {
        var ret = jQuery.parseJSON( msg );
        if( ret.done == 1 ) {
          $('.newslistinner').html( ret.data );
          return;
        }
        alert('Error');
      });
  });
  
  // Media
  //$('.createpost').on( 'click', function( e ) {
  //  e.preventDefault();
  //  $('#newposttitle').dialog('open');
  //});
  
  $('.createpage').on( 'click', '.createmediafolder', function( e ) {
    e.preventDefault();
    $('#newmediafoldertitle').dialog('open');
  });

  // if( $('.mediafolders .selected').length ) {
  //   
  //   $('.mediafolders .selected').parents('ul').addClass('open');
  //   $('.mediafolders .selected').parents('ul').show();
  // }
  
  // $('.folders').on( 'click', '.mediafolder .expand', function( e ) {
  //   e.stopPropagation();
  //   e.preventDefault();
  //   $(this).toggleClass( "open" );
  //   $(this).parent().parent().children('ul').slideToggle();
  // });

  foldername = $('.folders .selected .mediafolder span.name').html();
  $('.foldername').html( foldername );
  
  $('.folders').on( 'click', '.mediafolder', function() {

    foldername = $(this).children('span.name').html();

    if(typeof foldername == 'undefined'){
      $('.foldername').html( '&nbsp;' );
    } else {
      $('.foldername').html( foldername );
    }
  
    if( onthemove ) {
      mediafolder = $(this).attr('id').replace('folder','');
      $('#fallbackparentid').val( mediafolder );
      if( mediafolder == onthemove ) {
        $('span.target').fadeOut(0);
        $('.onthemove').removeClass('onthemove');
        onthemove = 0;
        $('span.delete').fadeIn(200);
        $('span.viewedit').fadeIn(200);
        $('span.move').fadeIn(200);
      } else if( $( '#folder'+onthemove ).parent().has( '#'+$(this).attr('id') ).length >= 1 ) {
        alert('You can\'t move the item there');
      } else {
        $.ajax({
          type: "POST",
          url: "../ajax/",
          data: { action: "media-move", id: onthemove, at: mediafolder }
        })
        .done(function( msg ) {
          onthemove = 0;
          if( msg ) {
            var ret = jQuery.parseJSON( msg );
            $('.folders').html( ret.folderTree );
            $('.medialistinner').html( ret.media );
          } else {
            alert('Error');
          }
        });
      }
    }
    else {
      $('.folders a').parent().removeClass('selected');
      $(this).parent().addClass('selected');
      mediafolder = $(this).attr('id').replace('folder','');
      $('#fallbackparentid').val( mediafolder );
      $('.dropzone .dz-success').remove();
      $('.dropzone .dz-error').remove();
      loadMediaByFolder();
      
      // Load create buttons for folder
      $.ajax({
        type: "POST",
        url: "../ajax/",
        data: { action: "media-loadCreateButtons", id: mediafolder }
      })
      .done(function( msg ) {
        $('.createpage').html( msg );
        return;
      });
      
      // Load dropzone for folder
      $.ajax({
        type: "POST",
        url: "../ajax/",
        data: { action: "media-loadDropzone", id: mediafolder }
      })
      .done(function( msg ) {
        $('.dropzonecontainer').html( msg );
        initMediaDropzone();
        return;
      });
    }
  });
  
  // Downloads    
  $('.createpage').on( 'click', '.createdownloadfolder', function( e ) {
    e.preventDefault();
    $('#newdownloadfoldertitle').dialog('open');
  });
    
  if( $('.downloadfolders .selected').length ) {
    
    $('.downloadfolders .selected').parents('ul').addClass('open');
    $('.downloadfolders .selected').parents('ul').show();
  }
  
  // $('.folders').on( 'click', '.downloadfolder .expand', function( e ) {
  //   
  //   e.stopPropagation();
  //   e.preventDefault();
  //   $(this).toggleClass( "open" );
  //   $(this).parent().parent().children('ul').slideToggle();
  // });

  foldername = $('.folders .selected .downloadfolder span.name').html();
  $('.foldername').html( foldername );
    
  $('.folders').on( 'click', '.downloadfolder', function( e ) {
    
    e.preventDefault();

    //alert($(this).children('span.name').html());
    foldername = $(this).children('span.name').html();

    if(typeof foldername == 'undefined'){
      $('.foldername').html( '&nbsp;' );
    } else {
      $('.foldername').html( foldername );
    }
  
    if( onthemove ) {
      downloadfolder = $(this).attr('id').replace('folder','');
      $('#fallbackparentid').val( downloadfolder );
      if( downloadfolder == onthemove ) {
        $('span.target').fadeOut(0);
        $('.onthemove').removeClass('onthemove');
        onthemove = 0;
        $('span.delete').fadeIn(200);
        $('span.viewedit').fadeIn(200);
        $('span.move').fadeIn(200);
      } else if( $( '#folder'+onthemove ).parent().has( '#'+$(this).attr('id') ).length >= 1 ) {
        alert('You can\'t move the item there');
      } else {
        $.ajax({
          type: "POST",
          url: "../ajax/",
          data: { action: "download-move", id: onthemove, at: downloadfolder }
        })
        .done(function( msg ) {
          onthemove = 0;
          if( msg ) {
            var ret = jQuery.parseJSON( msg );
            $('.folders').html( ret.folderTree );
            $('.doclistinner').html( ret.downloads );
          } else {
            alert('Error');
          }
        });
      }
    } else {
      $('.folders a').parent().removeClass('selected');
      $(this).parent().addClass('selected');
      downloadfolder = $(this).attr('id').replace('folder','');
      $('#fallbackparentid').val( downloadfolder );

      // Load downloads for folder
      $.ajax({
        type: "POST",
        url: "../ajax/",
        data: { action: "download-loadByFolder", id: downloadfolder }
      })
      .done(function( msg ) {
        $('.doclistinner').html( msg );
      });
      
      // Load create buttons for folder
      $.ajax({
        type: "POST",
        url: "../ajax/",
        data: { action: "download-loadCreateButtons", id: downloadfolder }
      })
      .done(function( msg ) {
        $('.createpage').html( msg );
        return;
      });
      
      // Load dropzone for folder
      $.ajax({
        type: "POST",
        url: "../ajax/",
        data: { action: "download-loadDropzone", id: downloadfolder }
      })
      .done(function( msg ) {
        $('.dropzonecontainer').html( msg );
        initDownloadDropzone();
        return;
      });
    }
  });
  
  $('.doclist').on( 'click', '.toggledownloadstatus:not(.accessdisabled)', function( e ) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: "../ajax/",
        data: { action: "download-status", id: $(this).attr('id').replace('status','') }
      })
      .done(function( msg ) {
        var ret = jQuery.parseJSON( msg );
        if( ret.done == 1 ) {
          $('.doclistinner').html( ret.data );
          return;
        }
        alert('Error');
      });
  });
  
  $('body').keydown(function(e) {
    if( e.ctrlKey == false && e.altKey == false ) {
      var tag = e.target.tagName.toLowerCase();
      if ( (e.which !== 0) && (tag != 'input') && (tag != 'textarea') && (tag != 'div') && (e.which != 13) && (e.which != 27) && (e.which != 42) && (e.which != 44) && (e.which != 112) && (e.which != 113) && (e.which != 114) && (e.which != 115) && (e.which != 116) && (e.which != 117) && (e.which != 122) && (e.which != 123)) {    
        $('.searchme').fadeIn(200);
        $('.searchme input').focus();
      } else if (e.which == 27){
        $('.searchme').fadeOut(200);
        $('.searchme input').val('');
      }
    }
  });
  
  if( $('.searchme input').length ) {
    $('body').keyup(function(e) {
      var search = $('.searchme input').val();
      if( search.length >= 3 ) {
        $.ajax({
          type: "POST",
          url: cmsURL+"ajax/",
          data: { action: "search-adminSearch", search: search }
        })
        .done(function( msg ) {
          if( msg ) {
            $('.results1').html( msg );
          } else {
            $('.results1').html( '<p class="noresults">Sorry, we\'ve not found any content matching ' + search + '</p>' );
          }
        });
      }
      else if( search.length == 0 ) {
        $('.searchme').fadeOut(200);
        $('.searchme input').val('');
      }
      else {
        $('.results1').html( '<p class="tooshort">Please enter a longer search term</p>' );
      }
    });    
  }
  
  $('.searchme .close').on('click', function(e) {
    e.preventDefault();
    $('.searchme').fadeOut(200);
    $('.searchme input').val('');
  });

      $('input:checkbox').after("<span class='checkbox'></span>");

    $('input:checkbox:checked').each(function(){
      $(this).next('span').addClass("checked");
    });



    $(document).on('change', 'input:checkbox', function(){
      if($(this).is(":checked")) {
        $(this).next('span').addClass("checked");
      } else {
        
        $(this).next('span').removeClass("checked");
      }
    });

    $(document).on('focus', 'input:checkbox', function(){
      $(this).next('span').addClass("focussed");
    });
    $(document).on('blur', 'input:checkbox', function(){
        $(this).next('span').removeClass("focussed");
    });

    $('input#hidefromsitemap:checkbox:checked').each(function(){
      $('input#hidefromnavigation').attr('checked', 'checked');
    });

    $('input:checkbox').on('change', function(){
      if(($('#hidefromnavigation').is(":checked")) && ($('#hidefromsitemap').is(":checked"))) {
        $('input#hidefromnavigation').attr('checked', 'checked');
        $('input#hidefromnavigation').next('span').addClass('checked');
        $('input#hidefromsitemap').attr('checked', 'checked');
        $('input#hidefromsitemap').next('span').addClass('checked');
      }
      else if (($('#hidefromsitemap').is(":checked"))) {
        $('input#hidefromnavigation').attr('checked', 'checked');
        $('input#hidefromnavigation').next('span').addClass('checked');
        $('input#hidefromsitemap').attr('checked', 'checked');
        $('input#hidefromsitemap').next('span').addClass('checked');
      }
      else if ($('#hidefromnavigation').is(":checked")) {
        $('input#hidefromnavigation').attr('checked', 'checked');
        $('input#hidefromnavigation').next('span').addClass('checked');
      }
    });

    $('.security input:checkbox').on('click', function(){
    
      if( $(this).attr('id') == 'security-allUserAccess' ) {
      
        $('.security-group').prop('checked', false); 
      } else {

        $('#security-allUserAccess').prop('checked', !$('.security-group').filter(':checked').length);  
      }
      
    });
  
  $('#imageFolder, .adfimageFolder').on( 'change', function() {
    
    mediafolder = $(this).children(":selected").attr("id").replace('folder','');
    loadMediaByFolder( '', 'selectimage' );
  });
  
  $('#seecmsredirectlink').on("click", function( e ){
      
    e.preventDefault();
    $('#selectseecmsredirectlink').dialog('open');
    $('#selectseecmsredirectlink .adflinks').show();
  });
  
  $("#seecmsremoveredirect").on("click", function( e ){
      
    e.preventDefault();
    $('#redirect').val( '' );
    $('#seecmsredirectlink').html( "Select a link" );
    $('#seecmsremoveredirect').html( "" );
  });
  
  $(".selectseecmsredirectlink").dialog({
      resizable: true,
      autoOpen:false,
      modal: true,
      width:800,
      height:600,
      buttons: {
        'Done': function() {
            if( $('.adflinks .emails').is(":visible") ) {
              selectedItem = 'email-' + $('.adflinks #emaillink').val();
              selectedItemLabel = $('.adflinks #emaillink').val();
            }
          
            if( $('.adflinks .externals').is(":visible") ) {
              selectedItem = 'link-' + $('.adflinks #weblink').val();
              selectedItemLabel = $('.adflinks #weblink').val();
            }
            
            $('#redirect').val( selectedItem );
            $('#seecmsredirectlink').html( 'Redirect to: ' + selectedItemLabel );
            $('#seecmsremoveredirect').html( "Remove redirect" );
            $(this).dialog('close');
        }, 
        Cancel: function() {
          $(this).dialog('close');
        } 
      }
    });
  
  /* -------------------------------- */
  /* ------ Start: Page clones ------ */
  /* -------------------------------- */
  
  $('#seecmsclonelink').on("click", function( e ){
      
    e.preventDefault();
    $('#selectseecmsclonelink').dialog('open');
    $('#selectseecmsclonelink .adflinks').show();
  });
  
  $("#seecmsremoveclone").on("click", function( e ){
      
    e.preventDefault();
    $('#clone').val( '' );
    $('#seecmsrclonelink').html( "Select a page" );
    $('#seecmsremoveclone').html( "" );
  });
  
  $(".selectseecmsclonelink").dialog({
      resizable: true,
      autoOpen:false,
      modal: true,
      width:800,
      height:600,
      buttons: {
        'Done': function() {
            
            $('#clone').val( selectedItem );
            $('#seecmsclonelink').html( 'Clone from: ' + selectedItemLabel );
            $('#seecmsremoveclone').html( "Remove cloning" );
            $(this).dialog('close');
        }, 
        Cancel: function() {
          $(this).dialog('close');
        } 
      }
    });
  
  /* -------------------------------- */
  /* ------ End: Page clones ------ */
  /* -------------------------------- */
  
  $( "body" ).on('change', ".adfselectLink select", function(){
    $( ".adfselectLink select option:selected").each(function(){
      if($(this).attr("value")=="page"){
        $(this).parent().parent().parent().siblings(".hidden").children("div").fadeOut(0);
        $(this).parent().parent().parent().siblings(".hidden").children(".pages").fadeIn(200);
      }
      else if($(this).attr("value")=="post"){
        $(this).parent().parent().parent().siblings(".hidden").children(".hidden div").fadeOut(0);
        $(this).parent().parent().parent().siblings(".hidden").children(".posts").fadeIn(200);
      }
      else if($(this).attr("value")=="email"){
        $(this).parent().parent().parent().siblings(".hidden").children(".hidden div").fadeOut(0);
        $(this).parent().parent().parent().siblings(".hidden").children(".emails").fadeIn(200);
      }
      else if($(this).attr("value")=="external"){
        $(this).parent().parent().parent().siblings(".hidden").children(".hidden div").fadeOut(0);
        $(this).parent().parent().parent().siblings(".hidden").children(".externals").fadeIn(200);
      }
      else if($(this).attr("value")=="download"){
        $(this).parent().parent().parent().siblings(".hidden").children(".hidden div").fadeOut(0);
        $(this).parent().parent().parent().siblings(".hidden").children(".downloads").fadeIn(200);
      }
  	});
  }).change();

  $(".adflinks").on( 'click', 'a',function(e){
  	e.preventDefault();
    selectedItem = $(this).attr('id');
    selectedItemLabel = $(this).html();
  	$(".finalstep").fadeIn(500);
  	$("html, body").animate({ scrollTop: $(document).height() }, 500);
  });

  $(".adflinks li a").on('click', function(e){
    e.preventDefault();
    if($(this, 'li a').parent().hasClass('folder')) {

    } else {
      $('.selected').removeClass('selected');
      $(this).parent().addClass('selected');
    }
  });
  
  $(".adflinks .pages li a").on('click', function(e){
    e.preventDefault();
    selectedItem = $(this).attr('id');
    selectedItemLabel = $(this).html();
    $('.selected').removeClass('selected');
    $(this).addClass('selected');
  });
  
  $(".adflinks li span.arrow").on('click', function(){
    if($(this).parent().hasClass('expand')) {
      $(this).parent().children('ul').fadeIn(0);
      $(this).parent().addClass('expanded');
      $(this).parent().removeClass('expand');
    }
    else if($(this).parent().hasClass('expanded')) {
      $(this).parent().children('ul').fadeOut(0); 
      $(this).parent().addClass('expand');
      $(this).parent().removeClass('expanded');
    }
    else if($(this).parent().hasClass('downloadexpand')) {
      $(this).parent().children('ul').fadeIn(0);
      $(this).parent().addClass('downloadexpanded');
      $(this).parent().removeClass('downloadexpand');
    }
    else if($(this).parent().hasClass('downloadexpanded')) {
      $(this).parent().children('ul').fadeOut(0); 
      $(this).parent().addClass('downloadexpand');
      $(this).parent().removeClass('downloadexpanded');
    }
  });
  
  $(".adflinks .downloads.folders li.folder").on('click', function(){

    $(this).children('ul').fadeIn(0);
    $(this).addClass('downloadexpanded');

    if($(this).hasClass('downexpanded')) {
      $(this).children('ul').fadeOut(0); 
      $(this).removeClass('downexpanded');
    }
  });
  
  if (typeof(loadMediaByFolder) == 'function') {
    if( !skipInitialMediaLoad ) {
      loadMediaByFolder( '', 'selectimage' );
      loadMediaFolders( '', 'option', 'imageFolder' );
    }
  }
  
  
  $('#seecmspostmedia').on("click", function( e ){
      
    e.preventDefault();
    $('#selectpostimage').dialog('open');
  });
  
  $('#seecmspostremovemedia').on("click", function( e ){
      
    e.preventDefault();
    $('#media_id').val( 0 );
    $('.postthumbnail').html( '' );
  });


  $(".selectpostimage").dialog({
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

  $('body').on( 'click', '.selectpostimage .adfimages .image', function( e ) {
    e.preventDefault();
    id = $(this).attr('id').replace('i','');
    $('#media_id').val( id );
    $('.postthumbnail').html( $(this).html() );
    $(".selectpostimage").dialog('close');
  });
  
  $('.selected > .postfolder').click();
  
  /* ----- START: SeeCMS Multisite Login ----- */
  var multisitelogincomplete = 0;
  
  if( typeof multisite === 'object' ) {
  
    $('form#SeeCMSLoginForm').on( 'submit', function( e ) {
      
      e.preventDefault();
          
      var returned = 0;  
      var sending = 0;  
      var x;
      for( x in multisite ){
        sending++;
      }
      
      for ( x in multisite ) {
        
        $.ajax({
          type: "POST",
          url: window.location.href.replace( window.location.host, multisite[x]['domain'] ),
          data: { 'seeform-SeeCMSAdminAuthentication-login-0': 1, remotelogin: true, email: $('form#SeeCMSLoginForm input#email').val(), password: $('form#SeeCMSLoginForm input#password').val() },
          xhrFields: { withCredentials: true }
        })
        .done(function( msg ) {
          if( msg == 'Done' ) {
            returned++;
            if( returned == sending ) {
              
              window.location.reload();
            }
          } else if( msg == 'Error' ) {
            
            if( !$('form#SeeCMSLoginForm .seeformerrors').length ) {
              $('form#SeeCMSLoginForm').prepend( '<p class="seeformerrors">Oops. Your login details are incorrect.</p>' );
            }
          }
        });
      }
      
      return false;
    });
  
  }
  /* ------ END: SeeCMS Multisite Login ------ */
  
  /* ----- START: SeeCMS Multisite Visit site ----- */
  var sites = $( '.visitmultisite' );
  if( sites.length ) {
    
    $('#visitwebsite').on('click', function( e ) {
      e.preventDefault();
      $( '.visitmultisite' ).toggle();
    });
  }
  /* ------ END: SeeCMS Multisite Visit site ------ */
  
  /* ------ START: Stop accessdisabled links being clickable ------ */
  $( '.accessdisabled' ).unbind();
  $( '.accessdisabled' ).off();
  $( document ).on( 'click', '.accessdisabled', function( e ) {

    e.preventDefault();
    
  });
  /* ------ END: Stop accessdisabled links being clickable ------ */
  
  /* ------ START: Submit page for approval ------ */
  $('.seecmsrequestapproval .seecmsfasubmit').on( 'click',  function( e ) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: "../../ajax/",
        data: { action: "content-requestApproval", objectType: $('#requestapproval-objecttype').val(), objectID: $('#requestapproval-objectid').val(), approverID: $('#requestapproval-approver').val() }
      })
      .done(function( msg ) {
        var ret = jQuery.parseJSON( msg );
        if( ret.done == 1 ) {
          window.location.href = './?id=' + $('#requestapproval-objectid').val();
        }
        if( msg.error ){
          alert('Error: '+msg.error);
        }
      });
  });
  /* ------ END: Submit page for approval ------ */
  
});