// SeeCMS is a website content management system
// @author See Green <http://www.seegreen.uk>
// @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
// @copyright 2015 See Green Media Ltd

var currentContainerID;

$(function() {

  $('body').on( 'click', '.editableApply', function( e ) {
    e.preventDefault();
    id = $(this).attr('id').replace( 'editableApply', '' );
    currentContainerID = 'editable'+id;
    
    $.ajax({ type: "POST", url: ajaxPath, data: { action: "content-apply", containerID: id, objectType: editObjectType, objectID: editObjectID, language: language } })
    .done(function( msg ) { if( msg ) { reloadContent( msg ); if (typeof customADFReload === "function") { customADFReload( id ); } return; } alert( 'Error' ); });
    
  });

  $('body').on( 'click', '.editableDiscard', function( e ) {
    e.preventDefault();
    id = $(this).attr('id').replace( 'editableDiscard', '' );
    currentContainerID = 'editable'+id;
    
    $.ajax({ type: "POST", url: ajaxPath, data: { action: "content-discard", containerID: id, objectType: editObjectType, objectID: editObjectID, language: language } })
    .done(function( msg ) { if( msg ) { reloadContent( msg ); if (typeof customADFReload === "function") { customADFReload( id ); } return; } alert( 'Error' ); });
    
  });
  
  $('#imageFolder, .adfimageFolder').on( 'change', function() {
    mediafolder = $(this).children(":selected").attr("id").replace('folder','');
    loadMediaByFolder( '', 'selectimage' );
  });

  $('body').on( 'click', '.editcontentRichText', function( e ) {
    e.preventDefault();
    currentContainerID = $(this).attr('id');
    
    // Sort superparts
    $( '.'+currentContainerID+' div[data-superpart]' ).each( function() {
      $(this).parent().prepend( '{{'+$(this).attr('data-superpart')+'}}' );
      $(this).remove();
    });
    
    var content = $('.'+currentContainerID).html();
    content = multisiteUpdate( content, 0 );
    content = replaceAll( content, 'href="' + siteURL, 'href="/' );
    
    //tinyMCE.activeEditor.setContent( content );
    tinyMCE.get('seecmsMainRTE').setContent( content );
    //$("#editableRTcontent").dialog( "option", "width", $(this).parent().width() );
    $('#editableRTcontent').dialog('open');
  });

  $('body').on( 'click', '.editcontentADF', function( e ) {
    e.preventDefault();
    currentContainerID = $(this).attr('id');
    id = $(this).attr('id').replace('editable','');
    $('#editablecontent'+id).dialog('open');
  });

  $('body').on( 'click', '.adfaddanotherset', function( e ) {
    e.preventDefault();
    id = $(this).attr('id').replace('adfaddanotherset','');
    if( adflimit[id] == 0 || adflimit[id] > adfsetsinuse[id] ) {
      htmlappend = replaceAll( eval( 'emptyadf' + id ), 'SEECMSADFSET', adfsets[id] + 1 );
      adfsets[id] += 1;
      adfsetsinuse[id] += 1;
      $( "#editablecontent" + id + " .editableADFcontentinner" ).append( htmlappend );
      if (typeof(tinyMCE) != "undefined") {
        initialiseSmallTinyMCE();
      }
    } else {
      alert( 'Limit reached' );
    }
  });

  $('body').on( 'click', '.deleteadfset', function( e ) {
    e.preventDefault();
    id = $(this).attr('id').replace('delete-','');
    if (typeof(tinyMCE) != "undefined") {
      tinymce.remove('#'+id+' div');
    }
    $( "#" + id ).remove();
    adfsetsinuse[$(this).attr('data-set-container')] -= 1;
  });
  
  var currentADFimage;
  var currentADFimageSize;

  $('body').on( 'click', '.adfselectimage', function( e ) {
    e.preventDefault();
    currentADFimage = $(this).attr('id');
    currentADFimageSize = $(this).attr('data-size');
    scrollTopADF = $(this).parent().parent().parent().parent().scrollTop();
    $('.adfset').hide();
    $('.adfaddanotherset').hide();
    $('.adflinks').hide();
    $('.adfimages').show();
    $('.adfmediafolder').hide();
  });

  $('body').on( 'click', '.adfremoveimage', function( e ) {
    e.preventDefault();
    currentADFimage = $(this).attr('id').replace('-remove','');
    $('.'+currentADFimage).html( '' );
    $('input[name=\''+currentADFimage+'\']').val( '' );
  });

  $('body').on( 'click', '.adfimages .image', function( e ) {
    e.preventDefault();
    id = $(this).attr('id').replace('i','');
    $('input[name=\''+currentADFimage+'\']').val( id );
    $('.'+currentADFimage).html( $(this).html().replace('-139-139-','-' + currentADFimageSize + '-') );
    $('.adfimages').hide();
    $('.adflinks').hide();
    $('.adfmediafolder').hide();
    $('.adfset').show();
    $('.adfaddanotherset').show();
    $('.editableADFcontent').scrollTop( scrollTopADF );
  });
  
  var currentADFmediaFolder;

  $('body').on( 'click', '.adfselectmediafolder', function( e ) {
    e.preventDefault();
    currentADFmediaFolder = $(this).attr('id');
    scrollTopADF = $(this).parent().parent().parent().parent().scrollTop();
    $(this).parent().parent().parent().children('.adfmediafolder').children(".selectMediaFolder").children("p").children(".adfimageFolder").children("option[id=folder"+$('input[name=\''+currentADFmediaFolder+'\']').val()+"]").attr( 'selected', true );
    $(this).parent().parent().children('.adfset').hide();
    $(this).parent().parent().parent().children('p').children('.adfaddanotherset').hide();
    $(this).parent().parent().parent().children('.adflinks').hide();
    $(this).parent().parent().parent().children('.adfimages').hide();
    $(this).parent().parent().parent().children('.adfmediafolder').show();
  });

  $('body').on( 'click', '#selectMediaFolderButton', function( e ) {
    e.preventDefault();
    id = $(this).parent().parent().children('.selectMediaFolder').children('p').children(".adfimageFolder").children("option:selected").attr('id').replace('folder','');
    $('input[name=\''+currentADFmediaFolder+'\']').val( id );
    $('.'+currentADFmediaFolder).html( $(this).parent().parent().children('.selectMediaFolder').children('p').children(".adfimageFolder").children("option:selected").text() );
    $('.adfimages').hide();
    $('.adflinks').hide();
    $('.adfmediafolder').hide();
    $('.adfset').show();
    $('.adfaddanotherset').show();
    $('.editableADFcontent').scrollTop( scrollTopADF );
  });

  $('body').on( 'click', '.adfremovemediafolder', function( e ) {
    e.preventDefault();
    currentADFmediaFolder = $(this).attr('id').replace('-remove','');
    scrollTopADF = $(this).parent().parent().parent().parent().scrollTop();
    $('input[name=\''+currentADFmediaFolder+'\']').val('');
    $('.'+currentADFmediaFolder).html( '' );
  });

  $('body').on( 'click', '#cancelSelectMediaFolderButton', function( e ) {
    e.preventDefault();
    $('.adfimages').hide();
    $('.adflinks').hide();
    $('.adfmediafolder').hide();
    $('.adfset').show();
    $('.adfaddanotherset').show();
    $('.editableADFcontent').scrollTop( scrollTopADF );
  });
  
  var currentADFlink;
  var scrollTopADF;

  $('body').on( 'click', '.adfselectlink', function( e ) {
    e.preventDefault();
    currentADFlink = $(this).attr('id');
    scrollTopADF = $(this).parent().parent().parent().parent().scrollTop();
    $('.adfset').hide();
    $('.adfaddanotherset').hide();
    $('.adfimages').hide();
    $('.adflinks').show();
    $('.adfmediafolder').hide();
  });

  // Make the editbar hover hightlighting work
  attachEditbarHover();


  $("#editableRTcontent").dialog({
      resizable: true,
      autoOpen:false,
      modal: false,
      width:800,
      height:600,
      buttons: {
        'Done': function() {
        
          var content = tinyMCE.activeEditor.getContent();
          $.ajax({
            type: "POST",
            url: ajaxPath,
            data: { action: "content-edit", content: content, containerID: currentContainerID.replace('editable',''), objectType: editObjectType, objectID: editObjectID, contentType: 'Rich Text', language: language }
          })
          .done(function( msg ) {
            if( msg ) {
              reloadContent( msg );
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
  
  if (typeof(loadMediaByFolder) == 'function') {
    loadMediaByFolder( '', 'selectimage' );
    loadMediaFolders( '', 'option', 'imageFolder' );
  }


  $(".editableADFcontent").dialog({
      resizable: true,
      autoOpen:false,
      modal: true,
      width:800,
      height:600,
      buttons: {
        'Done': function() {
        
          if( $('.adflinks').is(":visible") ) {
          
            if( $('.adflinks .emails').is(":visible") ) {
              selectedItem = 'email-' + $( '#'+currentContainerID.replace('editable','editablecontent')+' .adflinks #emaillink').val();
              var description = "Link: " + $( '#'+currentContainerID.replace('editable','editablecontent')+' .adflinks #emaillink').val();
            } else if( $('.adflinks .externals').is(":visible") ) {
              selectedItem = 'link-' + $( '#'+currentContainerID.replace('editable','editablecontent')+' .adflinks #weblink').val();
              var description = "Link: " + $( '#'+currentContainerID.replace('editable','editablecontent')+' .adflinks #weblink').val();
            } else {
              var description = $('#'+selectedItem).html();
            }
            
            $('input[name=\''+currentADFlink+'\']').val( selectedItem );
            $('.'+currentADFlink).html( description );
            $('.adfimages').hide();
            $('.adflinks').hide();
            $('.adfmediafolder').hide();
            $('.adfset').show();
            $('.adfaddanotherset').show();
            $('.editableADFcontent').scrollTop( scrollTopADF );
            
          } else {
        
            $('.adfimages').hide();
            $('.adflinks').hide();
            $('.adfmediafolder').hide();
            $('.adfset').show();
            $('.adfaddanotherset').show();
            $('.editableADFcontent').scrollTop( scrollTopADF );
            
            if (typeof(tinyMCE) != "undefined") {
              tinyMCE.triggerSave();
            }
            
            content = $( '#adfcontent' + currentContainerID.replace('editable','') ).serialize();

            $.ajax({
              type: "POST",
              url: ajaxPath,
              data: { action: "content-edit", content: content, containerID: currentContainerID.replace('editable',''), objectType: editObjectType, objectID: editObjectID, contentType: 'ADF', language: language, settingsScreen: settingsScreen }
            })
            .done(function( msg ) {
              if( msg ) {
                if( !settingsScreen ) {
                  reloadContent( msg );
                  if (typeof customADFReload === "function") { 
                    customADFReload();
                  }
                } else {
                  alert("Content saved");
                }
                return;
              }
              alert( 'Error' );
            });
            
            $(this).dialog('close');
          }
        }, 
        Cancel: function() {
          $('.adfimages').hide();
          $('.adflinks').hide();
          $('.adfmediafolder').hide();
          $('.adfset').show();
          $('.adfaddanotherset').show();
          $('.editableADFcontent').scrollTop( scrollTopADF );
          $(this).dialog('close');
        } 
      }
    });  

});

function reloadContent( content ) {

  if( content.indexOf("{{") != -1 ) {

    window.location.href = './?preview=1';
  }  

  content = multisiteUpdate( content, 1 );
  content = replaceAll( content, 'href="/', 'href="' + siteURL );
  content = replaceAll( content, 'src="/', 'src="' + siteURL );
  
  
  $('.'+currentContainerID).parent().replaceWith( content );
  $(".editable").removeClass( 'seecmseditbarbackground' );
  attachEditbarHover();
}

function escapeRegExp(string) {
    return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
}

function replaceAll(string, find, replace) {
  return string.replace(new RegExp(escapeRegExp(find), 'g'), replace);
}

function attachEditbarHover() {
  
  $(".editbar").hover(
    function() {
        //mouse over
        $(this).parent().addClass( 'seecmseditbarbackground' );
    }, function() {
        //mouse out
        $(".editable").removeClass( 'seecmseditbarbackground' );
    });
}