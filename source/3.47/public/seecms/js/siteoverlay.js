// SeeCMS is a website content management system
// @author See Green <http://www.seegreen.uk>
// @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
// @copyright 2015 See Green Media Ltd

$(document).ready(function(){

  $(document).on("click", ".see-cms-toolbar .button .inner", function(e){
    e.preventDefault();
    $('.nav-icon-sidebar').addClass('open');
    $(this).parent('.button').addClass('slide');
    $(".see-cms-toolbar").animate({ left: '0' }, 300);
  });

  $(document).on("click", ".see-cms-toolbar .button.slide .inner", function(e){
    e.preventDefault();
    $(this).parent('.button').removeClass('slide');
    $('.nav-icon-sidebar').removeClass('open');
    $(".see-cms-toolbar").animate({ left: '-150px' }, 300); 
  });

  $(document).on("click", ".see-cms-toolbar .hideedit", function(e){
    e.preventDefault();
    $(this).addClass('hiddenedit');
    $(this).html( '<span><i class=\"fa fa-eye\" aria-hidden=\"true\"></i></span>Show editing controls' );
    $('p.editbar').addClass('hideeditbar');
  });

  $(document).on("click", ".see-cms-toolbar .hideedit.hiddenedit", function(e){
    e.preventDefault();
    $(this).removeClass('hiddenedit');
    $(this).html( '<span><i class=\"fa fa-low-vision\" aria-hidden=\"true\"></i></span>Hide editing controls' );
    $('p.editbar').removeClass('hideeditbar');
  });

  $(document).on("click", ".see-cms-toolbar .see-cms-collapse", function(){
    $('.see-cms-toolbar').addClass('sidebarcollapse');
    $(this).addClass('sidebarcollapsed');
    $(this).html('<i class="fa fa-chevron-circle-down" aria-hidden="true"></i>');
  });
  
  $(document).on("click", ".see-cms-toolbar .see-cms-collapse.sidebarcollapsed", function(){
    $('.see-cms-toolbar').removeClass('sidebarcollapse');
    $(this).removeClass('sidebarcollapsed');
    $(this).html('<i class="fa fa-chevron-circle-up" aria-hidden="true"></i>');
  });

});