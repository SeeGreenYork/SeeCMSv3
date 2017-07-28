<div class="navbar-collapse collapse" id="navbar-main">
<?php

echo $data;

?>

<?php
if( isset( $_SESSION['seecms'][$this->see->siteID]['websiteuser']['id'] ) ) {
	echo '<p style="color: #fff; text-align: right; line-height: 21px; padding: 14.5px 0 14.5px 0; margin: 0;">Welcome back '.$_SESSION['seecms'][$this->see->siteID]['websiteuser']['forename'].' '.$_SESSION['seecms'][$this->see->siteID]['websiteuser']['surname'].' | <a href="/members/">Members area</a> | <a href="/login/?logout=1">Logout &gt;</a></p>';
} else {
  echo '<ul class="nav navbar-nav navbar-right"><li><a href="/login/">Login</a></li></ul>';
}
?>

<div class="clear"></div>
</div>