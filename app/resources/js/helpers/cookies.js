export default {
    getFromString(name,str) {
        var value = "; " + str;
        var parts = value.split("; " + name + "=");
        if (parts.length == 2) return parts.pop().split(";").shift();
        else {return '';}
    },
    get(name) {
        var value = "; " + (process.browser ? document.cookie : '');
        var parts = value.split("; " + name + "=");
        if (parts.length == 2) return parts.pop().split(";").shift();
        else {return '';}
    },
    set(name,value,minutes) {
        if (typeof document!=='undefined') {
            var expires = "";
            if (minutes) {
                var date = new Date();
                date.setTime(date.getTime() + (minutes*60*1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + value + expires + "; path=/";
        }
    }
}