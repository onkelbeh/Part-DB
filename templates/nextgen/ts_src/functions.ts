/**
 * Opens the given Link in the #content div
 * @param {string} page The URL that should be opened. (Must be on Part-DB)
 */
function openLink(page : string) {
    'use strict';
    $('#content').load(page + " #content-data");
}

/**
 * Add the given param to a existing URL.
 * @param {string} url The URL which should be modified.
 * @param {string} param The param (in Form "key=value") which should be appended to the URL
 * @returns {string} The url with the appended parameter.
 */
function addURLparam(url : string, param : string) : string
{
    'use strict';

    //If url already contains a ? than use a & for param addition
    if(url.indexOf('?') >= 0)
    {
        return url + "&" + param;
    }
    else  //Else use a ?
    {
        return url + "?" + param;
    }

}

/**
 * Submit the given Form and shows a loading bar, if the form doesn't have a ".no-progbar" class.
 * @param form The Form which should be submited.
 */
function submitForm(form) {
    'use strict';
    let data = {
        success:  showFormResponse,
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
    let name : string = $(btn).attr('name');
    let value : string = $(btn).attr('value');
    if(value === undefined)
        value = "";

    $(form).append('<input type="hidden" name="' + name + '" value="' + value + '">');
    submitForm(form);
}

/**
 * Opens the given URL in a new tab.
 * @param {string} url The URL which should be opened in a new Tab.
 */
function openInNewTab(url : string) {
    $("<a>").attr("href", url).attr("target", "_blank")[0].click();
}

/**
 * Scrolls Up, if a message is shown.
 * @returns {boolean}
 */
function scrollUpForMsg()
{
    if($("#messages").length)
    {
        $('#back-to-top').tooltip('hide');
        $('body,html').animate({
            scrollTop: 0
        }, 400);
        return false;
    }
}
