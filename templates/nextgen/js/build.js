var BASE = "";
function registerLinks() {
    'use strict';
    $("a").unbind("click").not(".link-anchor").not(".link-external").click(function (event) {
        event.preventDefault();
        var a = $(this), href = addURLparam(a.attr("href"), "ajax"); //We dont need the full version of the page, so request only the content
        $('#content').hide(0).load(href + " #content-data");
        $('#progressbar').show(0);
        return true;
    });
}
//Called when Form submit was submited
function showFormResponse(responseText, statusText, xhr, $form) {
    'use strict';
    $("#content").html($(responseText).find("#content-data").html()).fadeIn('slow');
}
;
function showRequest(formData, jqForm, options) {
    'use strict';
    if (!$(jqForm).hasClass("no-progbar")) {
        $('#content').hide(0);
        $('#progressbar').show(0);
    }
    return true;
}
function registerForm() {
    'use strict';
    var data = {
        success: showFormResponse,
        beforeSubmit: showRequest
    };
    $('form').ajaxForm(data);
}
function registerHoverImages() {
    'use strict';
    $('img[rel=popover]').popover({
        html: true,
        trigger: 'hover',
        placement: 'auto',
        container: 'body',
        content: function () {
            return '<img class="img-responsive" src="' + this.src + '" />';
        }
    });
}
function onNodeSelected(event, data) {
    'use strict';
    if (data.href.indexOf("github.com") !== -1) {
        openInNewTab(data.href);
        $(this).treeview('toggleNodeSelected', data.nodeId);
    }
    else {
        $('#content').hide().load(addURLparam(data.href, "ajax") + " #content-data");
        $('#progressbar').show();
    }
    //$('#content').fadeOut("fast");
    //$('#progressbar').show();
    $(this).treeview('toggleNodeExpanded', data.nodeId);
    $("#sidebar").removeClass("in");
}
function tree_fill() {
    'use strict';
    $.getJSON(BASE + 'api_json.php?mode="tree_category"', function (tree) {
        $("#tree-categories").treeview({ data: tree, enableLinks: false, showBorder: true, onNodeSelected: onNodeSelected }).treeview('collapseAll', { silent: true });
    });
    $.getJSON(BASE + 'api_json.php?mode="tree_devices"', function (tree) {
        $('#tree-devices').treeview({ data: tree, enableLinks: false, showBorder: true, onNodeSelected: onNodeSelected }).treeview('collapseAll', { silent: true });
    });
    $.getJSON(BASE + 'api_json.php?mode="tree_tools"', function (tree) {
        $('#tree-tools').treeview({ data: tree, enableLinks: false, showBorder: true, onNodeSelected: onNodeSelected }).treeview('collapseAll', { silent: true });
    });
}
function treeview_btn_init() {
    $(".tree-btns").click(function () {
        $(this).parents("div.dropdown").removeClass('open');
        var mode = $(this).data("mode");
        var target = $(this).data("target");
        if (mode === "collapse") {
            $('#' + target).treeview('collapseAll', { silent: true });
        }
        else if (mode === "expand") {
            $('#' + target).treeview('expandAll', { silent: true });
        }
    });
}
$(document).ready(function () {
    'use strict';
    var page = window.location.pathname;
    //Only load start page when on index.php (and no content is loaded already)!
    if (page.indexOf(".php") === -1 || page.indexOf("index.php") !== -1) {
        openLink("startup.php");
    }
    tree_fill();
    treeview_btn_init();
    registerForm();
    registerLinks();
    //bbcode_edit();
    $(window).scroll(function () {
        if ($(this).scrollTop() > 50) {
            $('#back-to-top').fadeIn();
        }
        else {
            $('#back-to-top').fadeOut();
        }
    });
    // scroll body to 0px on click
    $('#back-to-top').click(function () {
        $('#back-to-top').tooltip('hide');
        $('body,html').animate({
            scrollTop: 0
        }, 800);
        return false;
    }).tooltip('show');
});
function makeSortTable() {
    'use strict';
    if (!$.fn.DataTable.isDataTable('.table-sortable')) {
        $('.table-sortable').DataTable({
            "paging": false,
            "ordering": true,
            "info": false,
            "searching": false,
            "order": [],
            "columnDefs": [{
                    "targets": 'no-sort',
                    "orderable": false
                }]
        });
        //$(".table-sortable").DataTable().fnDraw();
    }
}
function makeFileInput() {
    'use strict';
    $(".file").fileinput();
}
//Make back in the browser go back in history
window.onpopstate = function (event) {
    'use strict';
    var page = location.href;
    //Go back only when the the target isnt the empty index.
    if (page.indexOf(".php") !== -1 && page.indexOf("index.php") === -1) {
        $('#content').hide(0).load(addURLparam(location.href, "ajax") + " #content-data");
        $('#progressbar').show(0);
    }
};
function registerSubmitBtn() {
    $("button.submit").unbind("click").click(function () {
        submitFormSubmitBtn($(this).closest("form"), this);
    });
}
$(document).ajaxComplete(function (event, xhr, settings) {
    'use strict';
    //Hide progressbar and show Result
    $('#progressbar').hide(0);
    //$('#content').show(0);
    $('#content').fadeIn("fast");
    makeSortTable();
    registerLinks();
    registerForm();
    makeFileInput();
    registerHoverImages();
    scrollUpForMsg();
    registerSubmitBtn();
    if ($("x3d").length) {
        x3dom.reload();
    }
    $(".selectpicker").selectpicker();
    //Push only if it was a "GET" request and requested data was an HTML
    if (settings.type.toLowerCase() !== "post" && settings.dataType !== "json" && settings.dataType !== "jsonp") {
        //Push the cleaned (no ajax request) to history
        window.history.pushState(null, "", settings.url.replace("&ajax", "").replace("?ajax", ""));
        //Set page title from response
        var regex = /<title>(.*?)<\/title>/gi, input = xhr.responseText;
        if (regex.test(input)) {
            var matches = input.match(regex);
            for (var match in matches) {
                document.title = $(matches[match]).text();
            }
        }
    }
});
//Called when an error occurs on loading ajax
$(document).ajaxError(function (event, request, settings) {
    'use strict';
    console.log(event);
});
$("#search-submit").click(function (event) {
    $("#searchbar").removeClass("in");
});
/**
 * Opens the given Link in the #content div
 * @param {string} page The URL that should be opened. (Must be on Part-DB)
 */
