try {
    /** pjax相关 */
    $.pjax.defaults.timeout = 3000;
    $.pjax.defaults.type = 'GET';
    $.pjax.defaults.container = '#pjaxContainer';
    $.pjax.defaults.fragment = '#pjaxContainer';
    $.pjax.defaults.maxCacheLength = 0;
    $(document).pjax('a:not(a[target="_blank"])', {	//
        container: '#pjaxContainer',
        fragment: '#pjaxContainer'
    });
    $(document).ajaxStart(function () {
        console.log(this);
        // ajax请求的时候显示顶部进度条
        if (adminDebug) {
            console.log('ajax请求开始');
        }
        NProgress.start();
    }).ajaxStop(function () {
        // ajax停止的时候结束进度条
        if (adminDebug) {
            console.log('ajax请求停止');
        }
        NProgress.done();
    });
} catch (e) {
    if (adminDebug) {
        console.log('初始化pjax报错，信息：' + e.message);
    }
}

$(document).on('pjax:timeout', function (event) {
    event.preventDefault();
});
$(document).on('pjax:send', function (xhr) {
    NProgress.start();
});
$(document).on('pjax:complete', function (xhr) {
    initToolTip();
    initImgViewer();
    setNavTab();
    viewCheckAuth();
    NProgress.done();
});
//列表页搜索pjax
$(document).on('submit', '.searchForm', function (event) {
    $.pjax.submit(event, '#pjaxContainer');
});

/** 表单验证 */
$.validator.setDefaults({
    errorElement: "span",
    errorClass: "help-block error",

    errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
        element.closest('.input-group').append(error);
        element.closest('.formInputDiv').append(error);
    },
    highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
    },
    unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
    },
    submitHandler: function (form) {
        if (adminDebug) {
            console.log('前端验证成功，开始提交表单');
        }
        submitForm(form);
        return false;
    }
});

/* 初始化 */
$(function () {
    viewCheckAuth();
    setNavTab();
    // 初始化提示
    initToolTip();
    // 初始化菜单点击高亮
    initMenuClick();
    // 初始化图片预览
    initImgViewer();

    let $body = $('body');
    /* 返回按钮 */
    $body.on('click', '.BackButton', function (event) {
        event.preventDefault();
        history.back();
    });

    /* 刷新按钮 */
    $body.on('click', '.ReloadButton', function (event) {
        event.preventDefault();
        $.pjax.reload();
    });


    /* 全屏按钮 */
    $body.on('click', '.FullScreenButton', function (event) {
        event.preventDefault();
        fullScreen();
        $('.FullScreenButton').hide();
        $('.ExitFullScreenButton').show();
    });

    /* 退出全屏按钮 */
    $body.on('click', '.ExitFullScreenButton', function (event) {
        event.preventDefault();
        exitFullscreen();
        $('.ExitFullScreenButton').hide();
        $('.FullScreenButton').show();
    });
    if (!adminDebug) {
        console.log = (function (oriLogFunc) {
            return function () {
            }
        })(console.log);
    }
});

// 设置tab激活选项卡
function setNavTab() {
    if ($('.NavTab').length === 1) {
        if (adminDebug) {
            console.log('选项卡初始化');
        }
        let hash = document.location.hash;
        if (hash) {
            $('.NavTab a[href="' + hash + '"]').tab('show');
        } else {
            $('.NavTab a:first').tab('show');
        }
        $('.NavTab .nav-item .nav-link').on('click', function () {
            document.location.hash = $(this).attr('href');
        })
    }
}

/* 清除搜索表单 */
function clearSearchForm() {
    let url_all = window.location.href;
    let arr = url_all.split('?');
    let url = arr[0];
    $.pjax({url: url, container: '#pjaxContainer'});
}

/**
 * 点击菜单高亮
 */
