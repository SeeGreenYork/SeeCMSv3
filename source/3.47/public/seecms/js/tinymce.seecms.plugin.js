tinymce.PluginManager.add('SeeCMS', function(editor, url) {

   editor.addMenuItem('insertimage', {
        text: 'Insert image',
        context: 'insert',
        onclick: function() {
            // Open window
            editor.windowManager.open({
                title: 'Insert image',
                url: cmsURL + 'popup/select/image/',
                width: '400px',
                height: '400px',
                buttons: [{ text: 'Cancel', onclick: 'close' }]
            }, 
            { url: siteURL }
            );
        }
    });

   editor.addButton('image', {
        text: '',
        icon: 'image',
        onclick: function() {
            // Open window
            editor.windowManager.open({
                title: 'Insert image',
                url: cmsURL + 'popup/select/image/',
                width: '400px',
                height: '400px',
                buttons: [{ text: 'Cancel', onclick: 'close' }]
            }, 
            { url: siteURL }
            );
        }
    });

   editor.addMenuItem('insertlink', {
        text: 'Link',
        context: 'insert',
        onclick: function() {
        
            var content = tinymce.activeEditor.selection.getContent();
            var $jqStr = $( "<span>"+ content + "</span>" );
            var href = $jqStr.find("a").attr("href");

            // Open window
            editor.windowManager.open({
                title: 'Link',
                url: cmsURL + 'popup/select/link/',
                width: '400px',
                height: '400px',
                buttons: [{ text: 'Cancel', onclick: 'close' }]
            }, 
            { link: href, content: content }
            );
        }
    });

    editor.addButton('link', {
        text: '',
        icon: 'link',
        onclick: function() {
        
            var content = tinymce.activeEditor.selection.getContent();
            var $jqStr = $( "<span>"+ content + "</span>" );
            var href = $jqStr.find("a").attr("href");

            // Open window
            editor.windowManager.open({
                title: 'Link',
                url: cmsURL + 'popup/select/link/',
                width: '400px',
                height: '400px',
                buttons: [{ text: 'Cancel', onclick: 'close' }]
            }, 
            { link: href, content: content }
            );
        }
    });

   editor.addMenuItem('inserthtml', {
        text: 'Insert HTML',
        context: 'insert',
        onclick: function() {
        
            var node = tinymce.activeEditor.selection.getNode();
            // Open window
            editor.windowManager.open({
                title: 'Insert HTML',
                url: cmsURL + 'popup/select/html/',
                width: '400px',
                height: '400px',
                buttons: [{ text: 'Cancel', onclick: 'close' }]
            }, 
            { node: node, url: siteURL }
            );
        }
    });
});