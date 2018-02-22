(function ($, document, window) {
var RKExternal = {};
var RKfunction = typeof RKfuncion === 'object' ? RKfuncion : {};
/**
 * notifycation popup
 * 
 * @param {type} message
 * @param {type} type: success, info, warning, danger
 * @return {unresolved}
 */
RKExternal.notify = function(message, type, position) {
    if (typeof type === 'undefined') {
        type = 'success';
    } else if (type === false) {
        type = 'warning';
    }
    position = $.extend({
        from : 'top',
        align : 'right', // left, center
    }, position);
    var messageList = '';
    if (typeof message === 'undefined' || !message) {
        messageList = type;
    } else if (Array.isArray(message)) {
        if (message.length > 1) {
            messageList += '<ul>';
            $.each(message, function (i, v) {
                messageList += '<li>'+ v +'</li>';
            });
            messageList += '</ul>';
        } else if (message.length === 1) {
            messageList += message[0];
        } else {
            messageList = type;
        }
    } else {
        messageList = message;
    }
    $.notifyClose();
    return $.notify({
        message: messageList
    },{
        type: type,
        z_index: 2000,
        placement: {
            from : position.from,
            align : position.align,
        },
    });
};

/**
 * progess bar 
 */
RKExternal.progressBar = {
    bar: null,
    timeout: null,
    step: 100,
    process: false,
    /**
     * start progress bar
     */
    start: function() {
        var __this = this;
        if (__this.process) {
            return true;
        }
        __this.process = true;
        if (!this.bar) {
            if (!$(".progressbar").length) {
                $('body').append('<div class="progressbar ui-progressbar-top"></div>');
            }
            __this.bar = $(".progressbar");
            __this.bar.progressbar({
                value: 0,
                max: 100,
                complete: function() {
                    __this.end();
                },
                create: function() {
                    __this.progress();
                }
            });
        } else {
            __this.bar.progressbar('value', 0);
            __this.bar.show();
            __this.progress();
        }
    },
    /**
     * end progress bar
     */
    end: function() {
        var __this = this;
        __this.process = false;
        if (!__this.bar) {
            return true;
        }
        if (__this.bar.progressbar('value') !== 100) {
            __this.bar.progressbar('value', 100);
        }
        if (__this.timeout) {
            clearTimeout(__this.timeout);
        }
        setTimeout(function() {
            __this.bar.hide();
        }, 700);
        
    },
    /**
     * progress change value
     */
    progress: function() {
        var val = this.bar.progressbar('value') || 0,
            newVal = val + Math.floor( Math.random() * 10 ),
            __this = this;
        if (newVal < 70) {
            __this.bar.progressbar("value", newVal);
            __this.timeout = setTimeout(function() {
                __this.progress();
            }, __this.step);
        } else if (newVal < 95) {
            __this.bar.progressbar("value", val + 0.5);
            __this.timeout = setTimeout(function() {
                __this.progress();
            }, __this.step);
        } else {
            clearTimeout(__this.timeout);
        }
    },
    setStep: function(step) {
        this.step = step;
        return this;
    }
};

/**
 * submit form ajax
 *
 * flag:
 *      is use validate: data-flag-valid
 * option dom:
 *      data-cb-success="namefunction"
 *      data-cb-error="namefunction"
 *      data-cb-complete="namefunction"
 *      data-cb-before-submit="namefunction"
 *
 * response:
 *      reload: 1|0
 *      refresh: string url
 *      popup: 1|0
 *      status: 1|0
 *      message: string
 */
RKExternal.formAjax = {
    flagDom: '[data-form-submit="ajax"]',
    flagButton: '[data-btn-submit="ajax"]',
    flagFormFile: '[data-form-file="1"]',
    /**
     * inir form submit by ajax
     * run when reload page
     */
    init: function init() {
        var __this = this;
        $(__this.flagDom + ' [type="submit"]').prop('disabled', false);
        $(document).on('submit', __this.flagDom, function (event) {
            event.preventDefault();
            __this.elementSubmit($(this), 1);
        });
        $(document).on('click', __this.flagButton, function (event) {
            event.preventDefault();
            __this.elementSubmit($(this), 2);
        });
    },
    elementSubmit: function (dom, type) {
        var __this = this;
        if (''+dom.data('flag-valid') === '1') {
            if (!dom.valid()) {
                dom.find('[type=submit]').removeAttr('disabled');
                return true;
            }
        }
        if (dom.data('running')) {
            return true;
        }
        if (dom.data('submit-noti')) {
            RKExternal.confirm(dom.data('submit-noti'), function(response) {
                if (response.result) {
                    __this.execSubmit(dom, type);
                }
            });
        } else {
            __this.execSubmit(dom, type);
        }
    },
    execSubmit: function(dom, type) {
        var __this = this,
        loadingSubmit = dom.find('.loading-submit'),
        loadingHiddenSubmit = dom.find('.loading-hidden-submit'),
        callbackBeforeSubmit = dom.data('cb-before-submit'),
        btnSubmit, dataForm;
        if (type === 2) {
            btnSubmit = dom;
            dataForm = {
                _token: siteConfigGlobal.token,
            };
        } else {
            btnSubmit = dom.find('[type=submit]:not(.no-disabled)');
            dataForm = __this.getDataForm(dom);
        }
        if (callbackBeforeSubmit && typeof RKExternal[callbackBeforeSubmit] === 'function') {
            RKExternal[callbackBeforeSubmit](dataForm, dom);
        }
        dom.data('running', true);
        btnSubmit.prop('disabled', true);
        loadingSubmit.removeClass('hidden');
        loadingHiddenSubmit.addClass('hidden');
        var ajaxData = {
            url: dom.attr('action'),
            type: 'POST',
            dataType: 'json',
            data: dataForm,
            success: function success(response) {
                if (typeof response.reload !== 'undefined' && ''+response.reload === '1') {
                    window.location.reload();
                    return true;
                }
                //error
                if (typeof response.status === 'undefined' || !response.status) {
                    RKExternal.notify(response.message, false);
                    var callbackError = dom.data('cb-error');
                    if (callbackError && typeof RKExternal[callbackError] === 'function') {
                        RKExternal[callbackError](response, dom);
                    }
                    return true;
                }
                if (typeof response.redirect !== 'undefined' && response.redirect) {
                    window.location.href = response.redirect;
                    return true;
                }
                if ((typeof response.popup === 'undefined' || ''+response.popup === '1') &&
                    typeof response.message !== 'undefined' && response.message
                ) {
                    RKExternal.notify(response.message);
                }
                if (response.urlReplace) {
                    RKExternal.urlReplace(response.urlReplace);
                }
                var callbackSuccess = dom.data('cb-success');
                if (callbackSuccess && typeof RKExternal[callbackSuccess] === 'function') {
                    RKExternal[callbackSuccess](response, dom);
                }
            },
            error: function error(response) {
                RKExternal.notify('System error', false);
                var callbackError = dom.data('cb-error');
                if (callbackError && typeof RKExternal[callbackError] === 'function') {
                    RKExternal[callbackError](response, dom);
                }
            },
            complete: function complete(response) {
                dom.data('running', false);
                btnSubmit.prop('disabled', false);
                loadingSubmit.addClass('hidden');
                loadingHiddenSubmit.removeClass('hidden');
                var callbackDone = dom.data('cb-complete');
                if (callbackDone && typeof RKExternal[callbackDone] === 'function') {
                    RKExternal[callbackDone](response, dom);
                }
            },
        };
        if (dom.data('form-file')) {
            ajaxData.contentType = false;
            ajaxData.processData = false;
        }
        $.ajax(ajaxData);
    },
    /**
     * get data of form
     *
     * @param {object dom} form
     * @return {String | object FormData}
     */
    getDataForm: function(form) {
        if (!form.data('form-file')) {
            return form.serialize();
        }
        var formData = new FormData();
        form.find('input:not([disabled]), select:not([disabled]), textarea:not([disabled])')
            .each(function (i, v) {
            var type = $(v).attr('type'),
                name = $(v).attr('name'),
                value = $(v).val();
            switch (type) {
                case 'file':
                    if (v.files.length === 1) {
                        formData.append(name, v.files[0]);
                    } else if (v.files.length > 1) {
                        formData.append(name, v.files);
                    }
                    break;
                case 'checkbox':
                case 'radio':
                    if ($(v).is(':checked')) {
                        formData.append(name, value);
                    }
                    break;
                default:
                    formData.append(name, value);
                    break;
            }
        });
        return formData;
    },
};

/**
 * get params from url
 *
 * @return {object}
 */
RKExternal.params = function () {
    var params = {};
    decodeURIComponent(window.location.search).replace(/[?&]+([^=&]+)=([^&]*)/gi, function (str, key, value) {
        params[key] = value;
    });
    return params;
};

/**
 * replace url
 *
 * @param string url
 * @param {object} params
 * @param {boolean} isMergeParams
 * @return {String}
 */
RKExternal.urlReplace = function (url, params, isMergeParams) {
    if (typeof url === 'string') {
        window.history.pushState(null, null, url);
        return true;
    }
    if (typeof isMergeParams !== 'undefined' && isMergeParams) {
        params = $.extend(RKExternal.params(), params);
    }
    var href = window.location.href,
        paramsIndex = href.indexOf('?'),
        url;
    if (paramsIndex === -1) {
        url = href + '?' + $.param(params);
    } else {
        url = href.substr(0, paramsIndex) + '?' + $.param(params);
    }
    window.history.pushState(null, null, url);
};

/**
 * confirm popup
 * @param {string} msg
 * @param {function} callback: response{result, hide}
 * @param {object} option : noHideAutoYes
 */
RKExternal.confirm = function (msg, callback, option) {
    option = typeof option === 'object' ? option : {
        autoHide: true,
    };
    var __this = RKExternal.confirm,
        modalConfirm = $('#modal-confirm-submit');
    if (!modalConfirm.length) {
        modalConfirm = $('<div class="modal" id="modal-confirm-submit" data-backdrop="static" data-keyboard="false"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 class="modal-title">Confirm</h4></div><div class="modal-body"><p data-mconfirm="body"></p></div><div class="modal-footer"><button type="button" class="btn btn-default btn-confirm-no pull-left" onclick="RKExternal.confirm.no()">No</button><button type="button" class="btn btn-primary btn-confirm-yes" onclick="RKExternal.confirm.yes()">OK</button></div></div></div></div>');
        $('body').append(modalConfirm);
        __this.hide = function() {
            $('#modal-confirm-submit').modal('hide');
        };
    }
    __this.yes = function() {
        if (option.autoHide) {
            __this.hide();
        }
        return callback({
            result: true,
            hide: __this.hide,
        });
    };
    if (typeof __this.no !== 'function') {
        __this.no = function() {
            if (option.autoHide) {
                __this.hide();
            }
            return callback({
                result: false,
                hide: __this.hide,
            });
        };
    }
    if (typeof RKfunction.general !== 'undefined' &&
        typeof RKfunction.general.modalBodyPadding === 'function'
    ) {
        RKfunction.general.modalBodyPadding();
    }
    modalConfirm.find('[data-mconfirm="body"]').html(msg);
    modalConfirm.modal('show');
};

/**
 * string to url and replace search in tail string to replaceBy
 *
 * @param string link
 * @param string search
 * @param string replaceBy
 * @returns {string}
 */
RKExternal.stringToUrlReplace = function(link, search, replaceBy) {
    search = '' + search;
    replaceBy = '' + replaceBy;
    var lastIndex = link.lastIndexOf(search),
        lengthSearch = search.length;
    if (lastIndex < 0) {
        return link;
    }
    return link.substr(0, lastIndex) + replaceBy + link.substr(lastIndex + lengthSearch);
};

RKExternal.formAjax.init();
window.RKExternal = RKExternal;
})(jQuery, document, window);