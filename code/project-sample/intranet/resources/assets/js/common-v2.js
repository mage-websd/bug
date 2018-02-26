(function ($, window, document) {
var rkGrapes = {};
var CoreVar = typeof CoreVarGlobal === 'object' ? CoreVarGlobal : {},
coreTrans = typeof CoreVar.trans === 'object' ? CoreVar.trans : {};
/**
 * notifycation popup
 *
 * @param {type} message
 * @param {type} typeSuccess
 * @return {unresolved}
 */
rkGrapes.notify = function (message, typeSuccess) {
    if (typeof typeSuccess === 'undefined') {
        typeSuccess = 'success';
    }
    if (typeof message === 'undefined' || !message) {
        if (typeSuccess === 'success') {
            message = 'Success';
        } else {
            message = 'Error';
        }
    }

    $.notifyClose();
    return $.notify({
        message: message,
    }, {
        type: typeSuccess,
        z_index: 2000,
        delay: 500,
        placement: {
            from : 'top',
            align : 'center',
        },
    });
};

/**
 * show list message flash
 *
 * @param {string} message
 * @param {boolean} typeSuccess
 */
rkGrapes.flashMessage = function (message, typeSuccess, scroll) {
    if (typeof message === 'undefined') {
        return true;
    }
    var classMessage;
    if (typeof typeSuccess === 'undefined' || typeSuccess) {
        classMessage = 'alert alert-success';
    } else {
        classMessage = 'alert alert-danger';
    }
    if (typeof scroll === 'undefined') {
        scroll = true;
    }
    var domFlash = $('[data-section-html="main"] .flash-message');
    if (!domFlash.length) {
        domFlash = $('<div class="flash-message"></div>');
        $('[data-section-html="main"]').prepend(domFlash);
    }
    var html = '<div class="' + classMessage + '">' + '<p>' + message + '</p>' + '</div>';
    domFlash.html(html);
    if (scroll) {
        $('html, body').animate({
            scrollTop: domFlash.offset().top - 50,
        }, 1000);
    }
    rkGrapes.removeFlashMessage();
};

/**
 * submit form ajax
 *
 * flag:
 *      is use validate: data-flag-valid
 * option dom:
 *      data-cb-success="namefunction"
 *      data-cb-error="namefunction"
 *      data-cb-done="namefunction"
 *      data-cb-before-submit="namefunction"
 *
 * response:
 *      reload
 *      refresh
 *      popup
 */
rkGrapes.formAjax = {
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
    elementSubmit: function _elementSubmit(dom, type) {
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
            rkGrapes.confirm(dom.data('submit-noti'), function(response) {
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
                _token: CoreVar.token,
            };
        } else {
            btnSubmit = dom.find('[type=submit]:not(.no-disabled)');
            dataForm = __this.getDataForm(dom);
        }
        if (callbackBeforeSubmit && typeof rkGrapes[callbackBeforeSubmit] === 'function') {
            rkGrapes[callbackBeforeSubmit](dom);
        }
        $('[data-section-html="main"] .flash-message').html('');
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
                if (typeof response.success === 'undefined' || !response.success) {
                    rkGrapes.flashMessage(response.message, false);
                    var callbackError = dom.data('cb-error');
                    if (callbackError && typeof rkGrapes[callbackError] === 'function') {
                        rkGrapes[callbackError](dom, response);
                    }
                    return true;
                }
                if (typeof response.reload !== 'undefined' && ''+response.reload === '1') {
                    window.location.reload();
                    return true;
                }
                if (typeof response.refresh !== 'undefined' && response.refresh) {
                    window.location.href = response.refresh;
                    return true;
                }
                if ((typeof response.popup === 'undefined' || ''+response.popup === '1') &&
                    typeof response.message !== 'undefined' && response.message
                ) {
                    rkGrapes.flashMessage(response.message);
                }
                if (response.urlReplace) {
                    rkGrapes.urlReplace(null, response.urlReplace);
                }
                var callbackSuccess = dom.data('cb-success');
                if (callbackSuccess && typeof rkGrapes[callbackSuccess] === 'function') {
                    rkGrapes[callbackSuccess](dom, response);
                }
            },
            error: function error(response) {
                rkGrapes.flashMessage(coreTrans['System error'], false);
                var callbackError = dom.data('cb-error');
                if (callbackError && typeof rkGrapes[callbackError] === 'function') {
                    rkGrapes[callbackError](dom, response);
                }
            },
            complete: function complete(response) {
                dom.data('running', false);
                btnSubmit.prop('disabled', false);
                loadingSubmit.addClass('hidden');
                loadingHiddenSubmit.removeClass('hidden');
                var callbackDone = dom.data('cb-done');
                if (callbackDone && typeof rkGrapes[callbackDone] === 'function') {
                    rkGrapes[callbackDone](dom, response);
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
 * [SimpleTable]
 * insertable + retriavable data.
 * @type {Object}
 */
rkGrapes.SimpleTable = {
    btnDelete : '[data-cs-btn ="delete"]',
    btnInsert : '[data-cs-btn="insert"]',
    tableId : '',
    totalRow : 0,
    rowIdentify : 'tr',

    init : function(tableOptions){
        this.table_select = tableOptions.table_select;
        this.rowTemplate = tableOptions.row_template;
        this.rowIdentify = tableOptions.rowIdentify || 'tr';
        this.addActions();
        this.totalRow = this.getTableObj().find('tbody').children('tr').length;
        return this;
    },

    /**
     * Add action listener for buttons
     */
    addActions : function(){
        var _this = this;
        _this.getTableObj().on('click', _this.btnDelete, function(e) {
            e.preventDefault();
            $(this).closest('tr').remove();
        });

        _this.getTableObj().on('click', _this.btnInsert, e => {
            e.preventDefault();
            this.insertRow();
            this.totalRow++;
        } );
    },

    /**
     * [get table jquery object]
     * @return {[jQuery Object]}
     */
    getTableObj : function(){
        return $(this.table_select);
    },

    /**
     * Insert new row
     * @return {[type]} [description]
     */
    insertRow : function(){
        this.getTableObj().find('tbody').append(this.rowTemplate);
    },

    /**
     * get table data, separated by row
     * @return {[Array]}
     */
    retrieveTableData : function(){
        let tableData = [];
        this.getTableObj().find('tbody').find(this.rowIdentify).each((index, row) => {
            tableData.push(this.retrieveRowData(row));
        });
        return tableData;
    },

    retrieveRowData : function(rowIdent){
        let rowData = {};
        $(rowIdent).find('input,select,textarea').each((index, v) => {
            rowData[v.name] = v.value;
        });
        return rowData;
    },

    /**
     * [destroy all rows]
     * @type {[type]}
     */
    destroy : function(){
        this.getTableObj().find('tbody').find(this.rowIdentify).remove();
    },
};


/**
 * remove message flash after 10s
 * run when reload page
 */
rkGrapes.removeFlashMessage = function (forceRemove) {
    clearTimeout(rkGrapes.flashVar);
    if (typeof forceRemove === 'undefined' || !forceRemove) {
        rkGrapes.flashVar = setTimeout(function () {
            $('[data-section-html="main"] .flash-message').html('');
        }, 15 * 1000);
    } else {
        $('[data-section-html="main"] .flash-message').html('');
    }
};

/**
 * active menu sidebar main
 * run when reload page
 */
rkGrapes.activeMenu = function () {
    if (!$('.sidebar__wrapper').length || !CoreVar.sidebar) {
        return true;
    }
    $('.sidebar__wrapper [data-sidebar="' + CoreVar.sidebar + '"]').addClass('active');
};

/**
 * get params from url
 *
 * @return {object}
 */
rkGrapes.params = function () {
    var params = {};
    decodeURIComponent(window.location.search).replace(/[?&]+([^=&]+)=([^&]*)/gi, function (str, key, value) {
        params[key] = value;
    });
    return params;
};

/**
 * string to url and replace search in tail string to replaceBy
 *
 * @param string link
 * @param string search
 * @param string replaceBy
 * @returns {string}
 */
rkGrapes.stringToUrlReplace = function(link, search, replaceBy) {
    var lastIndex = link.lastIndexOf(search),
        lengthSearch = search.length;
    if (lastIndex < 0) {
        return link;
    }
    return link.substr(0, lastIndex) + replaceBy + link.substr(lastIndex + lengthSearch + 1);
};

/**
 * split name and extension
 *
 * @param {string} filename
 * @return {array}
 */
rkGrapes.filenameSplitExt = function(filename) {
    var lastIndex = filename.lastIndexOf('.');
    if (lastIndex < 0) {
        return [filename, '', ''];
    }
    return [
        filename.substr(0, lastIndex),
        filename.substr(lastIndex + 1),
        '.',
    ];
};

/**
 * replace url
 *
 * @param {type} params
 * @return {String}
 */
rkGrapes.urlReplace = function (params, url, paramsReplaceAll) {
    if (typeof url === 'string') {
        window.history.pushState(null, null, url);
        return true;
    }
    if (!paramsReplaceAll) {
        params = $.extend(rkGrapes.params(), params);
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
 * process pager collection
 *
 *  Param pagerCollection:
 *      data: []
 *      current_page: number
 *      last_page: number
 *      total: number
 */
rkGrapes.pager = {
    htmlPage: '',
    htmlInfo: '',
    htmlCollection: '',
    htmlNumber: '',
    fgPage: '[data-pager-item="page"]',
    fgInfo: '[data-pager-item="info"]',
    fgPrev: '[data-pager-item="prev"]',
    fgNext: '[data-pager-item="next"]',
    fgNumber: '[data-pager-item="number"]',
    fgRenderInfo: '[data-pager-flag="flag-info"]',
    fgRenderPager: '[data-pager-flag="flag-pager"]',
    fgRenderNumber: '[data-pager-page]',
    fgContainer: '[data-pager="container"]',
    fgCollection: '[data-pager-item="collection"]',
    fgResult: '[data-pager-item="results"]',
    fgNoResult: '[data-pager-item="no-result"]',
    option: {},

    //param search
    fgSContainer: '[data-s-dom="container"]',
    fgSExec: '[data-s-dom="exec"]',
    fgSReset: '[data-s-dom="reset"]',
    fgSIpnput: '[data-s-input]',

    process: false,
    /**
     * init pager exec
     *
     * @param {object} collection
     * @return {object}
     */
    init: function init(collection) {
        var __this = this;
        if (!__this.htmlPage && $(__this.fgPage).length) {
            __this.htmlPage = $(__this.fgPage)[0].outerHTML;
        }
        if (!__this.htmlInfo && $(__this.fgInfo).length) {
            __this.htmlInfo = $(__this.fgInfo)[0].outerHTML;
        }
        if (!__this.htmlNumber && $(__this.fgNumber).length) {
            __this.htmlNumber = $(__this.fgNumber)[0].outerHTML;
        }
        if (!__this.htmlCollection && $(__this.fgCollection).length) {
            __this.htmlCollection = $(__this.fgCollection).html();
        }
        $(__this.fgPage).remove();
        $(__this.fgInfo).remove();
        $(__this.fgNumber).remove();
        $(__this.fgCollection).html('');
        __this.option = {
            param: 'page',
            show: CoreVar.pageShow ? CoreVar.pageShow : 10,
        }, __this.filterDefault();
        __this.action();
        __this.filterAction();
        return __this;
    },
    /**
     * set collection pager
     *
     * @param {object} collection
     * @return {object}
     */
    setCollection: function setCollection(collection) {
        var __this = this;
        if (typeof collection !== 'object' || !collection.total) {
            collection.is_show_pager = false;
            return true;
        }
        collection.is_show_pager = true;
        var middleShow = parseInt((__this.option.show - 1) / 2),
            startPage = collection.current_page - middleShow,
            endPage = collection.current_page + middleShow;
        collection.is_show_prev = collection.current_page > 1;
        collection.is_show_next = collection.current_page < collection.last_page;
        if (startPage < 1) {
            endPage += 1 - startPage;
            startPage = 1;
        }
        if (endPage > collection.last_page) {
            startPage -= endPage - collection.last_page;
            if (startPage < 1) {
                startPage = 1;
            }
            endPage = collection.last_page;
        }
        collection.range_from = startPage;
        collection.range_to = endPage;
        return collection;
    },
    /**
     * exec pager
     */
    exec: function exec(collection) {
        var __this = this;
        if (!collection.is_show_pager) {
            $(__this.fgNoResult).removeClass('hidden');
            $(__this.fgResult).addClass('hidden');
            return true;
        }
        __this.page(collection);
        __this.info(collection);
        __this.collection(collection);
        $(__this.fgNoResult).addClass('hidden');
        $(__this.fgResult).removeClass('hidden');
    },
    /**
     * render page pagination
     *
     * @param {type} collection
     * @return {undefined}
     */
    page: function page(collection) {
        var __this = this;
        if (!__this.htmlPage || !collection.is_show_pager) {
            $(__this.fgRenderPager).html('');
            return true;
        }
        var domPage = $(__this.htmlPage);
        if (!collection.is_show_prev) {
            domPage.find(__this.fgPrev).addClass('disabled');
        } else {
            domPage.find(__this.fgPrev).find(__this.fgRenderNumber).attr('data-pager-page', collection.current_page - 1);
        }
        var domNext = domPage.find(__this.fgNext);
        if (!collection.is_show_next) {
            domNext.addClass('disabled');
        } else {
            domNext.find(__this.fgRenderNumber).attr('data-pager-page', collection.current_page + 1);
        }
        domPage.find(__this.fgNumber).remove();
        for (var number = collection.range_from; number <= collection.range_to; number++) {
            var domNumber = $(__this.htmlNumber);
            domNumber.find(__this.fgRenderNumber).attr('data-pager-page', number).text(number);
            if (''+number === ''+collection.current_page) {
                domNumber.addClass('active');
            }
            domNext.before(domNumber);
        };
        $(__this.fgRenderPager).html(domPage[0].outerHTML);
        return __this;
    },
    /**
     * exec collection pager
     *
     * @param {object} collection
     */
    collection: function collection(_collection) {
        var __this = this;
        $(__this.fgCollection).html('');
        if (!_collection.data) {
            return true;
        }
        $.each(_collection.data, function (i, item) {
            var dom = $(__this.htmlCollection);
            if (typeof rkGrapes.pager.beforeRenderItem === 'function') {
                item = rkGrapes.pager.beforeRenderItem(item);
            }
            var ahref = dom.find('[data-pager-flag="col-href-id"]');
            if (ahref.length && item.id) {
                ahref.attr('href', rkGrapes.stringToUrlReplace(ahref.attr('href'), '0', item.id));
            }
            $.each(item, function (attr, value) {
                var domAttr = dom.find('[data-pager-col="' + attr + '"]');
                if (domAttr.length) {
                    domAttr.text(value);
                    if (typeof rkGrapes.pager.afterRenderAttr === 'function') {
                        rkGrapes.pager.afterRenderAttr(domAttr, attr, value);
                    }
                }
            });
            $(__this.fgCollection).append(dom);
        });
        if (typeof rkGrapes.pager.afterRenderData === 'function') {
            rkGrapes.pager.afterRenderData($(__this.fgCollection));
        }
    },
    /**
     * show info more of pager
     *
     * @param {object} collection
     */
    info: function info(collection) {
        var __this = this;
        var htmlInfo = __this.htmlInfo;
        htmlInfo = htmlInfo.replace(/xfrom/g, collection.from).replace(/xtotal/g, collection.total).replace(/xto/g, collection.to);
        $(__this.fgRenderInfo).html(htmlInfo);
        return __this;
    },
    /**
     * action click page in pager
     */
    action: function action() {
        var __this = this;
        $(document).on('click', '[data-pager-page]', function (event) {
            event.preventDefault();
            var dom = $(this);
            var page = dom.data('pager-page');
            if (!page || isNaN(page)) {
                return true;
            }
            page = parseInt(page);
            if (page < 1) {
                return true;
            }
            var params = rkGrapes.params();
            // active page click => no action
            if (''+params[__this.option.param] === ''+page) {
                return true;
            }
            params[__this.option.param] = page;
            __this.sendRequest(params);
        });
    },
    /**
     * filter action search params
     */
    filterAction: function filterAction() {
        var __this = this;
        $(document).on('keypress', __this.fgSContainer + ' ' + __this.fgSIpnput, function (event) {
            if (event.keyCode === 13) {
                event.preventDefault();
                __this.filterSubit();
            }
        });
        $(document).on('click', __this.fgSExec, function (event) {
            event.preventDefault();
            __this.filterSubit();
        });
        //reset filter
        $(document).on('click', __this.fgSReset, function (event) {
            event.preventDefault();
            var params = rkGrapes.params();
            $(__this.fgSContainer + ' ' + __this.fgSIpnput).each(function (i, v) {
                delete params[$(v).data('s-input')];
                if (['radio', 'checkbox'].indexOf($(v).attr('type')) > -1) {
                    $(v).prop('checked', false);
                    return true;
                }
                $(v).val('').change();
            });
            delete params[__this.option.param];
            __this.sendRequest(params);
        });
        return __this;
    },
    /**
     * filter submit
     */
    filterSubit: function filterSubit() {
        var __this = this,
        params = rkGrapes.params(),
        radioName = [];
        $(__this.fgSContainer + ' ' + __this.fgSIpnput).each(function (i, v) {
            var dataInput = $(v).data('s-input');
            if ('checkbox' === $(v).attr('type') && !$(v).is(':checked')) {
                delete params[dataInput];
                return true;
            } else if('radio' === $(v).attr('type')) {
                var inputName = $(v).attr('name');
                if (radioName.indexOf(inputName) > -1) {
                    return true;
                }
                radioName.push(inputName);
                var domChecked = $(__this.fgSContainer + ' ' + __this.fgSIpnput +
                    '[name="'+inputName+'"]:checked');
                if (!domChecked.length) {
                    delete params[dataInput];
                } else {
                    params[dataInput] = domChecked.val();
                }
                return true;
            }
            var value = $(v).val().trim();
            if (''+value === '') {
                delete params[dataInput];
            } else {
                params[dataInput] = value;
            }
        });

        params[__this.option.param] = 1;
        __this.sendRequest(params);
    },
    /**
     * set value for form search when load page
     * run when reload page
     */
    filterDefault: function filterDefault() {
        var __this = this;
        if (!$(__this.fgSContainer + ' ' + __this.fgSIpnput).length) {
            return true;
        }
        var params = rkGrapes.params();
        $.each(params, function (i, v) {
            var dom = $(__this.fgSContainer + ' [data-s-input="' + i + '"]');
            if (!dom.length) {
                return true;
            }
            v = v.trim();
            if (['radio', 'checkbox'].indexOf(dom.attr('type')) > -1) {
                $(__this.fgSContainer + ' [data-s-input="' + i + '"][value="' + v + '"]').prop('checked', true);
                return true;
            }
            dom.val(v).change();
        });
    },
    /**
     * send request with pager and filter to server
     *
     * @param {object} params
     */
    sendRequest: function sendRequest(params) {
        var __this = this,
            url = $(__this.fgContainer).data('pager-url');
        if (!url || __this.process) {
            return true;
        }
        if (typeof params === 'undefined' || !params) {
            params = rkGrapes.params();
        }
        __this.process = true;
        rkGrapes.urlReplace(params, null, true);
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            data: params,
            success: function success(response) {
                if (!response.collectionPager) {
                    return true;
                }
                var collection = __this.setCollection(response.collectionPager);
                __this.exec(collection);
            },
            complete: function complete() {
                __this.process = false;
            },
        });
    },
};

/**
 * convert array of object to csv
 * @param  {object} args
 * @param {object} titleAs title from key to name: {user_name: "User name"}
 * @return {string}
 */
rkGrapes.convertArrayOfObjectsToCSV = function(data, titleHead, option) {
    var result = '',
    columnDelimiter = ',',
    lineDelimiter = '\r\n',
    lengthLine = lineDelimiter.length,
    lengthColumn = columnDelimiter.length;
    // string format csv file with ",\r\n
    var stringformatCsv = function(text) {
        if (text !== 0 && !text) {
            text = '';
        } else {
            text = '' + text;
        }
        text = text.replace(/"/g, '""');
        if (/,|"|\r|\n/.test(text)) {
            text = '"' + text + '"';
        }
        return text + columnDelimiter;
    };

    // add head for file
    if (typeof titleHead !== 'object') {
        titleHead = {};
        $.each(Object.keys(data[0]), function (i, key) {
            titleHead[key] = key;
        });
    }
    $.each(titleHead, function (key, label) {
        result += stringformatCsv(label);
    });
    result = result.slice(0, -lengthColumn) + lineDelimiter;
    // add data
    data.forEach(function(item) {
        if (typeof option.beforeRenderItem === 'function') {
            item = option.beforeRenderItem(item);
        }
        $.each(titleHead, function (key) {
            var string = '';
            if (typeof item[key] !== 'undefined') {
                string = item[key];
            }
            result += stringformatCsv(string);
        });
        result = result.slice(0, -lengthColumn) + lineDelimiter;
    });
    result.slice(0, -lengthLine);
    return result;
};

/**
 * downloadCsv
 * @param  {string} fileName file name for download
 * @param  {array} data
 * @param {object} titleHead {key_column: label in excel}
 * @param {object} option {beforeRenderItem: function(){}}
 * @return {null || mixed}
 */
rkGrapes.downloadCSV = function (fileName, data, titleHead, option) {
    option = option || {
        beforeRenderItem: null,
    };
    if (!data || !data.length) {
        throw 'data empty';
    }
    if (!window.Encoding) {
        throw 'Missing Encoding, please add encoding.js from https://github.com/polygonplanet/encoding.js (saved at "public/js/csv-helper/encoding.min.js")';
    }
    if (!window.saveAs) {
        throw 'Missing saveAs, please add FileSaver.js from https://github.com/eligrey/FileSaver.js (saved at "public/js/csv-helper/FileSaver.min.js")';
    }
    var csv = rkGrapes.convertArrayOfObjectsToCSV(data, titleHead, option),
        encoding = Encoding;
    if (!csv) {
        return false;
    }
    if (!fileName) {
        fileName = 'export.csv';
    }
    var strArray = encoding.stringToCode(csv);
    var sjisArray = encoding.convert(strArray, "SJIS", "UNICODE");
    var uint8Array = new Uint8Array(sjisArray);
    var blob = new Blob([uint8Array], {type: "text/csv"});
    saveAs(blob, fileName);
};

/**
 * alert
 * @param  {string||array} msg
 * @param {object} option
 * @param {function|null} callback
 */
rkGrapes.alert = function (msg, option, callback) {

    if (typeof option === 'function') {
        callback = option;
    }

    option = typeof option !== 'object' ? {} : '';

    rkGrapes.alert.hide = function() {
        $('body').find('#modal-alert').modal('hide').remove();
    };
    rkGrapes.alert.close = function() {
        this.hide();
    };

    if (typeof msg == 'object') {
        msg = msg.split('\n');
    }

    if (option.size) {
        switch (option.size) {
            case 'md':
                option.size = 'md';
                break;
            case 'lg':
                option.size = 'lg';
                break;
            default:
                option.size = 'sm';
                break;
        }
    } else {
        option.size = 'sm';
    }

    if (option.type) {
        switch (option.type) {
            case 'error':
            case 'danger':
                option.type = 'danger';
                break;
            case 'warning':
                option.type = 'warning';
                break;
            default:
                option.type = 'success';
                break;
        }
    } else {
        option.type = '';
    }

    if (callback && typeof callback == 'function') {
        rkGrapes.alert.close = function() {
            callback({
                hide: this.hide,
            });
        }
    }

    if ($('body').find('#modal-alert').length) {
        $('body').find('#modal-alert').remove();
    }

    $('body').append('<div class="modal" id="modal-alert" data-backdrop="static" data-keyboard="false"> <div class="modal-dialog modal-' + option.size + '"> <div class="modal-content"> <div class="modal-header"> <h4 class="modal-title">お知らせ</h4> </div><div class="modal-body text-' + option.type + '"> ' + msg + ' </div><div class="modal-footer"> <button type="button" class="btn btn-default" onclick="rkGrapes.alert.close()">Close</button> </div></div></div></div>');
    $('body').find('#modal-alert').modal('show');
};

/**
 * format object/array to object with key by key
 *
 * @param {object} obj
 * @param {string} key
 * @result {object}
 */
rkGrapes.formatByKey = function(obj, key) {
    var result = {};
    if (typeof key === 'undefined' || !key) {
        key = 'id';
    }
    $.each(obj, function(i,v) {
        result[v[key]] = v;
    });
    return result;
};

/**
 * get full name of user
 *
 * @param {object} item
 * @return {String}
 */
rkGrapes.getFullname = function(item) {
    if (typeof item !== 'object') {
        return '';
    }
    return (item.user_family_name ? item.user_family_name : '')
        + ' '
        + (item.user_first_name ? item.user_first_name : '');
};

/**
 * confirm popup
 * @param {string} msg
 * @param {function} callback: response{result, hide}
 * @param {object} option : noHideAutoYes
 */
rkGrapes.confirm = function (msg, callback, option) {
    option = typeof option === 'object' ? option : {
        autoHide: true,
    };
    var modalConfirm = $('#modal-confirm');
    if (!modalConfirm.length) {
        modalConfirm = $('<div class="modal" id="modal-confirm" data-backdrop="static" data-keyboard="false"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 class="modal-title">確認</h4></div><div class="modal-body"><p data-mconfirm="body"></p></div><div class="modal-footer"><button type="button" class="btn btn-default btn-sm btn-confirm-no" onclick="rkGrapes.confirm.no()">いいえ</button><button type="button" class="btn btn-primary btn-sm btn-confirm-yes" onclick="rkGrapes.confirm.yes()">はい</button></div></div></div></div>')
        $('body').append(modalConfirm);
        rkGrapes.confirm.hide = function() {
            if ($('#modal-confirm').length) {
                $('#modal-confirm').modal('hide');
            }
        };
        rkGrapes.confirm.yes = function () {
            this.hide();
        };
        rkGrapes.confirm.no = function () {
            this.hide();
        };
    }
    if (typeof callback === 'function') {
        rkGrapes.confirm.yes = function() {
            if (option.autoHide) {
                this.hide();
            }
            return callback({
                result: true,
                hide: this.hide,
            });
        };
        rkGrapes.confirm.no = function() {
            if (option.autoHide) {
                this.hide();
            }
            return callback({
                result: false,
                hide: this.hide,
            });
        };
    }
    modalConfirm.find('[data-mconfirm="body"]').html(msg);
    modalConfirm.modal('show');
};

/**
 * file input alias span name
 */
rkGrapes.fileAttachAs = function() {
    var flagInput = '[data-cust-attach="input"]',
    flagNameAs = '[data-cust-attach="file-name"]';
    $(document).on('change', flagInput, function(event) {
        var domInput = $(this),
        domNameAs = domInput.siblings(flagNameAs);
        if (!event.target.value ||
            !event.target.files ||
            !event.target.files.length
        ) {
            domInput.val('');
            domNameAs.text('');
            return true;
        }
        if (domInput.data('flag-valid') && !domInput.valid()) {
            domInput.val('');
            return true;
        }
        domNameAs.text(event.target.value.split( '\\' ).pop());
    });
};

/**
 * html encode
 *
 * @param  {string} text
 * @return {string}
 */
rkGrapes.htmlEncode = function (text) {
    return $('<div/>').text(text).html();
}

/**
 * html decode
 *
 * @param  {string} text
 * @return {string}
 */
rkGrapes.htmlDecode = function (text) {
    return $('<div/>').html(text).text();
}

/*
    click modal to hide
 */
$(document).ready(function() {
    $('body').on('click', '#popup', function() {
        $(this).modal('hide').remove();
    });
});

/**
 * ajax error callback
 */
$( document ).ajaxError(function() {
    /*if (!rkGrapes.ajaxErrorDisableIntenal) {
        rkGrapes.showPopup('Server intenal error!', 'error');
    }*/
    if (window.NProgress) {
        NProgress.done();
    }
});


if (window.NProgress) {
    /*
        start progress bar when ajax start
     */
    $(document).ajaxStart(function() {
        if (!rkGrapes.ajaxBarDisable) {
            NProgress.start();
        }
    });

    /*
        end progress bar when ajax stop
     */
    $(document).ajaxStop(function() {
        NProgress.done();
    });
}

/**
 * action relate form
 */
rkGrapes.form = {
    resetError: function(form) {
        form.find('input, textarea, select').removeClass('error');
    },
};

rkGrapes.activeMenu();
rkGrapes.formAjax.init();
rkGrapes.removeFlashMessage();
window.rkGrapes = rkGrapes;
})(jQuery, window, document);
