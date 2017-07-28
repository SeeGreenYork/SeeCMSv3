// SeeCMS is a website content management system
// @author See Green <http://www.seegreen.uk>
// @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
// @copyright 2015 See Green Media Ltd

var selectedItem;
var selectedItemSRC;
var selectedItemALT;
var args;

$(document).ready(function(){

  args = top.tinymce.activeEditor.windowManager.getParams();

  $(".finalstep").hide();

  $(".selectImage select, .selectLink select").on('change', function(){
    $( "select option:selected").each(function(){
      if($(this).attr("value")=="page"){
        $(".hidden div").fadeOut(0);
        $(".pages").fadeIn(200);
        $(".finalstep").fadeOut(0);
      }
      else if($(this).attr("value")=="post"){
        $(".hidden div").fadeOut(0);
        $(".posts").fadeIn(200);
        $(".finalstep").fadeOut(0);
      }
      else if($(this).attr("value")=="media"){
        $(".hidden div").fadeOut(0);
        $(".medias").fadeIn(200);
        $(".finalstep").fadeOut(0);
      }
      else if($(this).attr("value")=="email"){
        $(".hidden div").fadeOut(0);
        $(".emails").fadeIn(200);
        $(".finalstep").fadeOut(0);
      }
      else if($(this).attr("value")=="external"){
        $(".hidden div").fadeOut(0);
        $(".externals").fadeIn(200);
        $(".finalstep").fadeOut(0);
      }
      else if($(this).attr("value")=="download"){
        $(".hidden div").fadeOut(0);
        $(".downloads").fadeIn(200);
        $(".finalstep").fadeOut(0);
      }
      else if($(this).attr("value")!=""){
        $(".hidden div").fadeOut(0);
        $(".medias").fadeIn(0);
        $(".finalstep").fadeOut(0);
      }
      else{
        $(".hidden div").fadeOut(0);
        $(".finalstep").fadeOut(0);
      }
  	});
  }).change();
  
	$("input[type='text']").on('input', function(e){
		e.preventDefault();
    if($(this, "input[type='text']").val()){
  	 $(".finalstep").fadeIn(500);
  	 $("html, body").animate({ scrollTop: $(document).height() }, 500);	
    } else {
      $(".finalstep").fadeOut(500);
    }
	}).change();

  $(".folders").on( 'click', 'a',function(e){
  	e.preventDefault();
    selectedItem = $(this).attr('id');
    selectedItemSRC = $(this).children('img').attr('src');
    selectedItemALT = $(this).children('img').attr('alt');
    selectedItemName = $(this).children('img').attr('title');
  	$(".finalstep").fadeIn(500);
  	$("html, body").animate({ scrollTop: $(document).height() }, 500);
  });

  $(".folders li a").on('click', function(e){
    e.preventDefault();
    if($(this, 'li a').parent().hasClass('folder')) {

    }
    //else if(($(this, '.pages li a').parent().hasClass('expand')) || ($(this, 'li a').parent().hasClass('expanded'))) {
      //$('.selected').removeClass('selected');
      //$(this).addClass('selected');      
    //}
    else {
      $('.selected').removeClass('selected');
      $(this).parent().addClass('selected');
    }
  });
  
  $(".folders.pages li a").on('click', function(e){
    e.preventDefault();
    selectedItem = $(this).attr('id');
    $('.selected').removeClass('selected');
    $(this).addClass('selected');
  });
  
  $(".folders li span.arrow").on('click', function(){
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

  /* ----- START: Expand and contract download folders in link selector ----- */
  $(".downloads.folders li.folder > a").on('click', function( e ){

    if($(this).parent().hasClass('downloadexpanded')) {
      
      $(this).parent().children('ul').fadeOut(0); 
      $(this).parent().removeClass('downloadexpanded');
    } else {
      
      $(this).parent().children('ul').fadeIn(0);
      $(this).parent().addClass('downloadexpanded');
    }
    
    e.stopPropagation();
  });
  /* ----- END: Expand and contract download folders in link selector ----- */

  $(document).on('click','.hidden a.image', function(){
    $(this).addClass('selected');
    $(this).siblings().removeClass('selected');
    $('.selectmelast').fadeIn(200);
  });

  $(document).on('click','.medialistinner a.image', function(){
    $(this).addClass('selected');
    $(this).siblings().removeClass('selected');
    $('.selectmelast').fadeIn(200);
  });
  
  $.ajax({
    type: "POST",
    url: "../../../ajax/",
    data: { action: "content-findSelectedLink", link: args.link }
  })
  .done(function( msg ) {
    if( msg ) {
      var ret = jQuery.parseJSON( msg );
      $("select").val(ret.type);
      $("select").trigger('change');
      selectedItem = ret.id;
      if( ret.type == 'external' ) {
        $('#weblink').val( ret.id );
      } else if( ret.type == 'email' ) {
        $('#emaillink').val( ret.id );
      } else {
        $('#'+selectedItem).addClass('selected');
      }
    }
  });
  
});

function insertContent( link, name, type ) {

  var rex = /(<a([^>]*)>)/ig;
  var content = args.content.replace(rex , "");
  
  content = replaceAll( content, '<p>', '<p>' + link );
  content = replaceAll( content, '<li>', '<li>' + link );
  content = replaceAll( content, '<h1>', '<h1>' + link );
  content = replaceAll( content, '<h2>', '<h2>' + link );
  content = replaceAll( content, '<h3>', '<h3>' + link );
  content = replaceAll( content, '<h4>', '<h4>' + link );
  content = replaceAll( content, '<h5>', '<h5>' + link );
  content = replaceAll( content, '<h6>', '<h6>' + link );
  content = replaceAll( content, '</p>', '</a></p>' );
  content = replaceAll( content, '</li>', '</a></li>' );
  content = replaceAll( content, '</h1>', '</a></h1>' );
  content = replaceAll( content, '</h2>', '</a></h2>' );
  content = replaceAll( content, '</h3>', '</a></h3>' );
  content = replaceAll( content, '</h4>', '</a></h4>' );
  content = replaceAll( content, '</h5>', '</a></h5>' );
  content = replaceAll( content, '</h6>', '</a></h6>' );
  
  if( !content ) {
  
    content = '<p class="seecms' + type + '">' + link + name + '</a></p>';
  }
  
  if( content == args.content ) {
    
    content = link + content + '</a>';
  }

  window.parent.tinyMCE.activeEditor.execCommand( 'mceInsertContent', 0, content );
  top.tinymce.activeEditor.windowManager.close();
}

function prepareLink() {

  if( $('#linktype').val() == 'email' ) {
    selectedItem = 'email-mailto:' + $('#emaillink').val();
  } if( $('#linktype').val() == 'external' ) {
    selectedItem = 'weblink-' + $('#weblink').val();
  }
  
  $.ajax({
    type: "POST",
    url: "../../../ajax/",
    data: { action: "content-prepareSelectedLink", item: selectedItem, newwindow: $('#newwindow:checked').val() }
  })
  .done(function( msg ) {
    var ret = jQuery.parseJSON( msg );
    insertContent( ret.link, ret.name, ret.type );
  });
}