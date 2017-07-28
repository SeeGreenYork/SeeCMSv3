<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */
?>
<div class="col1 pageTree">
<div class="sectiontitle"><h2>Pages</h2></div>
  <?php echo $data["pendingApproval"]; ?>
<ul class="draggable">
<?php echo $data["pages"]; ?>
</ul></div>

<div class="col2">
  <div class="createpages">
    <?php echo $data['createButtons']; ?>
  </div>
  <div class="support">
    <h2>Support <span><i class="fa fa-question-circle" aria-hidden="true"></i></span></h2> 
    <div class="supportinfo">
        <?php echo $see->SeeCMS->supportMessage; ?>
      </div>
  </div>
</div>
<div class="clear"></div>
<div id="newpagetitle" title="Create new page">
<p>Page title:<br /><input type="text" id="pagetitle" /></p>
</div>
<div id="deletepagepopup" title="Delete page?"></div>