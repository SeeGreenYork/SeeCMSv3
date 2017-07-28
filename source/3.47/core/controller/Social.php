<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeSocialController {

  var $see;

  function __construct( $see ) {
    
    $this->see = $see;
  }

  public function getTweets( $settings ) {
  
    $name = $settings['name'];
    $limit = (($settings['limit'])?$settings['limit']:1);
    $excludeReplies = (($settings['excludeReplies']===false)?false:true);
    $includeRetweets = (($settings['includeRetweets']===false)?'':true);
    
    $apiKey = $settings['APIKey'];
    $apiSecret = $settings['APISecret'];
    
    $accessToken = $settings['accessToken'];
    $accessTokenSecret = $settings['accessTokenSecret'];
  
    \Codebird\Codebird::setConsumerKey( $apiKey, $apiSecret ); // static, see 'Using multiple Codebird instances'

    $tweet = \Codebird\Codebird::getInstance();
    $tweet->setToken( $accessToken, $accessTokenSecret );

    $params = array( 'screen_name' => $name, 'count' => 100, 'callback' => '?', 'exclude_replies' => $excludeReplies, 'include_rts' => $includeRetweets );
    $decode = (array)$tweet->statuses_userTimeline($params);

    for( $a = 0; $a < $limit; $a++ ) {
      $op = "";
      $post = $decode[$a];
      $o[$a]['profImg'] = $post->user->profile_image_url;
      $o[$a]['post'] = str_replace( $name.": ", "", preg_replace( "#http://([^ \r\n\v\t$]+)#i", '<a href="http://\\1">http://\\1</a>', $post->text ) );
      $o[$a]['post'] = preg_replace( "#https://([^ \r\n\v\t$]+)#i", '<a href="https://\\1">https://\\1</a>', $o[$a]['post'] );
      $o[$a]['post'] = preg_replace( "#@([^ \r\n\v\t$]+)#i", '<a href="http://twitter.com/\\1">@\\1</a>',$o[$a]['post']);
      $o[$a]['post'] = preg_replace( '#^'.$name.' #', "", $o[$a]['post']);
      $o[$a]['pubDate'] = $post->created_at;
      $o[$a]['id'] = $post->id_str;
      $o[$a]['media']['url'] = $post->extended_entities->media[0]->media_url_https;

      if ($post->retweeted_status){
        $o[$a]['profImg'] = $post->retweeted_status->user->profile_image_url;
        $o[$a]['post'] = str_replace( $name.": ", " ", preg_replace( "#http://([^ \r\n\v\t$]+)#i", '<a href="http://\\1">http://\\1</a>',$post->retweeted_status->text));
        $o[$a]['post'] = str_replace( $name.": ", " ", preg_replace( "#@([^ \r\n\v\t$]+)#i", '<a href="http://twitter.com/\\1">@\\1</a>',$o[$a]['post']));
        $o[$a]['post'] = preg_replace( '#^'.$name.' #', "", $o[$a]['post']);
        $op['screenname'] = $post->retweeted_status->user->screen_nam;
        $op['screenname'] = str_replace( $name.": ", " ", preg_replace( "#@([^ \r\n\v\t$]+)#i", '<a href="http://twitter.com/\\1">@\\1</a>',$op['screenname']));
        $op['name'] = $post->retweeted_status->user->name;
        $op['profImg'] = $post->retweeted_status->user->profile_image_url;
        $o[$a]['OP'] = $op;
      }    
    }
    
    return( $o );
  }
  
  public function getRSS( $settings ) {

    $items = (($settings['items'])?$settings['items']:5);
    $url   = $settings['url'];
    
    $arContext['http']['timeout'] = 5;
    $rssxml = simplexml_load_file( $url, "SimpleXMLElement" );
    
    if( $rssxml ) {
      
      $a = 0;
      
      while( $a < $items ) {
        if( $rssxml->channel->item[$a]->title ) {
        
          // Get Description if there's one, otherwise summary
          if( $rssxml->channel->item[$a]->description ) {
            $summary = $rssxml->channel->item[$a]->description;
          } else {
            $summary = $rssxml->channel->item[$a]->summary;
          }
            
          // Get link, usually either content of tag or in href attributes
          $link = $rssxml->channel->item[$a]->link;
          
          $title = $rssxml->channel->item[$a]->title;
          $time = strtotime( $rssxml->channel->item[$a]->pubDate );
          
          $rss[] = array( 'title' => $title, 'link' => $link, 'summary' => $summary, 'unixtime' => $time );
        }
        $a++;
      }
    }
    
    return( $rss );
  }

}