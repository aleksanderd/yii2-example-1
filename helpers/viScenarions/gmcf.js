var queryResult = {
    'status': 50
};
var data, lines, cStats = {};
var settings = {
    mClientCallFailed: 'К сожалению, соединение не может быть установлено',
    mClientCallFailedAudio: 'none',
    mIncomingCall: 'Примите звонок с сайта',
    mIncomingCallAudio: 'none'
};
var voiceType = Language.RU_RUSSIAN_FEMALE;

// звонок манагеру, звонок клиенту, событие завершения звонка манагеру и клиенту (для статсов)
var mCall, cCall, me, ce;
var viLine = '78442459131';

function extend(a, b)
{
    for (var key in b) {
        if (b.hasOwnProperty(key)) {
            a[key] = b[key];
        }
    }
    return a;
}

function getTimestamp()
{
    var t = (new Date()).getTime() / 1000;
    t = t.toString();
    Logger.write(t);
    return t;
}

// основная точка входа
VoxEngine.addEventListener(AppEvents.Started, function(e) {
    data = JSON.parse(VoxEngine.customData());
    if (data.lines === undefined) {
        finish('Empty lines list');
    } else {
        if (data.settings !== undefined) {
            settings = extend(settings, data.settings);
        }
        if (settings.voiceType !== undefined && Language.hasOwnProperty(settings.voiceType)) {
            voiceType = Language[settings.voiceType];
        }
        if (settings.line !== undefined && settings.line !== null && settings.line.length > 0) {
            viLine = settings.line;
        }
        Logger.write('viLine: ' + viLine);
        lines = data.lines;
        callManager(lines);
    }
});

// точка входа для доступа к выполняемой сессии по http
VoxEngine.addEventListener(AppEvents.HttpRequest, function (e) {
    Logger.write('HTTP: ' + e.content);
    var qParts = e.content.split('&');
    var query = {};
    for (var i = 0; i < qParts.length; i++) {
        var p = qParts[i].split('=');
        query[decodeURIComponent(p[0])] = decodeURIComponent(p[1]);
    }

    var result = {
//    'lines': lines,
//    'cStats': cStats,
//    'me': me,
//    'ce': ce
//    'data': data
    };

    queryResult.lines = lines;
    queryResult.cStats = cStats;
//  queryResult.me = me;
    queryResult.ce = ce;

    if (query.cmd == 'terminate') {
        Logger.write('HTTP: Terminate request');
        VoxEngine.terminate();
    } else if (query.cmd == 'get-query-result') {
        return queryResult;
    } else {
        Logger.write('HTTP: Unknown cmd: ' + query.cmd);
    }
    return result;
});

// функция посылает запрос к серверу gmcf
function gwRequest(action, postData, callback) {
    if (data.gwUrl === undefined) {
        Logger.write('gwUrl undefined');
        return false;
    }
    var opts = new Net.HttpRequestOptions();
    opts.method = 'POST';
    var pData = 'query_id=' + encodeURIComponent(data.query_id);
    if (typeof postData == 'string') {
        pData = pData + '&' + postData;
    }
    opts.postData = pData;
    var url = data.gwUrl + '/vi-' + action;
    Logger.write('Requesting: ' + url);
    Net.httpRequest(url, function(response) {
        if (response.code == 200) {
            Logger.write('Request success: ' + url);
        } else {
            Logger.write('Request failed: ' + url);
        }
        if (typeof callback === 'function') {
            callback(response);
        }
    }, opts);
    return true;
}

var finished = false;
// функция завершает сценарий при этом пытаясь сообщить
// об этом серверу gmcf, чтобы тот забрал актуальные данные
function finish(msg) {
    if (finished) {
        return;
    }
    finished = true;
    queryResult.msg = msg;
    Logger.write('Finish: ' + msg );
    var pData = 'msg=' + encodeURIComponent(msg);
    var res = gwRequest('finish-query', pData, function(response) {
        if (response.code != 200) {
            VoxEngine.terminate();
        }
    });
    if (!res) {
        Logger.write('Finish: gateway undefined');
        VoxEngine.terminate();
    }
}

function normalizeNumber(info) {
    var result = info.trim();
    var replace = {'8': '7', '0': '972'};
    if (result.charAt(0) == '+') {
        result = result.substr(1);
    }
    for (var k in replace) {
        if (replace.hasOwnProperty(k) && result.substr(0, k.length) == k) {
            result = replace[k] + result.substr(k.length);
            break;
        }
    }
    return result;
}

