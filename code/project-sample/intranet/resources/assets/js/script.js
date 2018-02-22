/**
 * option custom
 */
var optionCustomR = {
    size: {
        adminlte_sm: 767,
        custom_sm: 1195
    }
};
var hash = window.location.hash;
(function($) {
    /**
     * active tab by hash
     */
    hash && $('ul.nav a[href="' + hash + '"]').tab('show');
    $('.nav-tabs a').click(function (e) {
        $(this).tab('show');
        var scrollmem = $('body').scrollTop() || $('html').scrollTop();
        window.location.hash = this.hash;
        $('html,body').scrollTop(scrollmem);
    });
    if (typeof RKfuncion === 'undefined') {
        RKfuncion = {};
    }
    /**
     * General function
     */
    RKfuncion.general = {
        parseHtml: function (note) {
            if (typeof note == 'undefined' || note.trim() == '') {
                return '';
            }
            return $.parseHTML(note.trim())[0].nodeValue
        },
        btnSubmitHref: function() {
            $('.btn-submit-href').click(function(event) {
                event.preventDefault();
                var href = $(this).data('href');
                if (!href || !$(href).length || !$(href).is('form')) {
                    return true;
                }
                $(href).submit();
            });
        },
        modalBodyPadding: function(flagCss) {
            if (typeof flagCss == 'undefined' || !flagCss) {
                flagCss = '.modal';
            }
            var paddingRight = $('body').css('padding-right');
            $(flagCss).on('hidden.bs.modal', function (e) {
                $('body').css('padding-right', paddingRight);
                if ($(flagCss).is(':visible')) {
                    setTimeout(function() {
                        $('body').addClass('modal-open');
                    },500);
                }
            });
        },
        removeBlock: function () {
            $(document).on('click touchstart', '.remove-block-click', function(event) {
                event.preventDefault();
                $(this).closest('.remove-block-wrapper').remove();
            });
        },
        reloadBlockAjax: function(dom) {
            /**
             * reload block by ajax
             */
            if (!dom.length) {
                return false;
            }
            dom.each(function() {
                var __thisDom = $(this);
                __thisDom.find('.block-loading-icon').removeClass('hidden');
                $.ajax({
                    url: __thisDom.data('url'),
                    type: 'GET',
                    data: {},
                    dataType: 'json',
                    success: function(data) {
                        if (typeof data.html !== 'undefined') {
                            __thisDom.find('.grid-data-query-table').html(data.html);
                        }
                    },
                    complete: function() {
                        __thisDom.find('.block-loading-icon').addClass('hidden');
                    }
                });
            });
        },
        serializeDataBlock: function (dom) {
            var data = {};
            dom.find('[data-block-form="1"]').each(function() {
                data[$(this).attr('name')] = $(this).val();
            });
            return data;
        } 
    };
    
    RKfuncion.keepStatusTab = {
        classDom: '.tab-keep-status',
        init: function() {
            var __this = this;
            if (!$(__this.classDom).length) {
                return false;
            }
            $(__this.classDom).each(function() {
                __this._elementKeep($(this));
            });
        },
        _elementKeep: function(dom) {
            var __this = this;
            var type = dom.data('type');
            if (!type) {
                return false;
            }
            var keyCookie = __this.classDom + '-' + type;
            keyCookie = keyCookie.replace(/(\#|\?|\=|\&|\:|\/|\.)/g, '');
            dom.find('.nav-tabs:first > li > a').click(function() {
                if ($(this)[0].hasAttribute('data-toggle')) {
                    var href = $(this).attr('href').replace(/(\#|\?|\=|\&|\:|\/)/g, '');
                    $.cookie( keyCookie, href, { expires: 1, path: '/' });
                }
            });
        }
    };
    
    RKfuncion.formSubmitAjax = {
        classDom: '.form-submit-ajax',
        classButton: '.post-ajax',
        classDomChange: '.form-submit-change-ajax',
        init: function() {
            var __this = this;
            if (!$('.warning-action').length) {
                $('body').append('<button class="warning-action hidden"></button>');
            }
            if (!$('.success-action').length) {
                $('body').append('<button class="success-action hidden"></button>');
            }
            $(document).on('submit', __this.classDom, function(event) {
                event.preventDefault();
                __this._elementSubmit($(this), 1);
            });
            $(document).on('change', __this.classDomChange + ' input', function(event) {
                event.preventDefault();
                __this._elementSubmit($(this), 3);
            });
            $(document).on('click', __this.classButton, function(event) {
                event.preventDefault();
                var _thisButton = $(this);
                if (_thisButton.hasClass('delete-confirm') ||
                    _thisButton.hasClass('warn-confirm')) {
                    setTimeout(function() {
                        if (_thisButton.hasClass('process')) {
                            return true;
                        }
                        __this._elementSubmit(_thisButton, 2);
                    }, 300);
                } else {
                    __this._elementSubmit(_thisButton, 2);
                }
            });
        },
        _elementSubmit: function(dom, type) {
            var __this = this;
            switch (type) {
                case 1: // submit form
                    var btnSubmit = dom.find('[type=submit]:not(.no-disabled)'),
                        data = dom.serialize(),
                        url = dom.attr('action'),
                        loadingRefresh = dom.find('.submit-ajax-refresh');
                    break;
                case 2: // button click
                    var btnSubmit = dom,
                        data = {
                            _token: siteConfigGlobal.token
                        };
                        url = dom.data('url-ajax'),
                        loadingRefresh = dom.find('.submit-ajax-refresh-btn');
                    if(dom.is('[data-block-form-submit="1"]')) {
                        var blockFormData = dom.closest('.block-form-data');
                        if (blockFormData.length) {
                            data = $.extend(data, RKfuncion.general.serializeDataBlock(blockFormData));
                        }
                    } else if(dom.hasClass('is-submit-report')) {
                        data = $("#form-dashboard-point :input.pp-input").serialize() +
                            '&_token='+siteConfigGlobal.token;
                    }
                    break;
                case 3: // form change submit
                    var btnSubmit = dom,
                        inputName = dom.attr('name'),
                        inputValue = dom.val(),
                        data = {
                            _token: siteConfigGlobal.token,
                            data: {}
                        },
                        domWrapper = dom.closest(__this.classDomChange),
                        url = domWrapper.data('url-ajax'),
                        loadingRefresh = domWrapper.find('.submit-ajax-refresh');
                        data.data[inputName] = inputValue;
                    break;
                default: 
                    return true;
            }
            if (dom.hasClass('has-valid')) {
                if (!dom.valid()) {
                    dom.find('[type=submit]').removeAttr('disabled');
                    return true;
                }
            }
            if (btnSubmit.attr('requestRunning')) {
                return;
            }
            btnSubmit.attr('requestRunning', true);
            btnSubmit.attr('disabled', 'disabled');
            btnSubmit.find('.btn-submit-main').addClass('hidden');
            btnSubmit.find('.btn-submit-refresh').removeClass('hidden');
            loadingRefresh.removeClass('hidden');
            $.ajax({
                url: url,
                type: 'post',
                dataType: 'json',
                data: data,
                success: function (data) {
                    if (typeof data.success !== 'undefined' && data.success == 1) {
                        if (typeof data.popup == 'undefined' || data.popup != 1) {
                            // show popup success
                            if (typeof data.message !== 'undefined' && data.message) {
                                $('.success-action').attr('data-noti', data.message);
                            }
                            $('.success-action').trigger('click');
                            if (typeof data.refresh !== 'undefined' && data.refresh) {
                                $('#modal-success-notification').on('hide.bs.modal', function() {
                                    window.location.href = data.refresh;
                                });
                            } else {
                                var callbackSuccess = dom.data('callback-success');
                                if (callbackSuccess && 
                                    typeof RKfuncion.formSubmitAjax[callbackSuccess] != 'undefined'
                                ) {
                                    RKfuncion.formSubmitAjax[callbackSuccess](dom, data);
                                }
                            }
                            // replace data html dom
                            if (typeof data.htmlDom != 'undefined') {
                                $.each(data.htmlDom, function(i,k) {
                                    if ($(i).length) {
                                        $(i).html(k);
                                    }
                                });
                            }
                            // add class for dom
                            if (typeof data.addClassDom != 'undefined') {
                                $.each(data.addClassDom, function(i,k) {
                                    if ($(i).length) {
                                        $(i).addClass(k);
                                    }
                                });
                            }
                            // remove class for dom
                            if (typeof data.removeClassDom != 'undefined') {
                                $.each(data.removeClassDom, function(i,k) {
                                    if ($(i).length) {
                                        $(i).removeClass(k);
                                    }
                                });
                            }
                        } else if (typeof data.reload !== 'undefined' && data.reload) {
                            window.location.reload();
                        } else if (typeof data.refresh !== 'undefined' && data.refresh) {
                            window.location.href = data.refresh;
                        } else if (typeof data.reloadBlockAjax !== 'undefined' && data.reloadBlockAjax) {
                            if (data.reloadBlockAjax instanceof Array) {
                                var i;
                                for (i in data.reloadBlockAjax) {
                                    RKfuncion.general.reloadBlockAjax($(data.reloadBlockAjax[i]));
                                }
                            } else if (typeof data.reloadBlockAjax === 'string'){
                                RKfuncion.general.reloadBlockAjax($([data.reloadBlockAjax]));
                            } else {
                                window.location.reload();
                            }
                            $('.modal').each(function() {
                                if ($(this).is(':visible')) {
                                    $(this).modal('hide');
                                }
                            });
                            /* myTask */ 
                            var callbackSuccess = dom.data('callback-success');
                            if (callbackSuccess && 
                                typeof RKfuncion.formSubmitAjax[callbackSuccess] != 'undefined'
                            ) {
                                RKfuncion.formSubmitAjax[callbackSuccess](dom, data);
                            }
                            /*End my Task*/
                            
                        } else {
                            var callbackSuccess = dom.data('callback-success');
                            if (callbackSuccess && 
                                typeof RKfuncion.formSubmitAjax[callbackSuccess] !== 'undefined'
                            ) {
                                RKfuncion.formSubmitAjax[callbackSuccess](dom, data);
                            }
                        }
                    } else {
                        var callbackError = dom.data('callback-error');
                        if (callbackError && 
                            typeof RKfuncion.formSubmitAjax[callbackError] !== 'undefined'
                        ) {
                            RKfuncion.formSubmitAjax[callbackError](dom, data);
                        } else if (typeof data.message !== 'undefined' && data.message) {
                            $('.warning-action').attr('data-noti', data.message);
                            $('.warning-action').trigger('click');
                        } else {
                            $('.warning-action').trigger('click');
                        }
                    }
                    btnSubmit.find('.btn-submit-main').removeClass('hidden');
                    btnSubmit.find('.btn-submit-refresh').addClass('hidden');
                    loadingRefresh.addClass('hidden');
                    if (typeof data.refresh == 'undefined' || !data.refresh) {
                        btnSubmit.removeAttr('disabled');
                        btnSubmit.removeAttr('requestrunning');
                    }
                },
                error: function() {
                    loadingRefresh.addClass('hidden');
                    btnSubmit.removeAttr('disabled');
                    btnSubmit.find('.btn-submit-main').removeClass('hidden');
                    btnSubmit.find('.btn-submit-refresh').addClass('hidden');
                    $('.warning-action').trigger('click');
                },
                complete: function () {
                    btnSubmit.removeAttr('requestrunning');
                    if(dom.hasClass('is-submit-report')) {
                        $('.is-report').find('.submit-ajax-refresh-btn').addClass('hidden');
                    }
                }
            });
        }
    };
    RKfuncion.radioToggleClickShow = {
        init: function() {
            var __this = this;
            $('.radio-toggle-click-wrapper').find('.radio-toggle-click-show')
                .addClass('hidden');
            $('.radio-toggle-click-wrapper input.radio-toggle-click').each (function () {
                __this._eachItem($(this));
            });
            $('.radio-toggle-click-wrapper input.radio-toggle-click').click(function() {
                __this._eachItem($(this));
            });
        },
        _eachItem: function (dom) {
            var toogleWrapper = dom.closest('.radio-toggle-click-wrapper'),
                id = dom.attr('id');
            if (dom.is(':checked')) {
                toogleWrapper.find('.radio-toggle-click-show')
                    .addClass('hidden');
                toogleWrapper.find('.radio-toggle-click-show[data-id="' + id + '"]')
                    .removeClass('hidden');
            }
        }
    };
    
    RKfuncion.fixHeightWindow = {
        init: function(wrapperFlag) {
            var windowHeight = $(window).outerHeight(),
            bodyHeight = $('body').outerHeight();
            if (typeof wrapperFlag != undefined) {
                var wrapper = $(wrapperFlag).outerHeight();
            }
            if (bodyHeight < windowHeight) {
                $('body').height(windowHeight);
                if (typeof wrapperFlag != undefined) {
                    $(wrapperFlag).height(windowHeight);
                }
            }
        }
    };
    RKfuncion.CKEditor = {
        init: function (arrayIdDom, ckfinder) {
            var ckEditorReturn = {};
            CKEDITOR.config.removePlugins = 'elementspath,save,font,wsc,scayt,undo';
            CKEDITOR.config.extraPlugins = 'justify,colorbutton,indentblock';
            if (typeof arrayIdDom == 'undefined' || !arrayIdDom || !arrayIdDom.length) {
                return true;
            }
            var indexDom;
            for (indexDom in arrayIdDom) {
                ckEditorReturn[arrayIdDom[indexDom]] = CKEDITOR.replace( arrayIdDom[indexDom] );
                if (typeof ckfinder != 'undefined' && ckfinder) {
                    CKFinder.setupCKEditor( ckEditorReturn[arrayIdDom[indexDom]], '/lib/ckfinder' );
                }
            }
            $('.btn-submit-ckeditor').click(function() {
                for (indexDom in arrayIdDom) {
                    $('#' + arrayIdDom[indexDom]).val(CKEDITOR.instances[arrayIdDom[indexDom]].getData());
                }
            });
            return ckEditorReturn;
        }
    };
    RKfuncion.select2 = {
        option: {},
        init: function (option) {
            if (typeof $().select2 == 'undefined') {
                return true;
            }
            var __this = this,
                optionDefault = {
                    showSearch: false
                };
            option = $.extend(optionDefault, option);
            __this.option = option;
            if (option.enforceFocus) {
                try {
                    $.fn.modal.Constructor.prototype.enforceFocus = function(){};
                } catch(e) {}
            }
            $('.select-search').each(function(){
                if ($(this).data('remote-url')) {
                    __this.elementRemote($(this), option);
                } else {
                    __this.element($(this), option);
                }
            });
        },
        element: function(dom, option) {
            if (typeof $().select2 == 'undefined') {
                return true;
            }
            var __this = this,
                optionDefault = {
                    showSearch: false
                };
            option = $.extend(optionDefault, option);
            __this.option = option;
            if (dom.hasClass('has-search')) {
                dom.select2();
            } else {
                dom.select2({
                    minimumResultsForSearch: Infinity
                });
            }
            var text = dom.find('option:selected').text().trim();
            dom.siblings('.select2-container')
                .find('.select2-selection__rendered').text(text);
            dom.on('select2:select', function () {
                var text = $(this).find('option:selected').text().trim();
                $(this).siblings('.select2-container').find('.select2-selection__rendered').text(text);
            });
        },
        elementRemote: function(dom, option) {
            if (typeof $().select2 == 'undefined') {
                return true;
            }
            /*
             * response need id and text, format
             * 
             *  {
             *      incomplete_results: true
             *      items:[
             *          {id: 1, text: "show"},
             *          {id: 1, text: "show"}
             *      ],
             *      total_count: 2
             */
            var __this = this,
                optionDefault = {
                    delay: 500,
                    minimumInputLength: 2
                };
            option = $.extend(optionDefault, option);
            __this.option = option;
            dom.select2({
                id: function(response){ 
                    return response.id;
                },
                minimumInputLength: 2,
                ajax: {
                    url: dom.data('remote-url'),
                    dataType: 'json',
                    delay: option['delay'],
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 20) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) { 
                    return markup; 
                }, // let our custom formatter work
                templateResult: __this.__formatReponse, // omitted for brevity, see the source of this page
                templateSelection: __this.__formatReponesSelection // omitted for brevity, see the source of this page
            });
            /*var text = dom.find('option:selected').text().trim();
            dom.siblings('.select2-container')
                .find('.select2-selection__rendered').text(text);
            dom.on('select2:select', function () {
                var text = $(this).find('option:selected').text().trim();
                $(this).siblings('.select2-container').find('.select2-selection__rendered').text(text);
            });*/
        },
        __formatReponse: function (response) {
            if (response.loading) {
                return response.text;
            }
            return markup = "<div class='select2-result-repository clearfix'>" +
                    "<div class='select2-result-repository__title'>" + response.text + "</div>" +
                "</div>";
          },
        __formatReponesSelection: function (response) {
            return  response.text;
        }
    };
    
    /**
     * match box height
     */
    RKfuncion.boxMatchHeight = {
        option: {},
        init: function (option) {
            var optionDefault = {
                width: 991, //min width to do action,
                parent: '',
                children: [],
                center: []
            },
            __this = this;
            __this.option = $.extend(optionDefault, option);
            if (!$(__this.option.parent).length || !__this.option.children.length) {
                return true;
            }
            __this.initWindow();
            $(window).load(function () {
                __this.initWindow();
            });
            $(window).resize(function () {
                __this.initWindow();
            });
            return __this;
        },
        initWindow: function () {
            var __this = this;
            __this.resetStyles();
            if ($(window).outerWidth() > __this.option.width) {
                $(__this.option.parent).each(function () {
                    __this.matchBoxParent($(this));
                });
            }
            return __this;
        },
        matchBoxParent: function (domParent) {
            var __this = this,
                    keyIndex,
                    flagChild,
                    heightBox;
            for (keyIndex in __this.option.children) {
                flagChild = __this.option.children[keyIndex];
                if (!domParent.find(flagChild)) {
                    continue;
                }
                heightBox = 0;
                domParent.find(flagChild).each(function () {
                    heightBox = $(this).height() > heightBox ? $(this).height() : heightBox;
                });
                if (heightBox > 0) {
                    domParent.find(flagChild).each(function () {
                        var heightChildCurrent = $(this).height();
                        $(this).height(heightBox);
                        // margin top if box center
                        if (__this.option.center.indexOf(flagChild) != -1) {
                            $(this).children().first().css('margin-top', (heightBox - heightChildCurrent) / 2)
                                    .css('display', 'block');
                        }
                    });
                }
            }
            return __this;
        },
        resetStyles: function () {
            var __this = this,
                    keyIndex;
            for (keyIndex in __this.option.children) {
                $(__this.option.children[keyIndex]).removeAttr('style');
            }
            for (keyIndex in __this.option.center) {
                $(__this.option.center[keyIndex]).each(function(){
                    $(this).children().first().removeAttr('style');
                });
            }
            return __this;
        }
    };
    
    /**
     * bootstrap-multiselect
     * 
     * -- class wrapper team-dropdown
     */ 
    RKfuncion.bootstapMultiSelect = {
        flagClass: '.bootstrap-multiselect',
        optionGlobal: {},
        init: function(option) {
            var __this = this,
            optionDefault = {
                includeSelectAllOption: false,
                nonSelectedText: 'Choose items',
                allSelectedText: 'All',
                nSelectedText: 'items selected',
                numberDisplayed: 3,
                enableFiltering: true,
                enableCaseInsensitiveFiltering: true,
                onChange: function(optionChange) {
                    __this._removeSpace(optionChange.closest('select'));
                },
                onDropdownShown: function(event) {
                    __this._overfollow($(event.currentTarget));
                },
                onDropdownHide: function(event) {
                    __this._overfollowClose($(event.currentTarget));
                }
            };
            option = $.extend(optionDefault, option);
            __this.optionGlobal = option;
            $(__this.flagClass).multiselect(option);
            setTimeout(function () {
                $(__this.flagClass).each(function() {
                    __this._removeSpace($(this));
                });
            }, 100);
        },
        _removeSpace: function(selectDom, limit) {
            var __this = this,
            selectedOptions = selectDom.find('option:selected');
            if (typeof limit == 'undefined') {
                limit = __this.optionGlobal.numberDisplayed;
            }
            if (selectedOptions.length > limit) {
                return true;
            }
            var textSelected = '';
            selectedOptions.each(function (index) {
                if (index === 0) {
                    textSelected += $(this).text().trim();
                } else {
                    textSelected += ', ' + $(this).text().trim();
                }
            });
            if (textSelected) {
                setTimeout(function() {
                    selectDom.parent().find('.btn-group .multiselect-selected-text').text(textSelected);
                    selectDom.parent().find('.btn-group button.multiselect').attr('title', textSelected);
                }, 50);
            }
        },
        _overfollow: function (dom) {
            var wrapper = dom.closest('.multiselect2-wrapper.flag-over-hidden');
            if (wrapper.length) {
                wrapper.height(wrapper[0].scrollHeight);
            }
        },
        _overfollowClose: function(dom) {
            var wrapper = dom.closest('.multiselect2-wrapper.flag-over-hidden');
            if (wrapper.length) {
                wrapper.removeAttr('style');
            }
        }
    };
    
    /**
     * jquery validate extend validator function
     */
    RKfuncion.jqueryValidatorExtend = {
        greater: function() {
            if (typeof $.validator !== 'undefined' && 
                typeof $.validator.addMethod != 'undefined'
            ) {
                $.validator.addMethod('greater', function(value, element, param) {
                    return this.optional(element) || value > $(param).val();
                }, 'Please enter a value greater');
            }
        },
        lesser: function() {
            if (typeof $.validator !== 'undefined' && 
                typeof $.validator.addMethod != 'undefined'
            ) {
                $.validator.addMethod('lesser', function(value, element, param) {
                    return this.optional(element) || value < $(param).val();
                }, 'Please enter a value lesser');
            }
        },
        greaterEqual: function() {
            if (typeof $.validator !== 'undefined' && 
                typeof $.validator.addMethod != 'undefined'
            ) {
                $.validator.addMethod('greaterEqual', function(value, element, param) {
                    return this.optional(element) || value >= $(param).val();
                }, 'Please enter a value greater');
            }
        },
        lesserEqual: function() {
            if (typeof $.validator !== 'undefined' && 
                typeof $.validator.addMethod != 'undefined'
            ) {
                $.validator.addMethod('lesserEqual', function(value, element, param) {
                    return this.optional(element) || value <= $(param).val();
                }, 'Please enter a value lesser');
            }
        }
    };
    
    /**
     * function add items and delete item
     * class container: add-items-container
     * class wrapper include items:  add-items-wapper
     * class wrapper button add: add-items-btn-add
     * class template: add-items-template
     * class button delete: add-items-btn-delete
     * class item: add-items-item
     * flag id incremt: xxx
     */
    RKfuncion.addItems = {
        indexIncrement: 0,
        itemNewOrgHtml: '',
        flagContainer: '.add-items-container',
        flagWrapper: '.add-items-wapper',
        flagBtnAdd: '.add-items-btn-add',
        flagTemplate: '.add-items-template',
        flagItem: '.add-items-item',
        flagBtnDelete: '.add-items-btn-delete',
        dataProcess: {},
        option: {},
        init: function(option) {
            var __this = this;
            __this.dataProcess = {
                flagWrapper: __this.flagContainer + ' ' + __this.flagWrapper,
                flagBtnAdd: __this.flagContainer + ' ' + __this.flagBtnAdd,
                flagTemplate: __this.flagContainer + ' ' + __this.flagTemplate,
                flagItem: __this.flagContainer + ' ' + __this.flagItem,
                flagBtnDelete: __this.flagContainer + ' ' + __this.flagBtnDelete
            };
            if (!$(__this.flagContainer).length || 
                !$(__this.flagContainer + ' ' + __this.flagWrapper).length || 
                !$(__this.flagContainer + ' ' + __this.flagBtnAdd).length || 
                !$(__this.flagContainer + ' ' + __this.flagTemplate).length || 
                !$(__this.flagContainer + ' ' + __this.flagItem).length
            ) {
                return false;
            }
            var domContainer = $(__this.flagContainer);
            __this.itemNewOrgHtml = domContainer.find(__this.flagTemplate).html();
            domContainer.find(__this.flagTemplate).remove();
            // add new item to wrapper
            var quotationNewClone = __this.itemNewOrgHtml.replace(/xxx/g, __this.indexIncrement);
            __this.indexIncrement++;
            domContainer.find(__this.flagWrapper).append(quotationNewClone);
            __this._checkDeleteItem(domContainer);
            // option init
            if (typeof option == 'undefined') {
                option = {};
            }
            __this.option = option;
            // call action
            __this._addAction();
            __this._deleteAction();
        },
        _addAction: function() {
            var __this = this;
            $(document).on('click', __this.dataProcess.flagBtnAdd, function(event) {
                event.preventDefault();
                var quotationNewClone = __this.itemNewOrgHtml.replace(/xxx/g, __this.indexIncrement),
                    domContainer = $(this).closest(__this.flagContainer);
                domContainer.find(__this.flagTemplate).remove();
                __this.indexIncrement++;
                
                domContainer.find(__this.flagWrapper)
                    .append(quotationNewClone);
                __this._checkDeleteItem(domContainer);
            });
        },
        _deleteAction: function() {
            var __this = this;
            $(document).on('click', __this.dataProcess.flagBtnDelete, function(event) {
                event.preventDefault();
                var domContainer = $(this).closest(__this.flagContainer);
                domContainer.find(__this.flagTemplate).remove();
                $(this).closest(__this.flagItem).remove();
                __this._checkDeleteItem(domContainer);
            });
        },
        _checkDeleteItem: function(domContainer) {
            var __this = this;
            if (__this.option.isAllowDeleteAll) {
                return true;
            }
            if (domContainer.find(__this.flagItem).length > 1) {
                domContainer.find(__this.flagBtnDelete).removeClass('hidden');
            } else {
                domContainer.find(__this.flagBtnDelete).addClass('hidden');
            }
        }
    };
/**
 * get team dev tree path
 */
RKfuncion.teamTree = {
    treePath: {},
    teamDev: [],
    treeParentTeamDev: [],
    html: null,
    selectedIds: [],
    init: function (treePath, selectedIds) {
        this.treePath = treePath;
        if (typeof selectedIds === 'undefined' || !selectedIds) {
            selectedIds = [];
        }
        this.selectedIds = selectedIds;
        this.html = [];
        this._getTeamDev();
        this._getOptionRecursive(0, 0);
        return this.html;
    },
    // call recursive to call option select
    _getOptionRecursive: function(idParent, level) {
        var __this = this;
        if (typeof __this.treePath[idParent] === 'undefined' ||
            !__this.treePath[idParent].child.length
        ) {
            return null;
        }
        var index, jndex, nameOption, itemChild, disabled, idChild,
            children = __this.treePath[idParent].child;
        for (index in children) {
            idChild = children[index];
            itemChild = __this.treePath[idChild];
            disabled = false;
            if (typeof __this.treePath[idChild] === 'undefined') {
                continue;
            }
            // not dev team && parent not dev team
            if (__this.treeParentTeamDev.indexOf(idChild) === -1 && 
                !itemChild.data.is_soft_dev
            ) {
                continue;
            }
            // not is soft dev, in tree team dev => disabled
            if (!itemChild.data.is_soft_dev) {
                disabled = true;
            }
            nameOption = '';
            for (jndex = 0; jndex < level; jndex++) {
                nameOption += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            nameOption += itemChild.data.name;
            __this.html.push({
                id: idChild,
                label: RKfuncion.general.parseHtml(nameOption),
                disabled: disabled,
                selected: __this.selectedIds.indexOf(idChild) > -1
            });
            __this._getOptionRecursive(idChild, level+1);
        }
    },
    /**
     * get all id team format tree avai dev
     */
    _getTeamDev: function() {
        var __this = this, index, item, j;
        for (index in __this.treePath) {
            item =__this.treePath[index];
            if (item.data.is_soft_dev) {
                // push dev team
                __this.teamDev.push(index);
                //push dev parents dev team
                for (j in item.parent) {
                    if (__this.treeParentTeamDev.indexOf(item.parent[j]) === -1) {
                        __this.treeParentTeamDev.push(item.parent[j]);
                    }
                }
            }
        }
        return __this.teamDev;
    }
};
})(jQuery);

/**
 * select2 reload and trim text result
 */
function selectSearchReload(option) {
    optionDefault = {
        showSearch: false
    };
    option = jQuery.extend(optionDefault, option);
    if (option.showSearch) {
        jQuery(".select-search").select2();
    } else {
        jQuery(".select-search.has-search").select2();
        jQuery(".select-search:not(.has-search)").select2({
            minimumResultsForSearch: Infinity
        });
    }
    
    jQuery('.select-search').each(function(i,k){
        var text = jQuery(this).find('option:selected').text().trim();
        jQuery(this).siblings('.select2-container').find('.select2-selection__rendered').text(text);
    });
    jQuery('.select-search').on('select2:select', function (evt) {
        var text = jQuery(this).find('option:selected').text().trim();
        jQuery(this).siblings('.select2-container').find('.select2-selection__rendered').text(text);
    });
}

/**
 * get date format
 * 
 * @param {datetime} date
 * @param {string} format
 * @returns {String}
 */
function getDateFormat(date, format) {
    if (format == 'Y') {
        return date.getFullYear();
    }
    if (format == 'M/Y') {
        return (date.getMonth() + 1 ) + '/' + date.getFullYear();
    }
    return '';
}

/**
 * get array format unicode
 * 
 * @param {array} arrayData
 * @returns {Array}
 */
function getArrayFormat(arrayData) {
    if (! arrayData) {
        return [];
    }
    for (i in arrayData) {
        for (iItem in arrayData[i]) {
            if (arrayData[i][iItem] != undefined && arrayData[i][iItem]) {
                arrayData[i][iItem] = jQuery.parseHTML(arrayData[i][iItem])[0].nodeValue;
            }
        }
    }
    return arrayData;
}

(function($) {
    $.fn.clickWithoutDom = function(option) {
        if (option == undefined || 
            option.container == undefined || 
            option.except == undefined
        ) {
            return false;
        }
        if (option.type == undefined) {
            option.type = 0;
        }
        $(document).mouseup(function (e){
            if (option.except.is(e.target)
                || option.except.has(e.target).length !== 0
            ){
                return false;
            } else if (! option.container.is(e.target)
                && option.container.has(e.target).length === 0
            ){
                switch (option.type) {
                    case 1:
                        $("body").removeClass('sidebar-open').removeClass('sidebar-collapse').trigger('collapsed.pushMenu');
                        break;
                    case 2:
                        if (typeof $.AdminLTE != 'undefined') {
                            $.AdminLTE.controlSidebar.close($('.right-sidebar-control'),true);
                        }
                        break;
                    case 3:
                        return true;
                }
            }
        });
        $(document).keyup(function(e) {
            if (e.keyCode == 27) {
                switch (option.type) {
                    case 1:
                        $("body").removeClass('sidebar-open').removeClass('sidebar-collapse').trigger('collapsed.pushMenu');
                        break;
                    case 2:
                        if (typeof $.AdminLTE != 'undefined') {
                            $.AdminLTE.controlSidebar.close($('.right-sidebar-control'),true);
                        }
                        break;
                    case 3:
                        return true;
                }
            }
        });
    };
    
    /**
     * check click without dom
     */
    var clicky;
    $(document).mousedown(function(e) {
        clicky = e.target;
    });
    $(document).mouseup(function() {
        clicky = null;
    });
    $.fn.isClickWithoutDom = function(option) {
        if (option == undefined || 
            option.container == undefined || 
            option.except == undefined
        ) {
            return false;
        }
        if (option.type == undefined) {
            option.type = 0;
        }
        if (option.except.is(clicky)
            || option.except.has(clicky).length !== 0
        ){
            return false;
        } else if (! option.container.is(clicky)
            && option.container.has(clicky).length === 0
        ) {
            return true;
        }
    };
})(jQuery);

jQuery(document).ready(function ($) {
//    $('ul.dropdown-menu [data-toggle=dropdown]').on('click', function (event) {
//        event.preventDefault();
//        event.stopPropagation();
//        if ($(this).parent().hasClass('open')) {
//            $(this).parent().removeClass('open');
//            $(this).parent().find('li.dropdown-submenu').removeClass('open');
//        } else {
//            $(this).parent().addClass('open');
//        }
//    });
    
    //modal delete confirm  '.delete-confirm'
    function modalDeleteConfirm(flagClassButton) {
        $('.' + flagClassButton).removeAttr('disabled');
        var buttonClickShowModal;
        $(document).on('click touchstart', '.' + flagClassButton, function (event) {
            if($(this).hasClass('process')) { //check flag processed
                return true;
            }
            event.preventDefault();
            buttonClickShowModal = $(this);
            $(this).addClass('process'); //set flag processing cofirm
            $('#modal-' + flagClassButton).modal('show');
        });
        $('#modal-' + flagClassButton).on('show.bs.modal', function (e) {
            $(this).find('.modal-footer .btn-ok').show();
            var notification = buttonClickShowModal.data('noti');
            warning = buttonClickShowModal.attr('data-warning');
            if(warning && buttonClickShowModal.hasClass('is-disabled')) {
                $(this).find('.modal-body .text-change').show().html(warning);
                $(this).find('.modal-body .text-default').hide().html(warning);
                $(this).find('.modal-footer .btn-ok').hide();
            } else {
                if (notification) {
                    $(this).find('.modal-body .text-change').show().html(notification);
                    $(this).find('.modal-body .text-default').hide().html(notification);
                } else {
                    $(this).find('.modal-body .text-change').hide();
                    $(this).find('.modal-body .text-default').show();
                }
            }
        });
        $('#modal-' + flagClassButton).on('hide.bs.modal', function (e) {
            buttonClickShowModal.removeClass('process'); //remove flag processing cofirm
        });
        $('#modal-' + flagClassButton + ' .modal-footer button').on('click touchstart', function (e) {
            if ($(this).hasClass('btn-ok')) {
                buttonClickShowModal.trigger('click');
                $('#modal-' + flagClassButton).modal('hide');
                return true;
            }
            $('#modal-' + flagClassButton).modal('hide');
            return false;
        });
    }
    modalDeleteConfirm('delete-confirm');
    modalDeleteConfirm('warn-confirm');
    
    /**
     * model warning, success
     */
    var buttonClickShowModalWarning;
    $(document).on('click touchstart', '.warning-action, .success-action', function () {
        buttonClickShowModalWarning = $(this);
        if (buttonClickShowModalWarning.hasClass('warning-action')) {
            $('#modal-warning-notification').modal('show');
        } else {
            $('#modal-success-notification').modal('show');
        }
        return false;
    });
    $('#modal-warning-notification, #modal-success-notification').on('show.bs.modal', function () {
        if (typeof notification === 'undefined') {
            var notification;
        }
        if (buttonClickShowModalWarning && buttonClickShowModalWarning.length) {
            notification = buttonClickShowModalWarning.attr('data-noti');
        }
        if (notification) {
            $(this).find('.modal-body .text-change').show().html(notification);
            $(this).find('.modal-body .text-default').hide().html(notification);
        } else {
            $(this).find('.modal-body .text-change').hide();
            $(this).find('.modal-body .text-default').show();
        }
        buttonClickShowModalWarning = null;
    });
    
    /**
     * form input dropdown
     */
    $('.form-input-dropdown .input-menu a').click(function(event) {
        event.preventDefault();
        var textHtml = $(this).html();
        var dataValue = $(this).data('value');
        $(this).closest('.form-input-dropdown').find('.input-show-data span').html(textHtml);
        $(this).closest('.form-drop-wrapper').find('input').removeAttr('disabled');
        $(this).closest('.form-input-dropdown').find('.input').val(dataValue);
    });
});

jQuery(document).ready(function($) {
    /* filter-grid action */
    // get params from filter input
    function getSerializeFilter(dom)
    {
        var filterUrl = dom.closest('.filter-wrapper').data('url');
        var urlSubmitFilter = typeof filterUrl == 'undefined' ? currentUrl : filterUrl;
        var valueFilter, nameFilter, params;
        params = '';
        $('.filter-grid').each(function(i,k) {
            valueFilter = $(this).val();
            nameFilter = $(this).attr('name');
            if (valueFilter && nameFilter) {
                if (valueFilter instanceof Array) {
                    var valueFilterItem;
                    for (valueFilterItem in valueFilter) {
                        params += nameFilter + '=' + $.trim(valueFilter[valueFilterItem]) + '&';
                    }
                } else {
                    params += nameFilter + '=' + $.trim(valueFilter) + '&';
                }
            }
        });
        params += 'current_url=' + urlSubmitFilter;
        return params;
    }    
    //filter request with param filter
    function filterRequest(dom)
    {
        data = getSerializeFilter(dom);
        $('.btn-search-filter .fa').removeClass('hidden');
        $.ajax({
            url: baseUrl + 'grid/filter/request',
            type: 'GET',
            data: data,
            success: function() {
                window.location.reload();
            }
        });
    }
    //filter pager with param filter
    function filterPager(dataSubmit, domWrapper)
    {
        if (dataSubmit == undefined) {
            dataSubmit = {};
        }
        if (typeof domWrapper == 'undefined') {
            domWrapper = $('.table-grid-data:first');
        }
        //filter grid data ajax have pagination
        if (typeof domWrapper !== 'undefined' && domWrapper.parents('.grid-data-query').length) {
            domWrapperParent = domWrapper.parents('.grid-data-query');
            domWrapperParent.find('.block-loading-icon').removeClass('hidden');
            urlSubmit = domWrapperParent.attr('data-url');
            gridAjax = true;
        } else { //filter grid data
            domWrapperParent = domWrapper.parents('.box:first');
            urlSubmit = baseUrl + 'grid/filter/pager';
            gridAjax = false;
        }
        if (dataSubmit.page == undefined || ! dataSubmit.page) {
            dataSubmit.page = parseInt(domWrapperParent.find('.grid-pager .form-pager input[name=page]').val());
        }
        if (dataSubmit.dir == undefined || ! dataSubmit.dir) {
            if (domWrapperParent.find('.form-dir-order input[name=dir]').val()) {
                dataSubmit.dir = domWrapperParent.find('.form-dir-order input[name=dir]').val();
            }
        }
        if (dataSubmit.order == undefined || ! dataSubmit.order) {
            if (domWrapperParent.find('.form-dir-order input[name=order]').val()) {
                dataSubmit.order = domWrapperParent.find('.form-dir-order input[name=order]').val();
            }
        }
        var filterUrl = domWrapper.closest('.filter-wrapper').data('url');
        var urlSubmitFilter = typeof filterUrl == 'undefined' ? currentUrl : filterUrl;
        dataSubmit.limit = domWrapperParent.find('.grid-pager select[name=limit] option:selected').data('value');
        if (!gridAjax) {
            dataSubmit = {'filter_pager': dataSubmit, 'current_url': urlSubmitFilter};
        }
        $('.btn-search-filter .fa').removeClass('hidden');
        $.ajax({
            url: urlSubmit,
            type: 'GET',
            data: dataSubmit,
            dataType: 'json',
            success: function(data) {
                if (!gridAjax) {
                    window.location.reload();
                } else {
                    if (typeof data.html != 'undefined') {
                        domWrapperParent.find('.grid-data-query-table').html(data.html);
                    }
                    domWrapperParent.find('.fa-refresh').addClass('hidden');
                }
            },
            complete: function() {
                domWrapperParent.find('.block-loading-icon').addClass('hidden');
                domWrapperParent.find('.fa-refresh').addClass('hidden');
            }
        });
    }
    
    //input filter grid key down - request filter action
    $(document).on('keydown','input.filter-grid',function(event) {
        if(event.which == 13) {
            filterRequest($(this));
            return false;
        }
    });

    // input filter grid change - request filter action
    $(document).on('change','select.select-grid',function(event) {
        filterRequest($(this));
    });
    RKfuncion.filterGrid = {
        filterRequest: function(dom) {
            filterRequest(dom);
        }
    };
    
    //reset filter
    $(document).on('click touchstart','.btn-reset-filter',function(event) {
        $('.btn-reset-filter .fa').removeClass('hidden');
        var filterUrl = $(this).closest('.filter-wrapper').data('url');
        var urlSubmitFilter = typeof filterUrl == 'undefined' ? currentUrl : filterUrl;
        $.ajax({
            url: baseUrl + 'grid/filter/remove',
            type: 'GET',
            data: 'current_url=' + urlSubmitFilter,
            success: function() {
                window.location.reload();
            }
        });
        return false;
    });
    
    //search filter button
    $(document).on('click touchstart','.btn-search-filter',function(event) {
        filterRequest($(this));
        return false;
    });
    
    //pager 
    $(document).on('change', '.grid-pager select[name=limit]', function(event) {
        event.preventDefault();
        filterPager({
            page: 1
        }, $(this));
    });
    $(document).on('click touchstart', '.grid-pager .pagination a', function(event) {
        if ($(this).hasClass('disabled') || $(this).parent().hasClass('disabled')) {
            return false;
        }
        page = parseInt($(this).data('page'));
        if (page) {
            event.preventDefault();
            filterPager({
                page: page
            }, $(this));
        }
    });
    $(document).on('keypress', '.grid-pager .pagination .form-pager input[name=page]', function(event) {
        if (event.keyCode == 13) {
            event.preventDefault();
            page = parseInt($(this).val());
            filterPager({
                page: page
            }, $(this));
        }
    });
    
    //sort order
    $('.sorting').on('click touchstart', function(event) {
        order = $(this).data('order');
        dir = $(this).data('dir');
        if (! order || ! dir) {
            return;
        }
        filterPager({
            order: order,
            dir: dir
        }, $(this));
    });
    
    /* ---- endfilter-grid action */
    
    //menu mobile
    
    $('.main-header .dropdown-menu').on('mouseover', function(event) {
        $(this).parents('li.dropdown').addClass('hover');
    });
    $('.main-header .dropdown-menu').on('mouseleave', function(event) {
        $(this).parents('li.dropdown').removeClass('hover');
    });
    $(window).load(function() {
        var domOpenChild = '<i class="fa fa-angle-left pull-right"></i>',
            menuMobileClone = $('#navbar-collapse .navbar-nav').clone();
        $('#navbar-collapse .navbar-nav > li').hover(function() {
            $(this).siblings('li').removeClass('open');
        });
        menuMobileClone.find('li:has(ul)',this).each(function() {
            $(this).children('a').append(domOpenChild);
            $(this).children('a').removeAttr('class').removeAttr('data-toggle').removeAttr('aria-expanded');
            $(this).addClass('treeview');
            $(this).removeClass('dropdown');
            $(this).removeClass('dropdown-submenu');
            $(this).children('ul').removeClass('dropdown-menu');
            $(this).children('ul').addClass('treeview-menu');
        });
        $('.main-sidebar .sidebar .sidebar-menu').html(menuMobileClone.html());
        if (typeof $.AdminLTE != 'undefined') {
            $.AdminLTE.layout.fix();
        }

        $('.sidebar-toggle').click(function(event) {
            windowWidth = $(window).width();
            if (windowWidth > optionCustomR.size.adminlte_sm) {
                if ($("body").hasClass('sidebar-open')) {
                    $("body").removeClass('sidebar-open').removeClass('sidebar-collapse').trigger('collapsed.pushMenu');
                } else {
                    $("body").addClass('sidebar-open').trigger('expanded.pushMenu');
                }
            }
        });

        $('.main-sidebar .sidebar-menu  li.treeview  a').on('click touchstart', function(event) {
            windowHeight = $(window).height();
            sidebarHeight = $(".sidebar").height();
            contentHeight = $(".content-wrapper").height();
            if (! $(this).parent().hasClass('active')) { //menu open
                if (windowHeight >= sidebarHeight) {
                    setTimeout(function () {
                        $(".content-wrapper").css('min-height', windowHeight);
                    }, 600);
                }
            } else {
                if (windowHeight < sidebarHeight) {
                    $(".content-wrapper, .right-side").css('min-height', sidebarHeight);
                }
            }
        });

        //menu setting
        menuMobileSettingClone = $('.main-header .navbar-custom-menu li.setting.dropdown > ul.dropdown-menu').clone();
        menuMobileSettingClone.find('li:has(ul)',this).each(function() {
            $(this).children('a').append(domOpenChild);
            $(this).children('a').removeAttr('class').removeAttr('data-toggle').removeAttr('aria-expanded');
            $(this).addClass('treeview');
            $(this).removeClass('dropdown');
            $(this).removeClass('dropdown-submenu');
            $(this).children('ul').removeClass('dropdown-menu');
            $(this).children('ul').addClass('treeview-menu');
        });
        $('.control-sidebar .sidebar .sidebar-menu').html(menuMobileSettingClone.html());
        
        $().clickWithoutDom({
            container: $("aside.main-sidebar"),
            except: $('.sidebar-toggle'),
            type: 1
        });
        $().clickWithoutDom({
            container: $("aside.right-sidebar-control"),
            except: $('.menu-setting-sidebar'),
            type: 2
        });
    });
    //------------------end menu mobile
    
    /* table click tr */
    $('.tr-clickable > td:not(.tr-td-not-click)').click(function() {
        window.location.href = $(this).parent('tr').data('url');
    });
    /* ---------------end table click tr */
    
    var topBtnClick = $('.top-up');
    topBtnClick.click(function(event) {
        event.preventDefault();
        $('body, html').animate({
            scrollTop: 0
          }, 500);
    });
    if (topBtnClick.length) {
        if ($(window).scrollTop() < 100) {
            topBtnClick.stop().animate({
                'bottom': '-100px'
            }, 600);
        }
        $(window).scroll(function() {
            if ($(this).scrollTop() > 100) {
                topBtnClick.stop().animate({
                    'bottom': '20px'
                }, 300);
            } else {
                topBtnClick.stop().animate({
                    'bottom': '-100px'
                }, 300);
            }
        });
    }
});

/*  table thead fixed  */ 
(function($){
    function calculatorWidthThead(domTable) {
        tbodyTrFirst = domTable.children('tbody');
        if (! tbodyTrFirst.length) {
            return;
        }
        tbodyTrFirst = tbodyTrFirst.children('tr:nth-child(2)');
        if (! tbodyTrFirst.length) {
            return;
        }
        tbodyTrFirst = tbodyTrFirst.children('td');
        if (! tbodyTrFirst.length) {
            return;
        }
        width = {};
        tbodyTrFirst.each(function(i) {
            width[i] = $(this).width();
        });
        return width;
    }
    
    function fixThead(thisWrapper, THeadDom, tdTheadDom) {
        thisWrapper.removeClass('fixing');
        topHeightThead = THeadDom.offset().top;
        $(window).scroll(function() {
            topScroll = $(window).scrollTop();
            if (topScroll > topHeightThead) {
                thisWrapper.addClass('fixing');
                widthTd = calculatorWidthThead(thisWrapper);
                if (! widthTd) {
                    thisWrapper.removeClass('fixing');
                } else {
                    tdTheadDom.each(function(i) {
                        $(this).width(widthTd[i]);
                    });
                }
            } else {
                thisWrapper.removeClass('fixing');
                tdTheadDom.removeAttr('style');
            }
        });
    }
    
    $.fn.tableTHeadFixed = function(object) {
        var thisWrapper = $(this),
            THeadDom = thisWrapper.children('thead');
        if (! THeadDom.length) {
            return;
        }
        tdTheadDom = THeadDom.children('tr');
        if (! tdTheadDom.length) {
            return;
        }
        tdTheadDom = tdTheadDom.children();
        if (! tdTheadDom.length) {
            return;
        }
        
        fixThead(thisWrapper, THeadDom, tdTheadDom);
        $(window).load(function() {
            if (thisWrapper.hasClass('fixing')) {
                widthTd = calculatorWidthThead(thisWrapper);
                tdTheadDom.each(function(i) {
                    $(this).width(widthTd[i]);
                });
            }
        });
        
        $(window).resize(function() {
            fixThead(thisWrapper, THeadDom, tdTheadDom);
        });
    };
})(jQuery);
/* -----end table thead fixed  */ 

(function($){
    //dom vertical center
    $.fn.verticalCenter = function(option) {
        var thisWrapper = $(this);
        optionDefault = {
            parent: true
        };
        option = $.extend(optionDefault, option);
        if (option.parent === true) {
            parentDom = thisWrapper.parent();
        } else {
            parentDom = $(option.parent);
            if (! parentDom.length) {
                return;
            }
        }
        heightParent = parentDom.outerHeight();
        heightThis = thisWrapper.outerHeight();
        placeHeight = heightParent / 2 - heightThis / 2;
        if (placeHeight < 0) {
            placeHeight = 0;
        }
        thisWrapper.css('margin-top', placeHeight + 'px');
        $(window).resize(function() {
            heightParent = parentDom.outerHeight();
            heightThis = thisWrapper.outerHeight();
            placeHeight = heightParent / 2 - heightThis / 2;
            if (placeHeight < 0) {
                placeHeight = 0;
            }
            thisWrapper.css('margin-top', placeHeight + 'px');
        });
    }; //end dom vertical center
    
    // preview image
    $.fn.previewImage = function(option) {
        var thisWrapper = $(this);
        if (option == undefined || ! option) {
            option = {};
        }
        srcDemo = thisWrapper.find('.image-preview > img').attr('src');
        optionDefault = {
            type: [ 'image/jpeg','image/png','image/gif'],
            size: 2048,
            default_image: srcDemo,
            message_size: 'File size is large',
            message_type: 'File type dont allow',
        };
        option = $.extend(optionDefault, option);
        //exec src image preview
        
        //var allowType = ['image/jpeg','image/png','image/gif'];
        var domInputFile = thisWrapper.find(".img-input input[type=file]");
        function readURL(input) {
            if (input.files && input.files[0]) {
                var fileUpload = input.files[0];
                if($.inArray(fileUpload.type, option.type) < 0) {
//                    thisWrapper.find('.image-preview > img').attr('src', option.default_image);
                    domInputFile.val('');
                    alert(option.message_type);
                } else if (fileUpload.size / 1000 > option.size) {
                    thisWrapper.find('.image-preview > img').attr('src', option.default_image);
                    domInputFile.val('');
                    alert(option.message_size);
                }
                else {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        thisWrapper.find('.image-preview > img').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(fileUpload);
                }
            }
        }
        domInputFile.change(function(){
            readURL(this);
        });
    };
    
    //tooltip dom
    $('[data-tooltip="true"]').tooltip();
    
    //submit form disable
    $('form').submit(function () {
        if ($(this).hasClass('no-disabled') || $(this).hasClass('form-pager')) {
            //not action
        } else if ($(this).hasClass('no-validate')) {
            $(this).find('[type=submit]:not(.no-disabled)').attr('disabled', 'disabled');
        } else {
            if ($(this).valid()) {
                $(this).find('[type=submit]:not(.no-disabled)').attr('disabled', 'disabled');
            } else {
                $(this).find('[type=submit]').removeAttr('disabled');
            }
        }
    });
    
    /**
     * reset form validation
     * 
     * @param array option array form
     */
    $.fn.formResetVaidation = function(option) {
        if (option == undefined || ! option || ! option.length) {
            return;
        }
        for (i in option) {
            if (option[i] == undefined || ! option[i]) {
                continue;
            }
            option[i].resetForm();
            formCurrent = option[i].currentForm;
            if (formCurrent && formCurrent.length && $(formCurrent).length) {
                $(formCurrent).find('input, textarea').removeClass('error');
            }
        }
    };
    
    /**
     * get serialize param of inputs
     * 
     * @param {object} option
     * @returns {unresolved|String}
     */
    $.fn.getFormDataSerializeFilter = function (option) {
        if (option == undefined || 
            option.dom == undefined || 
            !option.dom ||
            !option.dom.length
        ) {
            return null;
        }
        dataParams = {};
        option.dom.each(function(i,k) {
            valueFilter = $.trim($(this).val());
            nameFilter = $(this).attr('name');
            if (valueFilter && nameFilter) {
                dataParams[nameFilter] = valueFilter;
            }
        });
        return $.param(dataParams);
    };
    
    /**
     * action button filter ajax action
     */
    $.fn.filterAjaxActionButton = function () {
        $(document).on('click touchstart','.filter-action .btn-reset-filter-ajax',function(event) {
            event.preventDefault();
            tableFilter = $(this).parent('.filter-action').data('table');
            tableFilter = '.' + tableFilter;
            if ($(tableFilter).length) {
                $(tableFilter).find('.filter-input-grid input.filter-grid-ajax').val('');
            }
        });
    };
    
    /**
     * caculator positoin menu
     */
    $('ul.dropdown-menu [data-toggle=dropdown]').on('click mouseenter', function(event) {
        // Avoid following the href location when clicking
        event.preventDefault(); 
        // Avoid having the menu to close when clicking
        event.stopPropagation(); 
        menu = $(this).siblings("ul:first");
        if (menu.length) {
            parent = $(this).parent();
            widthParent = parent.width();
            leftParent = parent.offset().left;
            menupos = $(menu).offset();
            if (widthParent + leftParent + menu.width() > $(window).width()) {
                menu.css({ left: -(widthParent-1) });
            } else {
                menu.css({ left: widthParent });
            }
        }
    });
    
    $.fn.selectText = function(){
        var doc = document;
        var element = this[0];
        if (doc.body.createTextRange) {
            var range = document.body.createTextRange();
            range.moveToElementText(element);
            range.select();
        } else if (window.getSelection) {
            var selection = window.getSelection();        
            var range = document.createRange();
            range.selectNodeContents(element);
            selection.removeAllRanges();
            selection.addRange(range);
        }
    };
    
    RKfuncion.formSubmitAjax.init();
    RKfuncion.general.modalBodyPadding();
})(jQuery);

function isEmail(value) {
    re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    return re.test(value);
}

/**
 * Input only number 
 */
$(document).on("keydown", ".num", function (e) {
    // Allow: backspace, delete, tab, escape, enter and .
    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
         // Allow: Ctrl+A, Command+A
        (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
         // Allow: home, end, left, right, down, up
        (e.keyCode >= 35 && e.keyCode <= 40)) {
             // let it happen, don't do anything
             return;
    }
    // Ensure that it is a number and stop the keypress
    var condition = (e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105);
    if ($(this).hasClass('num-with-forward-slash')) {
        if ($(this).val().indexOf("/") >= 0 && (e.keyCode == 191 || e.keyCode == 111)) {
            e.preventDefault();
        }
        condition = condition && e.keyCode != 191 && e.keyCode != 111;
    }
    if (condition) {
        e.preventDefault();
    }
});

/**
 * Rounding number to {digit} digits after comma
 * @param {float} number
 * @returns {float}
 */
function rounding(number, digit) {
    var n = parseFloat(number); 
    number = Math.round(n * 1000)/1000; 
    return number.toFixed(digit);
}

function numberWithCommas(x) {
    var parts = x.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}

/**
 * if word is numeric then format as salary
 * 
 * @param {object} elem
 */
function numberFormat(elem) {
    $(elem).keyup(function () {

        var value = $(this).val();
        value = value.replace(/,/g, ''); // remove commas from existing input
        var arrValue = value.split(" ");
        var length = arrValue.length;
        for (var i = 0; i < length; i++) {
            if(Math.floor(arrValue[i]) == arrValue[i] && $.isNumeric(arrValue[i])) {
                arrValue[i] = numberWithCommas(arrValue[i]);
            }
        }

        $(this).val(arrValue.join(" "));
    });
}

/**
 * addClass() to first position of multiple classes
 * @param {element} sel
 * @param {string} strClass
 */
function prependClass(sel, strClass) {
    var $el = jQuery(sel);

    /* prepend class */
    var classes = $el.attr('class');
    classes = strClass +' ' +classes;
    $el.attr('class', classes);
}

/**
 * Check duplicate element in array
 * 
 * @param {array} array
 * @returns {Boolean}
 */
function checkDuplicate(array) {
    var arrayTrim = [];
    $.each(array, function(){
        arrayTrim.push($.trim(this.toUpperCase()));
    });
    var array = arrayTrim.sort(); 
    var flag = false;
    for (var i = 0; i < array.length - 1; i++) {
        if (array[i + 1] == array[i]) {
            flag = true;
            break;
        }
    }
    return flag;
}

$("a:contains('Music')").closest('li').find('ul li a').each(function() {
    if($(this).text() == 'Order') {
        $(this).attr('target', '_blank');
    }
});
