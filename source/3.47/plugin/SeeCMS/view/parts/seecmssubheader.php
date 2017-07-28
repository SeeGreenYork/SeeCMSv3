<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

echo '<div class="headerwrap">';
echo '<div class="header">';
echo '<div class="left">';
echo '<a target="_blank" class="logo" href="http://www.seecms.net"></a>';
echo '</div>';
echo '<div class="right">';
echo '<div class="loggedin">';
echo "<p>Welcome to <span><strong>{$see->siteTitle}</strong></span>, <strong>{$_SESSION['seecms'][$see->siteID]['adminuser']['name']}</strong></p>";
echo "<p><seecmsupdatealert><a href=\"?seecmsLogout=1\" id=\"logout\">Log out&nbsp;&nbsp;<i class=\"fa fa-power-off\" aria-hidden=\"true\"></i></a> <a id=\"visitwebsite\" target=\"_blank\" href=\"/\">Visit website&nbsp;&nbsp;<i class=\"fa fa-chevron-circle-right\" aria-hidden=\"true\"></i></a></p>";
echo '</div>';
if( count( $data ) ) {
  echo "<div class=\"visitmultisite\"><ul>";

  foreach( $data as $s ) {

    echo "<li><a target=\"_blank\" href=\"http://{$s->name}\">{$s->name}</a></li>";
  }

  echo "</ul></div>";
}
echo '</div>';
echo '</div>';
echo '</div>';
