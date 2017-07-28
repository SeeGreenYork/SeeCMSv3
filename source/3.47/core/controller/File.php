<?php
/**
 * SeePHP is a PHP micro framework
 *
 * @author See Green <http://www.seegreen.uk>
 * @license http://www.seephp.net/seephp-licence.txt GNU GPL v3.0 License
 * @copyright 2015 See Green Media Ltd
 */

class SeeFileController {

  static function getFileExtension( $file ) {
  
    $pathParts = pathinfo( $file );
    return( $pathParts['extension'] );
  }
  
  static function delete( $file ) {
  
    return( unlink( $file ) );
  }
  
  static function passthrough( $file, $inline = false ) {
  
    if ( file_exists( $file['path'] ) ) {

      $mimes = SeeFileController::mimeTypes();
      $ext = SeeFileController::getFileExtension( $file['path'] );
      
      if( $mimes[$ext] ) {
        header("Content-Type: ".$mimes[$ext]);
      } else {
        header("Content-Type: application/octet-stream" );
      }

      header("Expires: Mon, 01 Jan 1990 01:00:00 GMT");
      header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
      header("Content-Length: " . filesize( $file['path'] ) );
      
      if( $inline ) {
        header("Content-Disposition: filename=\"{$file['name']}\"");
      } else {
        header("Content-Disposition: attachment; filename=\"{$file['name']}\"");
      }

      ob_end_flush();
      readfile( $file['path'] );
      die();
    } else {
      SeeRouteController::http404();
    }
  }
  
  static function filesize( $bytes ) {
  
    $bytes = floatval($bytes);
    $arBytes = array( 0 => array("UNIT" => "TB","VALUE" => pow(1024, 4)), 1 => array("UNIT" => "GB","VALUE" => pow(1024, 3)), 2 => array("UNIT" => "MB","VALUE" => pow(1024, 2)), 3 => array("UNIT" => "KB","VALUE" => 1024), 4 => array("UNIT" => "B","VALUE" => 1));

    foreach($arBytes as $arItem) {
      if($bytes >= $arItem["VALUE"]) {
        $result = $bytes / $arItem["VALUE"];
        $result = strval(round($result, 2))." ".$arItem["UNIT"];
        break;
      }
    }
    
    return $result;
  }
  
