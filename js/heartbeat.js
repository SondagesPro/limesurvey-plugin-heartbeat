/**
 * @file heartBeat function for limesurvey
 * @author Frederik Prijck
 * @copyright Frederik Prijck <http://www.frederikprijck.net/>
 * @license magnet:?xt=urn:btih:d3d9a9a6595521f9666a5e94cc830dab83b65699&dn=expat.txt Expat (MIT)
 */

var heartBeat = (function(global, $) {
    return {
        beat: function (options) {
            global.setInterval(function() {
                $.get(options.endpoint);
            }, options.interval || 15000);
        }
    };
})(window, $);
