// SeeCMS is a website content management system
// @author See Green <http://www.seegreen.uk>
// @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
// @copyright 2015 See Green Media Ltd

var postfolder = 0;
var downloadfolder = 0;
var mediafolder = 0;

function hidePageStuff() {

  $('.insertpoint').css( 'display', 'none' );
  $('#newpagetitle').css( 'display', 'none' );
  $('#deletepagepopup').css( 'display', 'none' );

}

function loadMediaByFolder( pathModifier, mode ) {

  pathModifier = (( pathModifier === undefined ) ? '' : pathModifier );
  
  if( cmsURL !== undefined ) {
    url = cmsURL + 'ajax/';
  } else {
    url = pathModifier + "../ajax/";
  }
  
  $.ajax({
    type: "POST",
    url: url,
    data: { action: "media-loadByFolder", id: mediafolder, mode: mode }
  })
  .done(function( msg ) {
    $('.medialistinner').html( msg );
  });
}

function loadMediaFolders( pathModifier, mode, id ) {

  pathModifier = (( pathModifier === undefined ) ? '' : pathModifier );
  
  if( cmsURL !== undefined ) {
    url = cmsURL + 'ajax/';
  } else {
    url = pathModifier + "../ajax/";
  }
  
  $.ajax({
    type: "POST",
    url: url,
    data: { action: "media-folderTree", mode: mode }
  })
  .done(function( msg ) {
    
    $('#'+id).html( msg );
    $('.adf'+id).html( msg );
  });
}

function loadDownloadsByFolder() {
  
  if( cmsURL !== undefined ) {
    url = cmsURL + 'ajax/';
  } else {
    url = "../ajax/";
  }

  $.ajax({
    type: "POST",
    url: url,
    data: { action: "download-loadByFolder", id: downloadfolder }
  })
  .done(function( msg ) {
    $('.doclistinner').html( msg );
  });
}