$(document).ready(function(){
  initMediaDropzone();
});

function initMediaDropzone() {
  Dropzone.autoDiscover = false;

  if( $("#mediadropzone").length ) {
    var mediaDropzone = new Dropzone("div#mediadropzone", {url: "../media/add/", fallback: ''});
    var uploadBegun = 0;
    
    mediaDropzone.on("sending", function(file, xhr, formData) {
    
      if( !uploadBegun ) {
        uploadBegun = 1;
        $('.dropzone .dz-success').remove();
        $('.dropzone .dz-error').remove();
      }
      
      formData.append("parentid", mediafolder);
    });
    
    mediaDropzone.on("queuecomplete", function(file) {
      loadMediaByFolder();
      $('.dropzone .dz-default').show();
      uploadBegun = 0;
    });
    
  }
  
}