function initMenuClick() {
    $('.nav-sidebar li:not(.has-treeview) > a').on('click', function () {
        if (adminDebug) {
            console.log('点击了菜单');
        }
        $(this).addClass('active');
        let $parents = $(this).parents('li');
        $parents.find('a:first').addClass('active');
        $parents.siblings().find('a').removeClass('active');
        $parents.siblings().removeClass('active');
    });

    $('[data-toggle="popover"]').popover();
}

/**
 * 图片预览
 */
function initImgViewer() {
    $('.imgViewer').viewer({
        url: 'src',
        title: function (obj) {
            return obj.alt;
        }
    });
}


/**
 * 初始化提示
 */
function initToolTip() {
    // 提示泡
    $('[data-toggle="tooltip"]').tooltip({
        container: '#pjaxContainer',
        trigger: 'hover',
    });
}

/*列表中单个选择和取消*/
function checkThis(obj) {
    let id = $(obj).attr('value');
    if ($(obj).is(':checked')) {
        if ($.inArray(id, dataSelectIds) < 0) {
            dataSelectIds.push(id);
        }
    } else {
        if ($.inArray(id, dataSelectIds) > -1) {
            dataSelectIds.splice($.inArray(id, dataSelectIds), 1);
        }
    }

    let all_length = $("input[name='dataCheckbox']").length;
    let checked_length = $("input[name='dataCheckbox']:checked").length;
    if (all_length === checked_length) {
        $("#dataCheckAll").prop("checked", true);
    } else {
        $("#dataCheckAll").prop("checked", false);
    }
    if (adminDebug) {
        console.log('当前选中的ID：' + JSON.stringify(dataSelectIds));
    }
}

/*全部选择/取消*/
function checkAll(obj) {
    dataSelectIds = [];
    let all_check = $("input[name='dataCheckbox']");
    if ($(obj).is(':checked')) {
        all_check.prop("checked", true);
        $(all_check).each(function () {
            dataSelectIds.push(this.value);
        });
    } else {
        all_check.prop("checked", false);
    }
    if (adminDebug) {
        console.log('当前选中的ID：' + JSON.stringify(dataSelectIds));
    }
}


/**
 * 表单提交
 * @param form 表单dom
 * @param successCallback 成功回调
 * @param failCallback 失败回调
 * @param errorCallback 错误回调
 * @param showMsg 是否显示弹出信息
 * @returns {boolean}
 */
function submitForm(form, successCallback, failCallback, errorCallback, showMsg = true) {
    let loadT = layer.msg('正在提交，请稍候…', {icon: 16, time: 0, shade: [0.3, "#000"], scrollbar: false,});
    let action = $(form).attr('action');
    let method = $(form).attr('method');
    let data = new FormData($(form)[0]);
    if (adminDebug) {
        console.log('%c开始提交表单!', ';color:#333333');
        console.log('action:', action);
        console.log('method:', method);
        console.log('data:', data);
    }


    $.ajax({
            url: action,
            dataType: 'json',
            type: method,
            data: data,
            contentType: false,
            processData: false,
            complete: function () {
                if (adminDebug) {
                    console.log('表单ajax执行完毕');
                }
            },
            success: function (result) {
                layer.close(loadT);
                if (showMsg) {
                    layer.msg(result.msg, {
                        icon: result.code === 200 ? 1 : 2,
                        scrollbar: false,
                    });

                    setTimeout(function () {
                        parent.layer.closeAll();
                    }, 1000);
                }

                // 调试信息
                if (adminDebug) {
                    console.log('ajax请求成功!');
                    result.code === 200 ? console.log('%c业务返回成功', ';color:#00a65a') : console.log('%c业务返回失败', ';color:#f39c12');
                }
                if (result.code === 200) {
                    if (successCallback) {
                        // 如果有成功回调函数就走回调函数
                        successCallback(result);
                    } else {
                        // 没有回调函数跳转url
                        goUrl(result.url);
                    }
                } else {
                    if (failCallback) {
                        // 如果有失败回调函数就走回调函数
                        failCallback(result);
                    } else {
                        // 没有回调函数跳转url
                        goUrl(result.url);
                    }
                }
            },

            error: function (xhr, type, errorThrown) {
                layer.close(loadT);
                // 调试信息
                if (adminDebug) {
                    console.log('%c submit fail!', ';color:#dd4b39');
                    console.log("type:" + type + ",readyState:" + xhr.readyState + ",status:" + xhr.status);
                    console.log("errorThrown:" + errorThrown);
                    console.log("data:", data);
                }

                if (showMsg) {
                    showAjaxError(xhr, type, errorThrown);
                }

                if (errorCallback) {
                    errorCallback(xhr)
                }

            },
        }
    );
    return false;
}