  static function mimeTypes() {
  
    $mimes['3dm'] = "x-world/x-3dmf";
    $mimes['3dmf'] = "x-world/x-3dmf";
    $mimes['a'] = "application/octet-stream";
    $mimes['aab'] = "application/x-authorware-bin";
    $mimes['aam'] = "application/x-authorware-map";
    $mimes['aas'] = "application/x-authorware-seg";
    $mimes['abc'] = "text/vnd";
    $mimes['acgi'] = "text/html";
    $mimes['afl'] = "video/animaflex";
    $mimes['ai'] = "application/postscript";
    $mimes['aif'] = "audio/aiff";
    $mimes['aifc'] = "audio/aiff";
    $mimes['aiff'] = "audio/aiff";
    $mimes['aim'] = "application/x-aim";
    $mimes['aip'] = "text/x-audiosoft-intra";
    $mimes['ani'] = "application/x-navi-animation";
    $mimes['aos'] = "application/x-nokia-9000-communicator-add-on-software";
    $mimes['aps'] = "application/mime";
    $mimes['arc'] = "application/octet-stream";
    $mimes['arj'] = "application/arj";
    $mimes['art'] = "image/x-jg";
    $mimes['asf'] = "video/x-ms-asf";
    $mimes['asm'] = "text/x-asm";
    $mimes['asp'] = "text/asp";
    $mimes['asx'] = "video/x-ms-asf";
    $mimes['au'] = "audio/basic";
    $mimes['avi'] = "video/avi";
    $mimes['avs'] = "video/avs-video";
    $mimes['bcpio'] = "application/x-bcpio";
    $mimes['bin'] = "application/octet-stream";
    $mimes['bm'] = "image/bmp";
    $mimes['bmp'] = "image/bmp";
    $mimes['boo'] = "application/book";
    $mimes['book'] = "application/book";
    $mimes['boz'] = "application/x-bzip2";
    $mimes['bsh'] = "application/x-bsh";
    $mimes['bz'] = "application/x-bzip";
    $mimes['bz2'] = "application/x-bzip2";
    $mimes['c'] = "text/plain";
    $mimes['c++'] = "text/plain";
    $mimes['cat'] = "application/vnd";
    $mimes['cc'] = "text/plain";
    $mimes['ccad'] = "application/clariscad";
    $mimes['cco'] = "application/x-cocoa";
    $mimes['cdf'] = "application/cdf";
    $mimes['cer'] = "application/pkix-cert";
    $mimes['cha'] = "application/x-chat";
    $mimes['chat'] = "application/x-chat";
    $mimes['class'] = "application/java";
    $mimes['com'] = "application/octet-stream";
    $mimes['conf'] = "text/plain";
    $mimes['cpio'] = "application/x-cpio";
    $mimes['cpt'] = "application/mac-compactpro";
    $mimes['crl'] = "application/pkcs-crl";
    $mimes['crt'] = "application/pkix-cert";
    $mimes['csh'] = "application/x-csh";
    $mimes['css'] = "text/css";
    $mimes['cxx'] = "text/plain";
    $mimes['dcr'] = "application/x-director";
    $mimes['deepv'] = "application/x-deepv";
    $mimes['def'] = "text/plain";
    $mimes['der'] = "application/x-x509-ca-cert";
    $mimes['dif'] = "video/x-dv";
    $mimes['dir'] = "application/x-director";
    $mimes['dl'] = "video/dl";
    $mimes['dl'] = "video/x-dl";
    $mimes['doc'] = "application/msword";
    $mimes['dot'] = "application/msword";
    $mimes['dp'] = "application/commonground";
    $mimes['drw'] = "application/drafting";
    $mimes['dump'] = "application/octet-stream";
    $mimes['dv'] = "video/x-dv";
    $mimes['dvi'] = "application/x-dvi";
    $mimes['dwf'] = "drawing/x-dwf (old)";
    $mimes['dwg'] = "application/acad";
    $mimes['dxf'] = "application/dxf";
    $mimes['dxr'] = "application/x-director";
    $mimes['elc'] = "application/x-elc";
    $mimes['env'] = "application/x-envoy";
    $mimes['eps'] = "application/postscript";
    $mimes['es'] = "application/x-esrehber";
    $mimes['etx'] = "text/x-setext";
    $mimes['evy'] = "application/envoy";
    $mimes['exe'] = "application/octet-stream";
    $mimes['f'] = "text/plain";
    $mimes['f77'] = "text/x-fortran";
    $mimes['f90'] = "text/plain";
    $mimes['f90'] = "text/x-fortran";
    $mimes['fdf'] = "application/vnd";
    $mimes['fif'] = "image/fif";
    $mimes['fli'] = "video/fli";
    $mimes['flo'] = "image/florian";
    $mimes['flx'] = "text/vnd";
    $mimes['fmf'] = "video/x-atomic3d-feature";
    $mimes['for'] = "text/plain";
    $mimes['fpx'] = "image/vnd";
    $mimes['frl'] = "application/freeloader";
    $mimes['funk'] = "audio/make";
    $mimes['g'] = "text/plain";
    $mimes['g3'] = "image/g3fax";
    $mimes['gif'] = "image/gif";
    $mimes['gl'] = "video/gl";
    $mimes['gsd'] = "audio/x-gsm";
    $mimes['gsm'] = "audio/x-gsm";
    $mimes['gsp'] = "application/x-gsp";
    $mimes['gss'] = "application/x-gss";
    $mimes['gtar'] = "application/x-gtar";
    $mimes['gz'] = "application/x-gzip";
    $mimes['gzip'] = "application/x-gzip";
    $mimes['h'] = "text/plain";
    $mimes['hdf'] = "application/x-hdf";
    $mimes['help'] = "application/x-helpfile";
    $mimes['hgl'] = "application/vnd";
    $mimes['hh'] = "text/plain";
    $mimes['hlb'] = "text/x-script";
    $mimes['hlp'] = "application/hlp";
    $mimes['hpg'] = "application/vnd";
    $mimes['hpgl'] = "application/vnd";
    $mimes['hqx'] = "application/binhex";
    $mimes['hta'] = "application/hta";
    $mimes['htc'] = "text/x-component";
    $mimes['htm'] = "text/html";
    $mimes['html'] = "text/html";
    $mimes['htmls'] = "text/html";
    $mimes['htt'] = "text/webviewhtml";
    $mimes['htx'] = "text/html";
    $mimes['ice'] = "x-conference/x-cooltalk";
    $mimes['ico'] = "image/x-icon";
    $mimes['idc'] = "text/plain";
    $mimes['ief'] = "image/ief";
    $mimes['iefs'] = "image/ief";
    $mimes['iges'] = "application/iges";
    $mimes['igs'] = "application/iges";
    $mimes['ima'] = "application/x-ima";
    $mimes['imap'] = "application/x-httpd-imap";
    $mimes['inf'] = "application/inf";
    $mimes['ins'] = "application/x-internett-signup";
    $mimes['ip'] = "application/x-ip2";
    $mimes['isu'] = "video/x-isvideo";
    $mimes['it'] = "audio/it";
    $mimes['iv'] = "application/x-inventor";
    $mimes['ivr'] = "i-world/i-vrml";
    $mimes['ivy'] = "application/x-livescreen";
    $mimes['jam'] = "audio/x-jam";
    $mimes['jav'] = "text/x-java-source";
    $mimes['java'] = "text/x-java-source";
    $mimes['jcm'] = "application/x-java-commerce";
    $mimes['jfif'] = "image/jpeg";
    $mimes['jfif-tbnl'] = "image/jpeg";
    $mimes['jpe'] = "image/jpeg";
    $mimes['jpeg'] = "image/jpeg";
    $mimes['jpg'] = "image/jpeg";
    $mimes['js'] = "text/javascript";
    $mimes['jut'] = "image/jutvision";
    $mimes['kar'] = "audio/midi";
    $mimes['ksh'] = "application/x-ksh";
    $mimes['la'] = "audio/nspaudio";
    $mimes['lam'] = "audio/x-liveaudio";
    $mimes['latex'] = "application/x-latex";
    $mimes['lha'] = "application/lha";
    $mimes['lhx'] = "application/octet-stream";
    $mimes['list'] = "text/plain";
    $mimes['lma'] = "audio/nspaudio";
    $mimes['log'] = "text/plain";
    $mimes['lsp'] = "application/x-lisp";
    $mimes['lst'] = "text/plain";
    $mimes['lsx'] = "text/x-la-asf";
    $mimes['m'] = "text/plain";
    $mimes['m1v'] = "video/mpeg";
    $mimes['m2a'] = "audio/mpeg";
    $mimes['m2v'] = "video/mpeg";
    $mimes['m3u'] = "audio/x-mpequrl";
    $mimes['man'] = "application/x-troff-man";
    $mimes['map'] = "application/x-navimap";
    $mimes['mar'] = "text/plain";
    $mimes['mbd'] = "application/mbedlet";
    $mimes['mc$'] = "application/x-magic-cap-package-1";
    $mimes['mcd'] = "application/mcad";
    $mimes['mcf'] = "text/mcf";
    $mimes['mcp'] = "application/netmc";
    $mimes['me'] = "application/x-troff-me";
    $mimes['mht'] = "message/rfc822";
    $mimes['mhtml'] = "message/rfc822";
    $mimes['mid'] = "audio/midi";
    $mimes['midi'] = "audio/midi";
    $mimes['mif'] = "application/x-frame";
    $mimes['mime'] = "www/mime";
    $mimes['mjpg'] = "video/x-motion-jpeg";
    $mimes['mm'] = "application/base64";
    $mimes['mm'] = "application/x-meme";
    $mimes['mme'] = "application/base64";
    $mimes['mod'] = "audio/mod";
    $mimes['moov'] = "video/quicktime";
    $mimes['mov'] = "video/quicktime";
    $mimes['movie'] = "video/x-sgi-movie";
    $mimes['mp2'] = "audio/mpeg";
    $mimes['mp3'] = "audio/mpeg3";
    $mimes['mpa'] = "audio/mpeg";
    $mimes['mpc'] = "application/x-project";
    $mimes['mpe'] = "video/mpeg";
    $mimes['mpeg'] = "video/mpeg";
    $mimes['mpg'] = "video/mpeg";
    $mimes['mpga'] = "audio/mpeg";
    $mimes['mpt'] = "application/x-project";
    $mimes['mpv'] = "application/x-project";
    $mimes['mpx'] = "application/x-project";
    $mimes['mrc'] = "application/marc";
    $mimes['ms'] = "application/x-troff-ms";
    $mimes['mv'] = "video/x-sgi-movie";
    $mimes['my'] = "audio/make";
    $mimes['nap'] = "image/naplps";
    $mimes['naplps'] = "image/naplps";
    $mimes['nc'] = "application/x-netcdf";
    $mimes['nif'] = "image/x-niff";
    $mimes['niff'] = "image/x-niff";
    $mimes['nix'] = "application/x-mix-transfer";
    $mimes['nsc'] = "application/x-conference";
    $mimes['nvd'] = "application/x-navidoc";
    $mimes['o'] = "application/octet-stream";
    $mimes['oda'] = "application/oda";
    $mimes['omc'] = "application/x-omc";
    $mimes['omcd'] = "application/x-omcdatamaker";
    $mimes['omcr'] = "application/x-omcregerator";
    $mimes['p'] = "text/x-pascal";
    $mimes['p10'] = "application/pkcs10";
    $mimes['p12'] = "application/pkcs-12";
    $mimes['p7a'] = "application/x-pkcs7-signature";
    $mimes['p7c'] = "application/pkcs7-mime";
    $mimes['p7m'] = "application/pkcs7-mime";
    $mimes['p7r'] = "application/x-pkcs7-certreqresp";
    $mimes['p7s'] = "application/pkcs7-signature";
    $mimes['part'] = "application/pro_eng";
    $mimes['pas'] = "text/pascal";
    $mimes['pbm'] = "image/x-portable-bitmap";
    $mimes['pcl'] = "application/vnd";
    $mimes['pct'] = "image/x-pict";
    $mimes['pcx'] = "image/x-pcx";
    $mimes['pdb'] = "chemical/x-pdb";
    $mimes['pdf'] = "application/pdf";
    $mimes['pfunk'] = "audio/make";
    $mimes['pgm'] = "image/x-portable-graymap";
    $mimes['pic'] = "image/pict";
    $mimes['pict'] = "image/pict";
    $mimes['pkg'] = "application/x-newton-compatible-pkg";
    $mimes['pko'] = "application/vnd";
    $mimes['pl'] = "text/plain";
    $mimes['plx'] = "application/x-pixclscript";
    $mimes['pm'] = "image/x-xpixmap";
    $mimes['pm4'] = "application/x-pagemaker";
    $mimes['pm5'] = "application/x-pagemaker";
    $mimes['png'] = "image/png";
    $mimes['pnm'] = "application/x-portable-anymap";
    $mimes['pot'] = "application/mspowerpoint";
    $mimes['pov'] = "model/x-pov";
    $mimes['ppm'] = "image/x-portable-pixmap";
    $mimes['pps'] = "application/mspowerpoint";
    $mimes['ppt'] = "application/mspowerpoint";
    $mimes['pptx'] = "application/mspowerpoint";
    $mimes['ppz'] = "application/mspowerpoint";
    $mimes['pre'] = "application/x-freelance";
    $mimes['prt'] = "application/pro_eng";
    $mimes['ps'] = "application/postscript";
    $mimes['psd'] = "application/octet-stream";
    $mimes['pvu'] = "paleovu/x-pv";
    $mimes['pwz'] = "application/vnd";
    $mimes['py'] = "text/x-script";
    $mimes['pyc'] = "applicaiton/x-bytecode";
    $mimes['qcp'] = "audio/vnd";
    $mimes['qd3'] = "x-world/x-3dmf";
    $mimes['qd3d'] = "x-world/x-3dmf";
    $mimes['qif'] = "image/x-quicktime";
    $mimes['qt'] = "video/quicktime";
    $mimes['qtc'] = "video/x-qtc";
    $mimes['qti'] = "image/x-quicktime";
    $mimes['qtif'] = "image/x-quicktime";
    $mimes['ra'] = "audio/x-pn-realaudio";
    $mimes['ram'] = "audio/x-pn-realaudio";
    $mimes['ras'] = "application/x-cmu-raster";
    $mimes['rast'] = "image/cmu-raster";
    $mimes['rexx'] = "text/x-script";
    $mimes['rf'] = "image/vnd";
    $mimes['rgb'] = "image/x-rgb";
    $mimes['rm'] = "application/vnd";
    $mimes['rmi'] = "audio/mid";
    $mimes['rmm'] = "audio/x-pn-realaudio";
    $mimes['rmp'] = "audio/x-pn-realaudio";
    $mimes['rng'] = "application/ringing-tones";
    $mimes['rnx'] = "application/vnd";
    $mimes['roff'] = "application/x-troff";
    $mimes['rp'] = "image/vnd";
    $mimes['rpm'] = "audio/x-pn-realaudio-plugin";
    $mimes['rt'] = "text/richtext";
    $mimes['rtf'] = "application/rtf";
    $mimes['rtx'] = "application/rtf";
    $mimes['rv'] = "video/vnd";
    $mimes['s'] = "text/x-asm";
    $mimes['s3m'] = "audio/s3m";
    $mimes['saveme'] = "application/octet-stream";
    $mimes['sbk'] = "application/x-tbook";
    $mimes['scm'] = "application/x-lotusscreencam";
    $mimes['sdml'] = "text/plain";
    $mimes['sdp'] = "application/sdp";
    $mimes['sdr'] = "application/sounder";
    $mimes['sea'] = "application/sea";
    $mimes['set'] = "application/set";
    $mimes['sgm'] = "text/sgml";
    $mimes['sgml'] = "text/sgml";
    $mimes['sh'] = "application/x-bsh";
    $mimes['shar'] = "application/x-bsh";
    $mimes['shtml'] = "text/html";
    $mimes['sid'] = "audio/x-psid";
    $mimes['sit'] = "application/x-sit";
    $mimes['skd'] = "application/x-koan";
    $mimes['skm'] = "application/x-koan";
    $mimes['skp'] = "application/x-koan";
    $mimes['skt'] = "application/x-koan";
    $mimes['sl'] = "application/x-seelogo";
    $mimes['smi'] = "application/smil";
    $mimes['smil'] = "application/smil";
    $mimes['snd'] = "audio/basic";
    $mimes['sol'] = "application/solids";
    $mimes['spc'] = "application/x-pkcs7-certificates";
    $mimes['spl'] = "application/futuresplash";
    $mimes['spr'] = "application/x-sprite";
    $mimes['sprite'] = "application/x-sprite";
    $mimes['src'] = "application/x-wais-source";
    $mimes['ssi'] = "text/x-server-parsed-html";
    $mimes['ssm'] = "application/streamingmedia";
    $mimes['sst'] = "application/vnd";
    $mimes['step'] = "application/step";
    $mimes['stl'] = "application/sla";
    $mimes['stp'] = "application/step";
    $mimes['sv4cpio'] = "application/x-sv4cpio";
    $mimes['sv4crc'] = "application/x-sv4crc";
    $mimes['svf'] = "image/vnd";
    $mimes['swf'] = "application/x-shockwave-flash";
    $mimes['t'] = "application/x-troff";
    $mimes['talk'] = "text/x-speech";
    $mimes['tar'] = "application/x-tar";
    $mimes['tbk'] = "application/toolbook";
    $mimes['tbk'] = "application/x-tbook";
    $mimes['tcl'] = "application/x-tcl";
    $mimes['tex'] = "application/x-tex";
    $mimes['texi'] = "application/x-texinfo";
    $mimes['texinfo'] = "application/x-texinfo";
    $mimes['text'] = "text/plain";
    $mimes['tgz'] = "application/gnutar";
    $mimes['tif'] = "image/tiff";
    $mimes['tiff'] = "image/tiff";
    $mimes['tr'] = "application/x-troff";
    $mimes['tsi'] = "audio/tsp-audio";
    $mimes['tsp'] = "application/dsptype";
    $mimes['tsv'] = "text/tab-separated-values";
    $mimes['turbot'] = "image/florian";
    $mimes['txt'] = "text/plain";
    $mimes['uil'] = "text/x-uil";
    $mimes['uni'] = "text/uri-list";
    $mimes['unis'] = "text/uri-list";
    $mimes['unv'] = "application/i-deas";
    $mimes['uri'] = "text/uri-list";
    $mimes['uris'] = "text/uri-list";
    $mimes['ustar'] = "application/x-ustar";
    $mimes['uu'] = "application/octet-stream";
    $mimes['uue'] = "text/x-uuencode";
    $mimes['vcd'] = "application/x-cdlink";
    $mimes['vcs'] = "text/x-vcalendar";
    $mimes['vda'] = "application/vda";
    $mimes['vdo'] = "video/vdo";
    $mimes['vew'] = "application/groupwise";
    $mimes['viv'] = "video/vivo";
    $mimes['vivo'] = "video/vivo";
    $mimes['vmd'] = "application/vocaltec-media-desc";
    $mimes['vmf'] = "application/vocaltec-media-file";
    $mimes['voc'] = "audio/voc";
    $mimes['vos'] = "video/vosaic";
    $mimes['vox'] = "audio/voxware";
    $mimes['vqe'] = "audio/x-twinvq-plugin";
    $mimes['vqf'] = "audio/x-twinvq";
    $mimes['vql'] = "audio/x-twinvq-plugin";
    $mimes['vrml'] = "application/x-vrml";
    $mimes['vrt'] = "x-world/x-vrt";
    $mimes['vsd'] = "application/x-visio";
    $mimes['vst'] = "application/x-visio";
    $mimes['vsw'] = "application/x-visio";
    $mimes['w60'] = "application/wordperfect6";
    $mimes['w61'] = "application/wordperfect6";
    $mimes['w6w'] = "application/msword";
    $mimes['wav'] = "audio/wav";
    $mimes['wb1'] = "application/x-qpro";
    $mimes['wbmp'] = "image/vnd";
    $mimes['web'] = "application/vnd";
    $mimes['wiz'] = "application/msword";
    $mimes['wk1'] = "application/x-123";
    $mimes['wmf'] = "windows/metafile";
    $mimes['wml'] = "text/vnd";
    $mimes['wmlc'] = "application/vnd";
    $mimes['wmls'] = "text/vnd";
    $mimes['wmlsc'] = "application/vnd";
    $mimes['word'] = "application/msword";
    $mimes['wp'] = "application/wordperfect";
    $mimes['wp5'] = "application/wordperfect";
    $mimes['wp5'] = "application/wordperfect6";
    $mimes['wp6'] = "application/wordperfect";
    $mimes['wpd'] = "application/wordperfect";
    $mimes['wq1'] = "application/x-lotus";
    $mimes['wri'] = "application/mswrite";
    $mimes['wrz'] = "model/vrml";
    $mimes['wsc'] = "text/scriplet";
    $mimes['wsrc'] = "application/x-wais-source";
    $mimes['wtk'] = "application/x-wintalk";
    $mimes['xbm'] = "image/x-xbitmap";
    $mimes['xdr'] = "video/x-amt-demorun";
    $mimes['xgz'] = "xgl/drawing";
    $mimes['xif'] = "image/vnd";
    $mimes['xl'] = "application/excel";
    $mimes['xla'] = "application/excel";
    $mimes['xlb'] = "application/excel";
    $mimes['xlc'] = "application/excel";
    $mimes['xld'] = "application/excel";
    $mimes['xlk'] = "application/excel";
    $mimes['xll'] = "application/excel";
    $mimes['xlm'] = "application/excel";
    $mimes['xls'] = "application/excel";
    $mimes['xlt'] = "application/excel";
    $mimes['xlv'] = "application/excel";
    $mimes['xlw'] = "application/excel";
    $mimes['xm'] = "audio/xm";
    $mimes['xml'] = "application/xml";
    $mimes['xmz'] = "xgl/movie";
    $mimes['xpix'] = "application/x-vnd";
    $mimes['xpm'] = "image/x-xpixmap";
    $mimes['x-png'] = "image/png";
    $mimes['xsr'] = "video/x-amt-showrun";
    $mimes['xwd'] = "image/x-xwd";
    $mimes['xyz'] = "chemical/x-pdb";
    $mimes['z'] = "application/x-compress";
    $mimes['zip'] = "application/zip";
    $mimes['zoo'] = "application/octet-stream";
    $mimes['zsh'] = "text/x-script";
    
    return( $mimes );
  }

}