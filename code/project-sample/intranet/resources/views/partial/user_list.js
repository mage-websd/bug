(function($, rkGrapes) {
var userVar = typeof UserGlob === 'object' ? UserGlob : {},
trans = typeof userVar.trans === 'object' ? userVar.trans : {};
var collectionPager = rkGrapes.pager.init().setCollection(userVar.collectionPager);

/**
 * before exec render item of collection pager
 *
 * @param {type} item
 * @return {unresolved}
 */
rkGrapes.pager.beforeRenderItem = function(item) {
    if (typeof item.account_role_id !== 'undefined' &&
        item.account_role_id !== null &&
        typeof userVar.roles[item.account_role_id] !== 'undefined'
    ) {
        item.role_name = userVar.roles[item.account_role_id] ? 
            userVar.roles[item.account_role_id] : '';
    }
    if (item.status) {
        item.status = 1;
    } else {
        item.status = 0;
    }
    item.user_name = rkGrapes.getFullname(item);
    item.enable_label = userVar.activeLabel[item.status];
    return item;
};
rkGrapes.pager.exec(collectionPager);

/**
 * call back after success
 *
 * @param {type} dom
 * @param {type} data
 * @return {undefined}
 */
rkGrapes.userExport = function(dom, response) {
    rkGrapes.downloadCSV (
        'user_export.csv',
        response.data,
        {
            login_id: trans['Login ID'],
            user_family_name: trans['User family name'],
            user_first_name: trans['User first name'],
            user_kana_family_name: trans['User kana family name'],
            user_kana_first_name: trans['User kana first name'],
            email: trans['Mail address'],
            id: trans['System id'],
            tdy_id: trans['Reservation system ID'],
            password_flag: trans['Password'],
            account_role_id: trans['Authority type'],
            sr_code: trans['Showroom code'],
            ad_code: trans['Ad code'],
            display_order: trans['Display order'],
            status: trans['Status'],
        }
    );
};
/**
 * event click for export csv
 */
/*$('.btn-csv-download').on('click', function() {
    var _this = $(this);
    _this.attr('disabled', '');
    _this.find('i').attr('class', 'fa fa-spinner fa-spin');
    $.ajax({
        url: exportCSVUrl,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
        data: {
            'sr_id': $('[data-s-input=s-sr]').val(),
            'name': $('[data-s-input=s-name]').val(),
            'login_id': $('[data-s-input=s-login_id]').val(),
            'status': $('[data-s-input=s-status]').val(),
        },
        success: function(res) {
            rkGrapes.downloadCSV('file_name.csv', res.data);
            _this.removeAttr('disabled');
            _this.find('i').attr('class', 'fa fa-download');
        },
        error: function(err) {
            _this.removeAttr('disabled');
            _this.find('i').attr('class', 'fa fa-download');
        },
    });
});*/

/**
 * event click btn-csv-upload
 */
/*$('.btn-csv-upload').on('click', function() {
    $('#csv-upload').click();
});*/

})(jQuery, rkGrapes);