/** 跳转到指定url */
function goUrl(url) {

    if (url === '' || url === undefined) {
        return;
    }

    //清除列表页选择的ID
    if (url !== 'url://current' && url !== 1) {
        dataSelectIds = [];
    }
    if (url === 'url://current' || url === 1) {
        console.log('Stay current page.');
    } else if (url === 'url://reload' || url === 2) {
        console.log('Reload current page.');
        // $.pjax.reload();
        parent.location.reload();

    } else if (url === 'url://close-refresh-UI' || url === 2) {
        console.log('close-refresh-UI current page.');
        parent.location.reload();
    } else if (url === 'url://back' || url === 3) {
        console.log('Return to the last page.');
        window.history.go(-1);
        $.pjax.reload('#pjaxContainer');
    } else if (url === 4 || url === 'url://close-refresh') {
        console.log('Close this layer page and refresh parent page.');
        let indexWindow = parent.layer.getFrameIndex(window.name);
        //先刷新父级页面
        parent.goUrl(2);
        //再关闭当前layer弹窗
        parent.layer.close(indexWindow);
    } else if (url === 5 || url === 'url://close-layer') {
        console.log('Close this layer page.');
        let indexWindow = parent.layer.getFrameIndex(window.name);
        parent.layer.close(indexWindow);
    } else if (url === "url://refresh") {
        // parent.location.reload();
        $(document).pjax('a[data-pjax]', '#pjaxContainer', {fragment: '#pjaxContainer', timeout: 10000});
        $.pjax.reload('#pjaxContainer');
    } else {
        console.log('Go to ' + url);
        try {
            $.pjax({
                url: url,
                container: '#pjaxContainer'
            });
        } catch (e) {
            window.location.href = url;
        }
    }
}

/**
 * ajax访问按钮
 * 例如元素为<a class="AjaxButton" data-confirm="1" data-type="1" data-url="disable" data-id="2" data-go="" ></a>
 * data-confirm为是否弹出提示，1为是，2为否。比如删除某条数据，data-confirm="1"就会弹出来提示
 * data-type为访问方式，1为直接ajax访问，例如删除操作。2是为打开layer窗口展示数据，例如查看操作日志详情
 * data-url为要访问的url
 * data-id为要操作的数据ID，可以填写正常的数据ID，例如data-id="2"，
 * 或者填写checked表示获取当前数据列表选择的ID，也就是取的变量dataSelectIds的值
 * data-go为操作完成后的跳转url，不设置此参数默认根据后台返回的url跳转
 * data-confirm-title为确认提示弹窗的标题 例如data-confirm-title="删除警告"
 * data-confirm-content为确认提示的内容 例如data-confirm-content="您确定要删除此数据吗？"
 * data-title 窗口显示的标题
 *
 */
