<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeCSVController {

  static function makeCSVFromArray( $arr, $download = false, $name = null ) {
    
    if( $download ) {
      
      ob_get_clean();
      header('Content-Type: text/csv; charset=utf-8');
      header('Content-Disposition: attachment; filename="'.$name.'"');
      
      $output = fopen('php://output', 'w');
    }
    
    foreach( $arr as $row ) {
      
      foreach( $row as $col ) {
      
        $d .= '"'.str_replace( '"', '""', $col ).'",';
      }
      
      $d = trim( $d, ',' );
      $d .= "\r\n";
    }
    
    if( $download ) {
      
      echo $d;
      die();
    } else {
      
      return( $d );
    }
  }

  static function CSVtoArray( $file ) {
    
    $row = 0;
    if( ( $handle = fopen( $file, "r" ) ) !== FALSE ) {
      while( ( $data = fgetcsv( $handle, 0, "," ) ) !== FALSE ) {
        $rows[$row] = $data;
        $row++;
      }
      fclose($handle);
    }
    
    return( $rows );
  }
}