function openLink(page) {
    'use strict';
    $('#content').load(page + " #content-data");
}
/**
 * Add the given param to a existing URL.
 * @param {string} url The URL which should be modified.
 * @param {string} param The param (in Form "key=value") which should be appended to the URL
 * @returns {string} The url with the appended parameter.
 */
function addURLparam(url, param) {
    'use strict';
    //If url already contains a ? than use a & for param addition
    if (url.indexOf('?') >= 0) {
        return url + "&" + param;
    }
    else {
        return url + "?" + param;
    }
}
/**
 * Submit the given Form and shows a loading bar, if the form doesn't have a ".no-progbar" class.
 * @param form The Form which should be submited.
 */
function submitForm(form) {
    'use strict';
    var data = {
        success: showFormResponse,
        beforeSubmit: showRequest
    };
    $(form).ajaxSubmit(data);
}
/**
 * Submit a form, via the given Button (it's value gets appended to request)
 * @param form The form which should be submited.
 * @param btn The button, which was pressed to submit the form.
 */
function submitFormSubmitBtn(form, btn) {
    var name = $(btn).attr('name');
    var value = $(btn).attr('value');
    if (value === undefined)
        value = "";
    $(form).append('<input type="hidden" name="' + name + '" value="' + value + '">');
    submitForm(form);
}
/**
 * Opens the given URL in a new tab.
 * @param {string} url The URL which should be opened in a new Tab.
 */
function openInNewTab(url) {
    $("<a>").attr("href", url).attr("target", "_blank")[0].click();
}
/**
 * Scrolls Up, if a message is shown.
 * @returns {boolean}
 */
function scrollUpForMsg() {
    if ($("#messages").length) {
        $('#back-to-top').tooltip('hide');
        $('body,html').animate({
            scrollTop: 0
        }, 400);
        return false;
    }
}
function octoPart_success(response) {
    'use strict';
    $('#description_select').modal('show');
    $('#description').val(response.results[0].snippet);
}
function octoPart() {
    'use strict';
    var url = 'http://octopart.com/api/v3/parts/search?', part = $('#name').val();
    url += '&apikey=e418fbe2';
    $.ajax({
        url: url,
        // The name of the callback parameter
        jsonp: "callback",
        // Tell jQuery we're expecting JSONP
        dataType: "jsonp",
        //
        data: {
            q: part
        },
        // Work with the response
        success: octoPart_success
    });
}