$(function () {
    $('body').on('click', '.AjaxButton', function (event) {

        event.preventDefault();
        if (adminDebug) {
            console.log('点击Ajax请求按钮');
        }

        let dataData = {};
        // 动画
        let layerAnim = -1;
        // 是否弹出提示
        let layerConfirm = parseInt($(this).data("confirm") || 1);
        //访问方式，1为直接访问，2为layer窗口显示
        let layerType = parseInt($(this).data("type") || 1);
        //访问的url
        let url = $(this).data("url");
        //访问方式，默认post
        let layerMethod = $(this).data("method") || 'post';
        //访问成功后跳转的页面，不设置此参数默认根据后台返回的url跳转
        let go = $(this).data("go") || 'url://reload';

        //当为窗口显示时可定义宽度和高度
        let layerWith = $(this).data("width") || '80%';
        let layerHeight = $(this).data("height") || '60%';

        //窗口的标题
        let layerTitle = $(this).data('title');

        //窗口位置
        let layerOffset = $(this).data('offset') || 'auto';

        //当前操作数据的ID
        let dataId = $(this).data("id");

        //如果没有定义ID去查询data-data属性
        if (dataId === undefined) {
            dataData = $(this).data("data") || {};
        } else {
            if (dataId === 'checked') {
                if (dataSelectIds.length === 0) {
                    layer.msg('请选择要操作的数据', {icon: 2, scrollbar: false,});
                    return false;
                }
                dataId = dataSelectIds;
            }
            dataData = {"id": dataId};
        }

        if (typeof (dataData) != 'object') {
            dataData = JSON.parse(dataData);
        }

        /*需要确认操作*/
        if (layerConfirm === 1) {
            //提示窗口的标题
            let confirmTitle = $(this).data("confirmTitle") || '操作确认';
            //提示窗口的内容
            let confirmContent = $(this).data("confirmContent") || '您确定要执行此操作吗?';
            //再次确定提示窗口的标题
            let replaceConfirmTitle = $(this).data("replaceConfirmTitle") || '再次操作确认';
            //触发再次确定的操作按钮
            let replaceConfirmContent = $(this).data("replaceConfirmContent") || '再次确定要执行此操作吗?';
            //是否开启触发再次操作按钮(1=>开启 2=>关闭)
            let replaceConfirmType = $(this).data("replaceConfirmType") || 0;
            layer.confirm(confirmContent, {title: confirmTitle, closeBtn: 1, icon: 3}, function () {

                if (replaceConfirmType === 1) {
                    layer.confirm(replaceConfirmContent, {
                        title: replaceConfirmTitle,
                        closeBtn: 1,
                        icon: 3
                    }, function () {
                        //如果为直接访问
                        if (layerType === 1) {
                            ajaxRequest(url, layerMethod, dataData, go);
                        } else if (layerType === 2) {
                            let tempUrl = url;
                            if (url.indexOf("?") !== -1) {
                                tempUrl = url + "&request_type=layer_open&" + parseParam(dataData);
                            } else {
                                tempUrl = url + "?request_type=layer_open&" + parseParam(dataData)
                            }
                            layer.open({
                                type: 1,
                                area: [layerWith, layerHeight],
                                title: layerTitle,
                                offset: layerOffset,
                                anim: layerAnim,
                                closeBtn: 1,
                                shift: 0,
                                content: tempUrl,
                                scrollbar: false,
                            });
                        }
                    })
                } else {
                    //如果为直接访问
                    if (layerType === 1) {
                        ajaxRequest(url, layerMethod, dataData, go);
                    } else if (layerType === 2) {
                        //如果为打开窗口
                        openLayer(url, dataData, [layerWith, layerHeight, layerTitle, layerOffset])
                    }
                }
            });
        } else if (layerType === 1) {
            //直接请求
            ajaxRequest(url, layerMethod, dataData, go);
        } else if (layerType === 2) {
            //弹出窗口
            openLayer(url, dataData, [layerWith, layerHeight], layerTitle, layerOffset)

        }
    });
    $('.closeLayer').on('click', function () {

        parent.layer.closeAll()
    })

});

