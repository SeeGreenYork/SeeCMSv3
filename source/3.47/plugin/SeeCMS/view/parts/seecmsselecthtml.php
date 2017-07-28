<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

$see->html->start();
$see->html->css( 'editor.css', 'screen', '/seecms/css/' );
$see->html->js( 'jquery-1.11.1.min.js', '', '/seecms/js/' );

?>

<script>

args = top.tinymce.activeEditor.windowManager.getParams();
var currentnode = args.node;
var currentnodeSet = false;

$(document).ready(function(){
  if( currentnode.outerHTML.search("seecmshtml") > 0 ) {
    currentnodeSet = true;
    $('#html').val( currentnode.innerHTML );
  }
});

function insertHTML() {

  var finalhtml = $('#html').val().replace( "src=\"/images", 'src="'+args.url+'images' );
  
  window.parent.tinyMCE.activeEditor.execCommand( 'mceInsertContent', 0, "<div class=\"seecmshtml mceNonEditable\">" + finalhtml + "</div><p></p>" );
  top.tinymce.activeEditor.windowManager.close();
}
</script>

<div class="popup">
<p><textarea id="html"></textarea></p>
<a class="commit" href="#" onclick="insertHTML()">Insert HTML</a>
</div>