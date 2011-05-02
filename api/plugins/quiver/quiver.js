function post_to_url(path_from, path_to, params, method) {
    method = method || "post"; // Set method to post by default, if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path_to);

    // path_from field
    var pathFromField = document.createElement("input");
    pathFromField.setAttribute("type", "hidden");
    pathFromField.setAttribute("name", "path_from");
    pathFromField.setAttribute("value", path_from);
    form.appendChild(pathFromField);

    for(var key in params) {
        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("type", "hidden");
        hiddenField.setAttribute("name", key);
        hiddenField.setAttribute("value", params[key]);

        form.appendChild(hiddenField);
    }

    document.body.appendChild(form);    // Not entirely sure if this is necessary
    form.setAttribute("target", "_blank");
    form.submit();
}

function get_selected_text() {
    var txt = '';

    if (window.getSelection)
    {
        txt = window.getSelection();
    }
    else if (document.getSelection) // FireFox
    {
        txt = document.getSelection();
    }
    else if (document.selection)  // IE 6/7
    {
        txt = document.selection.createRange().text;
    }
    else {
        return;
    }

    return txt;
}

/*

<a href="
    javascript:(function()
    {
        var head=document.getElementsByTagName('head')[0];
        script=document.createElement('script');
        script.type='text/javascript';
        script.src='http://www.site.com/api/plugins/quiver/quiver.js?' + Math.floor(Math.random()*99999);
        head.appendChild(script);
        var current_url=document.URL;
        var post_url='http://www.site.com/api/plugins/quiver/quiver.php?origin=Quiver';
        post_to_url(current_url,post_url,{'text':get_selected_text()},'post');
    })();
    void 0">
    Quiver
</a>

<a href="javascript:(function(){var head=document.getElementsByTagName('head')[0];script=document.createElement('script');script.type='text/javascript';script.src='http://www.site.com/api/plugins/quiver/quiver.js?' + Math.floor(Math.random()*99999);head.appendChild(script);var current_url=document.URL;var post_url='http://www.site.com/api/plugins/quiver/quiver.php?origin=Quiver';post_to_url(current_url,post_url,{'text':get_selected_text()},'post');})();void 0">Quiver</a>

*/