function openLayer(url, dataData, area = ['50%', '100%'], layerTitle = "添加数据", layerOffset = 'r') {
    //弹出窗口
    let tempUrl = url;
    if (url.indexOf("?") !== -1) {
        tempUrl = url + "&request_type=layer_open&" + parseParam(dataData);
    } else {
        tempUrl = url + "?request_type=layer_open&" + parseParam(dataData)
    }
    console.log('tempUrl', tempUrl)

    //用窗口打开
    layer.open({
        type: 2,
        area: area,
        title: layerTitle,
        offset: layerOffset,
        anim: 'slideLeft',
        closeBtn: 1,
        shift: 0,
        shadeClose: true,
        content: tempUrl,
        scrollbar: false,
    });
}

//获取url中的参数
function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
}

/**
 * ajax form_data 请求封装
 * @param url
 * @param method
 * @param formData
 * @param go
 */
function ajaxRequestFormData(url, method, formData, go) {
    let loadT = layer.msg('正在请求,请稍候…', {
        icon: 16,
        time: 0,
        offset: window.innerHeight / 2,
        shade: [0.3, '#000'],
        scrollbar: true,
    });
    $.ajax({
            url: url,
            dataType: 'json',
            type: method,
            data: formData,
            contentType: false,
            processData: false,
            complete: function () {

            },
            success: function (result) {
                layer.close(loadT);
                layer.msg(result.msg, {
                    icon: result.code === 200 ? 1 : 2,
                    scrollbar: false,
                });

                if (adminDebug) {
                    console.log('request success!');
                    if (result.code === 200) {
                        console.log('%c result success', ';color:#00a65a');
                    } else {
                        go = 'url://current';
                        console.log('%c result fail', ';color:#f39c12');
                    }
                }

                goUrl(go);
            },
            error: function (xhr, type, errorThrown) {

                layer.close(loadT);
                if (adminDebug) {
                    console.log('%c request fail!', ';color:#dd4b39');
                    console.log("url:" + url);
                    console.log("data:", data);
                }

                showAjaxError(xhr, type, errorThrown);

            }
        }
    );
}

/**
 * ajax请求封装
 * @param url 访问的url
 * @param method  访问方式
 * @param data  data数据
 * @param go 要跳转的url
 */
function ajaxRequest(url, method, data, go) {
    let loadT = layer.msg('正在请求,请稍候…', {icon: 16, time: 0, shade: [0.3, '#000'], scrollbar: false,});
    $.ajax({
            url: url,
            dataType: 'json',
            type: method,
            data: data,
            complete: function () {

            },
            success: function (result) {
                layer.close(loadT);
                layer.msg(result.msg, {
                    icon: result.code === 200 ? 1 : 2,
                    scrollbar: false,
                });

                if (adminDebug) {
                    console.log('request success!');
                    if (result.code === 200) {
                        console.log('%c result success', ';color:#00a65a');
                    } else {
                        go = 'url://current';
                        console.log('%c result fail', ';color:#f39c12');
                    }
                }

                goUrl(go);
            },
            error: function (xhr, type, errorThrown) {

                layer.close(loadT);
                if (adminDebug) {
                    console.log('%c request fail!', ';color:#dd4b39');
                    console.log("url:" + url);
                    console.log("data:", data);
                }

                showAjaxError(xhr, type, errorThrown);

                setTimeout(function() {
                    goUrl(go);
                }, 800);
            }
        }
    );
}


function showAjaxError(xhr, type, errorThrown) {
    let errorTitle;
    // 调试信息
    if (adminDebug) {
        console.log('xhr', xhr);
        console.log('errorThrown', errorThrown);
        console.log("type:" + type + ",readyState:" + xhr.readyState + ",status:" + xhr.status);
    }

    if (xhr.responseJSON.code !== undefined && xhr.responseJSON.code === 500) {
        errorTitle = xhr.responseJSON.msg;
    } else {
        errorTitle = '系统繁忙,状态码：' + xhr.status + ',参考信息：' + (xhr.responseJSON.message || xhr.responseJSON.msg || '');
    }

    layer.msg(errorTitle, {icon: 2, scrollbar: false,});
}


