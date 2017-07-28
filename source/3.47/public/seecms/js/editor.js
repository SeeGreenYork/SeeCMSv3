// SeeCMS is a website content management system
// @author See Green <http://www.seegreen.uk>
// @license http://www.seecms.net/seecms-licence.txt GNU GPL v3.0 License
// @copyright 2015 See Green Media Ltd

function escapeRegExp(string) {
    return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
}

function replaceAll(string, find, replace) {
  return string.replace(new RegExp(escapeRegExp(find), 'g'), replace);
}

function multisiteUpdate( content, mode ) {
  
  if( typeof multisite === 'object' ) {
      
    var x;
    for ( x in multisite ) {
      
      if( mode ) {
      
        content = replaceAll( content, 'href="/' + multisite[ x ][ 'route' ], 'href="' + multisite[ x ][ 'url' ] );
      } else {
        
        content = replaceAll( content, 'href="' + multisite[ x ][ 'url' ], 'href="/' + multisite[ x ][ 'route' ] );
      }
    }
  }
  
  return content;
}