function viCall(info) {
    var vl = viLine;
    info = normalizeNumber(info);
    //vl = '15182520885';
    var i0 = info.substring(0, 1);
    if (i0 == '7') {
        vl = '78442459131';
    } else if (i0 == '9') {
        if (info.substring(0, 3) == '972') {
            vl = '97233728611';
        }
    }
    Logger.write('Calling: ' + info + ', via line: ' + vl);
    return VoxEngine.callPSTN(info, vl);
}

// запускает звонок менеджеру
function callManager() {
    var li = -1;
    for (var i = 0, ll = lines.length; i < ll; i++) {
        if (lines[i].stats === undefined) {
            li = i;
            break;
        }
    }
    if (li < 0) {
        finish('Manager list end');
        return false;
    }
    queryResult.status = 100;
    lines[li].stats = {started_at: getTimestamp()};
    var call = viCall(lines[li].info);
    call.addEventListener(CallEvents.Connected, function(e) {
        lines[li].stats.connected_at = getTimestamp();
        handleManagerConnected(e);
    });
    call.addEventListener(CallEvents.Failed, function(e) {
        lines[li].e = e;
        lines[li].stats.failed_at = getTimestamp();
        Logger.write('Calling ' + lines[li].info + ' failed');
        queryResult.status = 101;
        callManager();
    });
    call.addEventListener(CallEvents.Disconnected, function(e) {
        lines[li].e = e;
        lines[li].stats.disconnected_at = getTimestamp();
        Logger.write('Called manager ' + lines[li].info + ' disconnected');
        if (queryResult.status > 999) {
            queryResult.status = 1001;
        } else {
            queryResult.status = 191;
        }
        me = e;
        finish('Manager call disconnected');
        if (cCall !== undefined) {
            cCall.hangup();
        }
    });
    call.sendMessage(call.number);
    return call;
}

function handleManagerConnected(e) {
    Logger.write('Manager connected, calling customer: ' + data.call_info);
    queryResult.status = 190;
    mCall = e.call;
    queryResult.status = 200;
    cStats.started_at = getTimestamp();
    cCall = viCall(data.call_info);
    cCall.addEventListener(CallEvents.Connected, function (e2) {
        cStats.connected_at = getTimestamp();
        handleClientConnected(e2);
    });
    cCall.addEventListener(CallEvents.Failed, function(e2) {
        cStats.failed_at = getTimestamp();
        mCall.stopPlayback();
        mCall.startEarlyMedia();
        Logger.write('ClientCallFailed say/play');
        if (settings.mClientCallFailedAudio == 'none') {
            mCall.say(settings.mClientCallFailed, voiceType);
        } else {
            mCall.startPlayback(settings.mClientCallFailedAudio);
        }
        mCall.addEventListener(CallEvents.PlaybackFinished, function(e3) {
            queryResult.status = 201;
            finish('Customer call failed');
            mCall.hangup();
        });
    });
    cCall.addEventListener(CallEvents.Disconnected, function(e2) {
        Logger.write('Called customer ' + data.call_info + ' disconnected');
        cStats.disconnected_at = getTimestamp();
        if (queryResult.status > 999) {
            queryResult.status = 1002;
        } else {
            queryResult.status = 291;
        }
        ce = e2;
        cCall = e2.call;
        finish('Customer call disconnected');
        mCall.hangup();
    });
    mCall.startEarlyMedia();
    Logger.write('IncomingCall say/play');
    if (settings.mIncomingCallAudio == 'none') {
        mCall.say(settings.mIncomingCall, voiceType);
    } else {
        mCall.startPlayback(settings.mIncomingCallAudio);
    }
    mCall.addEventListener(CallEvents.PlaybackFinished, function() {
        mCall.playProgressTone('RU');
    });
}

function handleClientConnected(e)
{
    queryResult.status = 900;
    Logger.write('Customer connected, mergin with manager');
    VoxEngine.sendMediaBetween(mCall, e.call);
    VoxEngine.easyProcess(mCall, e.call);
    e.call.addEventListener(CallEvents.RecordStarted, function(e2) {
        queryResult.status = 1000;
        queryResult.recordUrl = e2.url;
    });
    e.call.record();
}
