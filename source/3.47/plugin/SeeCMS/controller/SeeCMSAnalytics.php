<?php
/**
 * SeeCMS is a website content management system
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */
 

class SeeCMSAnalyticsController {

  var $see;
  
  public function __construct( $see ) {
  
    $this->see = $see;
  }
  
  public function loadData() {

  
    $month = $_POST['month'];
    $year = $_POST['year'];
  
    if( !$month ) {
    
      $month = date("m");
      $year = date("Y");
    }
    
    $createCache = false;
    
    if( $month.$year == date("mY") ) {
      $end = date( "Y-m-d H:i:s" );
    } else {
      $end = date( "Y-m-t 23:59:59", strtotime("{$year}-{$month}-01") );
      
      $cache = SeeDB::findOne( 'datacache', ' name = ? && context = ? ', array( 'SeeCMSAnalytics', $month.$year ) );
      if( $cache ) {
        $data['cache'] = base64_decode( $cache->data, true );
      } else {
        $data['createCache'] = true;
      }
    }

    if( !$data['cache'] ) {
    
      ini_set('memory_limit', '512M');
      
      $vs = SeeDB::find( 'analyticsvisitor', ' start >= ? && end <= ? GROUP BY ip ', array( "{$year}-{$month}-01 00:00:00", $end ) );
      $data['uniqueVisitors'] = count( $vs );
      
      $vs = SeeDB::find( 'analyticsvisitor', ' start >= ? && end <= ? ', array( "{$year}-{$month}-01 00:00:00", $end ) );
      $data['visitors'] = count( $vs );
      foreach( $vs as $v ) {
        $start = strtotime( $v->start );
        $end = strtotime( $v->end );
        $totalSeconds += $end - $start;
        $data['visitsByDay'][date('d',$start)] += 1;
        
        
        $bs = unserialize( $v->browser );
        $data['visitsByBrowser'][$bs[0]] += 1;
        
        $cvs = unserialize( $v->content );
        foreach( $cvs as $cv ) {
          if( $cv[0] ) {
            $data['contentViews']["{$cv[0]}-{$cv[1]}"] += 1;
          }
        }
      }
      
      foreach( $data['visitsByBrowser'] as $bsk => $bsv ) {
      
        $data['visitsByBrowserPercent'][$bsk] = number_format(( $bsv / $data['visitors'] ) * 100, 2);
      }
      
      $data['averageVisit'] = gmdate( "H:i:s", $totalSeconds/$data['visitors'] );
    }
      
    $data['month'] = $month;
    $data['year'] = $year;
    
    $data['displayMonth'] = date( "F", strtotime( "{$year}-{$month}-01" ) );
    $data['displayYear'] = $year;
    
    return( $data );
  }
  
  public static function logVisit( $objecttype, $objectid, $siteID ) {
  
    $kb = SeeCMSAnalyticsController::knownBrowser( $_SERVER['HTTP_USER_AGENT'] );

    if( $kb ) {
      $_SESSION['seecms'][$siteID]['analyticsvisitor']['content'][] = array( $objecttype, $objectid );
      $a = SeeDB::load( 'analyticsvisitor', (int)$_SESSION['seecms'][$siteID]['analyticsvisitor']['id'] );
      if( !$a->id ) {
      
        $a->start = date("Y-m-d H:i:s");
        $a->browser = serialize( array( $kb, substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) ) );
        $a->ip = substr( $_SERVER['REMOTE_ADDR'], 0, 32 );
      }
      $a->end = date("Y-m-d H:i:s");
      $a->content = serialize( $_SESSION['seecms'][$siteID]['analyticsvisitor']['content'] );
      SeeDB::store( $a );
      $_SESSION['seecms'][$siteID]['analyticsvisitor']['id'] = $a->id;
    }
  }
  
  function knownBrowser( $ua ) {
  
    $browser["AmigaVoyager"] = "AmigaVoyager";
    $browser["BlackBerry"] = "BlackBerry";
    $browser["Camino"] = "Camino";
    $browser["Chrome mobile"] = array( "Chrome/", 'mobile' );
    $browser["Chrome"] = "Chrome/";
    $browser["DoCoMo"] = "DoCoMo";
    $browser["Firefox"] = "Firefox";
    $browser["Firefox (Shiretoko)"] = "Shiretoko";
    $browser["Galeon"] = "Galeon";
    $browser["Iceweasel"] = "Iceweasel";
    $browser["Windows mobile"] = "IEMobile";
    $browser["Internet Explorer 10"] = "MSIE 10";
    $browser["Internet Explorer 9"] = "MSIE 9";
    $browser["Internet Explorer 8"] = "MSIE 8";
    $browser["Internet Explorer 7"] = "MSIE 7";
    $browser["Internet Explorer 6"] = "MSIE 6";
    $browser["Internet Explorer 5.5"] = "MSIE 5.5";
    $browser["Internet Explorer 5"] = "MSIE 5";
    $browser["iPad"] = "iPad";
    $browser["iPhone"] = "iPhone";
    $browser["iPod"] = "iPod";
    $browser["K-Meleon"] = "Meleon";
    $browser["Konqueror"] = "Konqueror";
    $browser["Lynx"] = "Lynx";
    $browser["Firebird"] = "Firebird";
    $browser["Netscape"] = "Netscape";
    $browser["Netscape (Sun OS)"] = "sun4u";
    $browser["Nintendo 3DS"] = "Nintendo 3DS";
    $browser["Nokia"] = "Nokia";
    $browser["OpenCORE"] = "OpenCORE";
    $browser["Opera"] = "Opera";
    $browser["Palm mobile"] = "Palm";
    $browser["Polaris"] = "Polaris";
    $browser["QuickTime"] = "QuickTime";
    $browser["Safari"] = "Safari";
    $browser["SeaMonkey"] = "Sea Monkey";
    $browser["Sylera"] = "Sylera";
    $browser["Thunderbird"] = "Thunderbird";

    $browser["Internet Explorer 11"] = array( "Windows", "Trident" );
    
    foreach( $browser as $k => $v ) {
    
      if( is_array( $v ) ) {
        if( strstr( $ua, $v[0] ) && strstr( $ua, $v[1] ) ) {
          $uaName = $k;
          break;
        }
      } else {
        if( strstr( $ua, $v ) ) {
          $uaName = $k;
          break;
        }
      }
    }
    
    return( $uaName );
  }
}