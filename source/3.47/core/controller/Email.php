<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeEmailController {

  function sendHTMLEmail( $from, $to = "", $email = "", $subject = "", $files = "", $replyto = "", $plaintext = "", $useunixlinebreaksinemail = false, $cc = "", $bcc = "" ) {

    if( is_array( $from ) ) { // New style
      $data = $from;
      $from = $data["from"];
      $to = $data["to"];
      $email = $data["email"];
      $subject = $data["subject"];
      $files = $data["files"];
      $replyto = $data["replyto"];
      $plaintext = $data["plaintext"];
      $useunixlinebreaksinemail = $data["useunixlinebreaksinemail"];
      $cc = $data["cc"];
      $bcc = $data["bcc"];
    }

    ini_set( "sendmail_from", $from );
    $randomhash = md5( time() );
    $mixedboundary = "SEECMS-MIXED-{$randomhash}";
    $altboundary = "SEECMS-ALT-{$randomhash}";
    $replyto = ( $replyto ? $replyto : $from );
    $headers = "From: {$from}\r\nReply-To: {$replyto}";
    $headers .= "\r\nMIME-version: 1.0";
    $headers .= "\r\nContent-Type: multipart/mixed; boundary=\"{$mixedboundary}\"";

    if( $cc ) {
      $headers .= "\r\nCc: {$cc}";
    }

    if( $bcc ) {
      $headers .= "\r\nBcc: {$bcc}";
    }

    ob_start(); // Turn on output buffering
    echo "--{$mixedboundary}\r\n";
    echo "Content-Type: multipart/alternative; boundary=\"{$altboundary}\"\r\n\r\n";

    // Plain text version
    echo "--{$altboundary}\r\n";
    echo "Content-Type: text/plain; charset=UTF-8\r\n";
    echo "Content-Transfer-Encoding: 7bit\r\n\r\n";

    // If plain text version not set, set from HTML
    if( !$plaintext ) {
      $plaintext = trim( strip_tags( str_ireplace( array( "<br />", "<br>", "<br/>", "<br>", "</p>", "</tr>", "</td>" ), array( "\r\n", "\r\n", "\r\n", "\r\n", "\r\n\r\n", "\r\n\r\n", " " ), $email ) ) );
    }

    echo wordwrap( $plaintext, 70, "\r\n" );

    // HTML version
    echo "\r\n\r\n--{$altboundary}\r\n";
    echo "Content-Type: text/html; charset=UTF-8\r\n";
    echo "Content-Transfer-Encoding: 7bit\r\n\r\n";
    echo $email;
    echo "\r\n\r\n--{$altboundary}--\r\n\r\n";

    if( is_array( $files ) ) {

      foreach( $files as $v ) {

        if( $v[0] || $v[2] ) {
          echo "--{$mixedboundary}\r\nContent-Type: application/octet-stream; name=\"{$v[1]}\"\r\nContent-Transfer-Encoding: base64\r\n";
          echo "Content-Disposition: attachment\r\n\r\n";

          if( $v[2] ) {
            echo chunk_split( base64_encode( $v[2] ) );
          }
          else {
            echo chunk_split( base64_encode( file_get_contents( $v[0] ) ) );
          }

        }

      }

    }

    echo "--{$mixedboundary}--\r\n";

    // Copy current buffer contents into $message variable and delete current output buffer
    $message = ob_get_clean();

    if( $useunixlinebreaksinemail ) {
      $message = str_replace( "\r\n", "\n", $message );
    }

    return mail( $to, $subject, $message, $headers );
  }
}
