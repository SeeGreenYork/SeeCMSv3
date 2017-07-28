
$(document).ready(function(){
  initDownloadDropzone();
});

function initDownloadDropzone() {
  Dropzone.autoDiscover = false;

  if( $("#downloadsdropzone").length ) {
    var downloadsDropzone = new Dropzone("div#downloadsdropzone", {url: "../download/add/", fallback: ''});
    var uploadBegun = 0;
    
    downloadsDropzone.on("sending", function(file, xhr, formData) {
      
      if( !uploadBegun ) {
        uploadBegun = 1;
        $('.dropzone .dz-success').remove();
        $('.dropzone .dz-error').remove();
      }
      
      formData.append("parentid", downloadfolder);
    });
    
    downloadsDropzone.on("queuecomplete", function(file) {
      loadDownloadsByFolder();
      $('.dropzone .dz-default').show();
      uploadBegun = 0;
    });
    
  } 
    
}