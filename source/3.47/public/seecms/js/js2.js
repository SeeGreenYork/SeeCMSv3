
$(document).ready(function(){
  
  /* START: WEBSITE USERS / GROUPS */
  
  /* START: Website user filter */
  $('#websiteuserfilter').on( 'input', function(e) {
        
    $('#websiteuserfiltertext').html('');
        
    var websiteuserfilter = $('#websiteuserfilter').val();
    if( websiteuserfilter.length >= 3 ) {
      $.ajax({
        type: "POST",
        url: cmsURL+"ajax/",
        data: { action: "websiteUser-adminSearch", search: websiteuserfilter, loadHTML: 'websiteuserfilter', limit: 1000 }
      })
      .done(function( msg ) {
        var ret = jQuery.parseJSON( msg );  
        if( ret.html ) {
          $('#websiteusertable').html( ret.html );
        } else {
          $('#websiteusertable').html( '<p class="noresults">Sorry, we\'ve not found any users matching <strong>' + websiteuserfilter + '</strong></p>' );
        }
      });
    } else {
      $('#websiteusertable').html( 'Please enter a longer search filter' );
    }
  });
   /* END: Website user filter */
   
  /* START: Website user load all */
  $('#websiteuserloadall').on( 'click', function(e) {
        
    $('#websiteuserfiltertext').html("Showing all users");

    $.ajax({
      type: "POST",
      url: cmsURL+"ajax/",
      data: { action: "websiteUser-adminSearch", search: '', loadHTML: 'websiteuserfilter', limit: 100000  }
    })
    .done(function( msg ) {
      
      var ret = jQuery.parseJSON( msg );
        
      if( ret.html ) {
        $('#websiteusertable').html( ret.html );
      } else {
        $('#websiteusertable').html( '<p class="noresults">Sorry, we\'ve not found any users matching <strong>' + websiteuserfilter + '</strong></p>' );
      }
    });
  });
   /* END: Website user load all */
   
  /* START: Website group user filter */
  $('#groupuserfilter').on( 'input', function(e) {
        
    $('#groupuserfiltertext').html('');
        
    var groupuserfilter = $('#groupuserfilter').val();
    if( groupuserfilter.length >= 3 ) {
      $.ajax({
        type: "POST",
        url: cmsURL+"ajax/",
        data: { action: "websiteUser-adminSearch", search: groupuserfilter, groupid: $('#groupuserfilter').attr('data-groupid'), loadHTML: 'groupuserfilter', limit: 1000 }
      })
      .done(function( msg ) {
        
        var ret = jQuery.parseJSON( msg );
          
        if( ret.html ) {
          $('#groupusertable').html( ret.html );
        } else {
          $('#groupusertable').html( '<p class="noresults">Sorry, we\'ve not found any users matching <strong>' + groupuserfilter + '</strong></p>' );
        }
      });
    } else {
      $('#groupusertable').html( 'Please enter a longer search filter' );
    }
  });
  /* END: Website group user filter */
   
  /* START: Website group user load all */
  $('#groupuserloadall').on( 'click', function(e) {
        
    $('#groupuserfiltertext').html("Showing all users");
        
    $.ajax({
      type: "POST",
      url: cmsURL+"ajax/",
      data: { action: "websiteUser-adminSearch", search: '', groupid: $('#groupuserfilter').attr('data-groupid'), loadHTML: 'groupuserfilter', limit: 100000 }
    })
    .done(function( msg ) {
      
      var ret = jQuery.parseJSON( msg );
        
      if( ret.html ) {
        $('#groupusertable').html( ret.html );
      } else {
        $('#groupusertable').html( '<p class="noresults">Sorry, we\'ve not found any users matching <strong>' + groupuserfilter + '</strong></p>' );
      }
    });
  });
  /* END: Website group user load all */
  
  /* START: Activate website user */
  $('#websiteusertable').on( 'click', 'a.activate', function( e ) {
    
    $(this).parent().parent().children('.notification').html('<div class="seecmscheckmarkloader"></div>');
    var cA = $(this);
    
    e.preventDefault();
    $.ajax({
      type: "POST",
      url: "../../ajax/",
      data: { action: "websiteUser-activate", email: $(this).attr('data-siteuseremail'), activation: $(this).attr('data-siteuseractivation'), admin: 1 }
    })
    .done(function( msg ) {
      
      var ret = jQuery.parseJSON( msg );
      
      $("tr#wu"+ret.uid+" a.activate").attr('data-siteuseractivation','');
      $("tr#wu"+ret.uid+" a.activate").attr('title', "Deactivate "+$("tr#wu"+ret.uid+" a.activate").attr('data-name'));
      $("tr#wu"+ret.uid+" .activate").addClass('deactivate').removeClass('activate');
      $("tr#wu"+ret.uid+" .notification").html('<div class="seecmscheckmark"><div class="background"></div><div class="checkmark draw"></div></div>');
      setTimeout( function() { $("tr#wu"+ret.uid+" .notification").html(''); }, 1000 );
      
    });
  });
  /* END: Activate website user */
   
  /* START: Deactivate website user */
  $('#websiteusertable').on( 'click', 'a.deactivate', function( e ) {
    
    $(this).parent().parent().children('.notification').html('<div class="seecmscheckmarkloader"></div>');
    
    e.preventDefault();
    $.ajax({
      type: "POST",
      url: "../../ajax/",
      data: { action: "websiteUser-deactivate", id: $(this).attr('data-siteuserid'), admin: 1 }
    })
    .done(function( msg ) {
      
      var ret = jQuery.parseJSON( msg );
      
      $("tr#wu"+ret.uid+" a.deactivate").attr('data-siteuseractivation', ret.activation);
      $("tr#wu"+ret.uid+" a.deactivate").attr('title', "Activate "+$("tr#wu"+ret.uid+" a.deactivate").attr('data-name'));
      $("tr#wu"+ret.uid+" .deactivate").addClass('activate').removeClass('deactivate');
      $("tr#wu"+ret.uid+" .notification").html('<div class="seecmscheckmark"><div class="background"></div><div class="checkmark draw"></div></div>');
      setTimeout( function() { $("tr#wu"+ret.uid+" .notification").html(''); }, 1000 );

    });
  });
  /* END: Deactivate website user */
  
  /* START: Toggle website group user*/
  $(document).on( 'change', '.togglegroupuser', function(e) {
    
    $(this).parent().parent().children('.notification').html('<div class="seecmscheckmarkloader"></div>');
    
    $.ajax({
      type: "POST",
      url: cmsURL+"ajax/",
      data: { action: "websiteUser-toggleGroupUser", userid: $(this).attr('data-userid'), groupid: $(this).attr('data-groupid') }
    })
    .done(function( msg ) {
      
      var ret = jQuery.parseJSON( msg );
      
      if( ret.result == 1 ) {
        $("tr#wu"+ret.uid+" .togglegroupuser").prop('checked', 'checked');
        $("tr#wu"+ret.uid+" .notification").html('<div class="seecmscheckmark"><div class="background"></div><div class="checkmark draw"></div></div>');
      } else if( ret.result == 0 ) {
        $("tr#wu"+ret.uid+" .togglegroupuser").prop('checked', '');
        $("tr#wu"+ret.uid+" .notification").html('<div class="seecmscheckmark"><div class="background"></div><div class="checkmark draw"></div></div>');
      }
      
      setTimeout( function() { $("tr#wu"+ret.uid+" .notification").html(''); }, 1000 );
      
    });
  });
  /* END: Toggle website group user filter */
  
  /* START: Toggle website user group */
  $(document).on( 'change', '.toggleusergroup', function(e) {
    
    $(this).parent().parent().children('.notification').html('<div class="seecmscheckmarkloader"></div>');
    
    $.ajax({
      type: "POST",
      url: cmsURL+"ajax/",
      data: { action: "websiteUser-toggleGroupUser", userid: $(this).attr('data-userid'), groupid: $(this).attr('data-groupid') }
    })
    .done(function( msg ) {
      
      var ret = jQuery.parseJSON( msg );
      
      if( ret.result == 1 ) {
        $("tr#wg"+ret.gid+" .toggleusergroup").prop('checked', 'checked');
        $("tr#wg"+ret.gid+" .notification").html('<div class="seecmscheckmark"><div class="background"></div><div class="checkmark draw"></div></div>');
      } else if( ret.result == 0 ) {
        $("tr#wg"+ret.gid+" .toggleusergroup").prop('checked', '');
        $("tr#wg"+ret.gid+" .notification").html('<div class="seecmscheckmark"><div class="background"></div><div class="checkmark draw"></div></div>');
      }
      
      setTimeout( function() { $("tr#wg"+ret.gid+" .notification").html(''); }, 1000 );
      
    });
  });
  /* END: Toggle website group user filter */
   
  /* START: Delete website user */
  $('#websiteusertable').on('click', 'a.delete', function(){
    selectedItem = $(this).attr( 'data-siteuserid' );
    $('#deleteuserpopup').dialog();
    $('.dialog').dialog('open');
    $('#deleteuserpopup').html( '<p>Are you sure you want to delete this user?</p>' );
    $('#deleteuserpopup').dialog('open');
  });
  /* END: Delete website user */
  
  /* START: Delete website user group */
  $('table.sitegroups').on('click', 'a.delete', function(){
    selectedItem = $(this).attr( 'data-sitegroupid' );
    $('#deletesitegrouppopup').dialog();
    $('.dialog').dialog('open');
    $('#deletesitegrouppopup').html( '<p>Are you sure you want to delete this group?</p>' );
    $('#deletesitegrouppopup').dialog('open');
  });
  /* END: Delete website user group */
   
  /* END: WEBSITE USERS / GROUPS */

  /* START: POST FUNCTIONS  */

  /* START: POST FOLDER TOGGLE */
  $(document).on('click', '.postfolder span.toggle, .mediafolder span.toggle, .downloadfolders span.toggle', function(e){
   e.preventDefault();
   if( $(this).hasClass( "open" ) ){
     $(this).removeClass( "open" );
     $(this).addClass( "close" );
     $(this).parent().parent().children('ul').slideDown(200);
     $(this).children('i').removeClass( "fa-chevron-down" ).addClass('fa-chevron-up');
   }
   else {
     $(this).removeClass( "close" );
     $(this).addClass( "open" );
     $(this).parent().parent().children('ul').slideUp(200);
     $(this).children('i').removeClass( "fa-arrow-up" ).addClass('fa-chevron-down');
   }
  });
  /* END: POST FOLDER TOGGLE */

  /* END: POST FUNCTIONS  */
  
  /* START: ADF FUNCTIONS */
  
  /* START: SORTABLE ADFS */
  
  $('.editableADFcontentinner.sortable',document).sortable();
  
  /* END: SORTABLE ADFS */
  
  /* END: ADF FUNCTIONS */
   
});