//改变每页数量
function changePerPage(obj) {
    if (adminDebug) {
        console.log('当前每页数量' + Cookies.get(cookiePrefix + 'admin_list_rows'));
    }
    Cookies.set(cookiePrefix + 'admin_list_rows', obj.value, {expires: 30});
    $.pjax.reload();
}

/** 处理url参数 **/
function parseParam(param) {
    // let paramStr = "";
    // if (param instanceof String || param instanceof Number || param instanceof Boolean) {
    //     paramStr += "&" + key + "=" + encodeURIComponent(param);
    // } else {
    //     $.each(param, function (i) {
    //         let k = key == null ? i : key + (param instanceof Array ? "[" + i + "]" : "." + i);
    //         paramStr += '&' + parseParam(this, k);
    //     });
    // }
    // return paramStr.substr(1);

    if (param) {
        let arr = []
        for (let k in param) {
            arr.push(`${k}=${param[k]}`)
        }
        console.log(arr.join('&'))
        return arr.join('&')

    }
}

/** 导出excel **/
function exportData(url) {
    let exportUrl = url || 'export';
    let openUrl = exportUrl + '?' + $("#searchForm").serialize();
    window.open(openUrl);

}

/** 全屏 **/
function fullScreen() {
    let element = document.documentElement;
    if (element.requestFullscreen) {
        element.requestFullscreen();
    } else if (element.msRequestFullscreen) {
        element.msRequestFullscreen();
    } else if (element.mozRequestFullScreen) {
        element.mozRequestFullScreen();
    } else if (element.webkitRequestFullscreen) {
        element.webkitRequestFullscreen();
    }

}

/** 退出全屏 **/
function exitFullscreen() {
    if (document.exitFullscreen) {
        document.exitFullscreen();
    } else if (document.msExitFullscreen) {
        document.msExitFullscreen();
    } else if (document.mozCancelFullScreen) {
        document.mozCancelFullScreen();
    } else if (document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
    }

}

/**
 * 退出
 */
function logout() {
    $.ajax({
            url: logoutUrl,
            dataType: 'json',
            type: 'POST',
            data: {},
            success: function (result) {
                if (adminDebug) {
                    console.log('退出成功', result);
                }
                goUrl(result.data.redirect);
            }
        }
    );
}

/**
 * 地图输入组建防止回车提交
 */
$(function () {
    let $mapKey = $(".mapKeywords");
    $mapKey.keyup(function (e) {
        if (e.keyCode === 13) {
            return false;
        }
    })
    $mapKey.keydown(function (e) {
        if (e.keyCode === 13) {
            return false;
        }
    })
    $mapKey.keypress(function (e) {
        if (e.keyCode === 13) {
            return false;
        }
    })
});

/** 检查视图内权限 */
function viewCheckAuth() {
    $('.viewCheckAuth').each(function () {
        let $obj = $(this);
        let haveAuth = parseInt($obj.data('auth'));
        if (adminDebug) {
            console.log('检查元素权限', haveAuth);
        }
        if (haveAuth === 1 && !$obj.is(":visible")) {
            $obj.show();
        } else {
            $obj.hide();
        }
    });
}


//滚动条位置
function scrollPosition() {
    let t, l, w, h;
    if (document.documentElement && document.documentElement.scrollTop) {
        t = document.documentElement.scrollTop;
        l = document.documentElement.scrollLeft;
        w = document.documentElement.scrollWidth;
        h = document.documentElement.scrollHeight;
    } else if (document.body) {
        t = document.body.scrollTop;
        l = document.body.scrollLeft;
        w = document.body.scrollWidth;
        h = document.body.scrollHeight;
    }
    return {
        top: t,
        left: l,
        width: w,
        height: h
    };
}

//城市列表
function getCityList(callback) {
    $.get('/admin/district/list', function (res) {
        const cityList = res.data
        callback(cityList)
    })
}

