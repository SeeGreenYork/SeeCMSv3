<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeHelperController {

  /* Moved to SeeFileController, maintained here for backwards compatibility */
  static function getFileExtension( $file ) {
  
    return( SeeFileController::getFileExtension( $file ) );
  }
  
  static function leadingZeroRange( $start, $end, $increment = 1 ) {
    
    $range = array();
    for( $a = $start; $a <= $end; $a+=$increment  ) {
    
      if( $a < 10 ) {
        $range[] = "0{$a}";
      } else {
        $range[] = $a;
      }
    }
    
    return( $range );
  }
  
  static function yearRange( $start, $end, $increment = 1 ) {
    
    $range = array();
    for( $a = $start; $a <= $end; $a+=$increment  ) {
    
      if( $a < 10 ) {
        $range[] = "0{$a}";
      } else {
        $range[] = $a;
      }
    }
    
    return( $range );
  }
  
  static function monthRange( $start, $end, $increment = 1 ) {
    
    $range = array();
    for( $a = $start; $a <= $end; $a+=$increment  ) {
    
      if( $a < 10 ) {
        $range["0{$a}"] = date("F", strtotime( "2000-0{$a}-01" ));
      } else {
        $range["{$a}"] = date("F", strtotime( "2000-{$a}-01" ));
      }
    }
    
    return( $range );
  }
  
  static function timeRange( $start = 0, $end = 23, $increment = 1, $minincrement = 1, $blankAtStart = false ) {

  $range = array();
  
    if( $blankAtStart ) {
      $range[] = '';
    }
    
    for( $h = $start; $h <= $end; $h += $increment  ) {
    
      if( $h < 10 ) {
        $hour = "0{$h}";
      } else {
        $hour = $h;
      }
      
      for( $m = 0; $m <= 59; $m += $minincrement  ) {
      
        if( $m < 10 ) {
          $min = "0{$m}";
        } else {
          $min = $m;
        }
        
        $range[] = "{$hour}:{$min}";
      }
      
    }
    
    return( $range );
  }
  
  static function checkValueOperator( $val1, $operator, $val2 ) {
  
    if( $operator == '=' || $operator == '==' ) {
      if( $val1 == $val2 ) {
        return true;
      }
    } else if( $operator == '!=' ) {
      if( $val1 != $val2 ) {
        return true;
      }
    } else if( $operator == '>=' ) {
      if( $val1 >= $val2 ) {
        return true;
      }
    } else if( $operator == '>' ) {
      if( $val1 > $val2 ) {
        return true;
      }
    } else if( $operator == '<=' ) {
      if( $val1 <= $val2 ) {
        return true;
      }
    } else if( $operator == '<' ) {
      if( $val1 < $val2 ) {
        return true;
      }
    } else {
      $this->error( 'Function: checkValueOperator : Error: Invalid operator' );
    }
    
    return false;
  }
  
  static function countPathLevels( $route ) {
  
    return( substr_count( $route, '/' ) - 1 );
  }

}