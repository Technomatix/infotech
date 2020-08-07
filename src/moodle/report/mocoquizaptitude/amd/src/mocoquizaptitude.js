define(["jquery", "jqueryui-i18n", "select2", "loadingoverlay", "datetimepicker", "timepicker-i18n", "bootstrap", "placeholder", "recalctable"], function ($) {
    var mocoquizaptitude = {
        init: function ($crnt_lng) {
            actionButtonsLock();
            rewriteSelectFilters($crnt_lng);
            $.LoadingOverlaySetup({
                maxSize            : "50px",
                color   : "rgba(255, 255, 255, 0.5)"
            });

            $("#botNavBtn, #topNavBtn").on('click',function(){
                $.recalculateTableWidth();
            });

            $('#filterCourse').on('change', function (){ checkRequired(); });

            $('#filterName').on('change', function (){ checkRequired(); });

            $('#filtersBody').on('click', '#filterGetReportButton', function(e) {
                if(!$(e.target).hasClass('disabled')){
                    searchFilter();
                }
            });

            $('#filtersBody').on('click', '#filterExportButton', function() {
                if($(e.target).hasClass('disabled')){
                    return;
                }
                var url = 'index.php?csv=1&getreport=1';
                if ($('#filterCourse').length && $('#filterCourse').val()) {
                    url = url+'&courseid='+$('#filterCourse').val();
                }
                if ($('#filterTopDivision').length && $('#filterTopDivision').val()) {
                    url = url+'&d='+$('#filterTopDivision').val();
                }
                if ($('#filterName').length && $('#filterName').val()) {
                    url = url+'&u='+$('#filterName').val();
                }

                window.location.href = url;
            });
        }
    };

    function searchFilter() {
        $.LoadingOverlay("show");
        var filter = '';
        if ($('#filterCourse').length && $('#filterCourse').val()) {
            filter = filter+'&courseid='+$('#filterCourse').val();
        }
        if ($('#filterTopDivision').length && $('#filterTopDivision').val()) {
            filter = filter+'&d='+$('#filterTopDivision').val();
        }
        if ($('#filterName').length && $('#filterName').val()) {
            filter = filter+'&u='+$('#filterName').val();
        }
        $('.tableSection').load('?getreport=1'+filter, function () {
            $.LoadingOverlay("hide");
            $.recalculateTableWidth();
        });
    }

    function rewriteSelectFilters($crnt_lng) {
        $('label[for=filterName]').html($('label[for=filterName]').text() + '<span style="color:red;">&nbsp;*</span>');
        $('select#filterTopDivision').select2('destroy');
        $('select#filterTopDivision').select2({
            minimumInputLength: 1,
            containerCssClass: 'new-container-select2',
            language: $crnt_lng,
            multiple: false,
            maximumSelectionSize: 10,
            width:"95%",
            ajax: {
                url: 'ajax.php',
                dataType: 'json',
                quietMillis: 100,
                data: function (params) {
                    return {
                        s: 'getsubdivisions',
                        q: params.term,
                        page_limit: 10,
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    var more = (data.items.length === parseInt(data.limit));
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: more
                        }
                    };
                }
            },
            scapeMarkup: function (m) {
                return m;
            }
        });
        $('select#filterName').select2('destroy');
        $('select#filterName').select2({
            minimumInputLength: 1,
            containerCssClass: 'new-container-select2',
            language: $crnt_lng,
            multiple: false,
            maximumSelectionSize: 10,
            width:"95%",
            ajax: {
                url: 'ajax.php',
                dataType: 'json',
                quietMillis: 100,
                data: function (params) {
                    return {
                        s: 'getsubpersons',
                        q: params.term,
                        page_limit: 10,
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    var more = (data.items.length === parseInt(data.limit));
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: more
                        }
                    };
                }
            },
            scapeMarkup: function (m) {
                return m;
            }
        });
    }

    function actionButtonsLock(){
        $('#filterGetReportButton').addClass('disabled');
        $('#filterExportButton').addClass('disabled');
    }

    function actionButtonsUnlock(){
        $('#filterGetReportButton').removeClass('disabled');
        $('#filterExportButton').removeClass('disabled');
    }

    function checkRequired() {
        if($('#filterCourse').val() !== null && $('#filterName').val() !== null) {
            actionButtonsUnlock()
        }else{
            actionButtonsLock()
        }
    }

    return mocoquizaptitude;
});
