
module.exports = (routes,type) => {
    return (route, param) => {
        if(type){
            var routeUrl = routes[type+route];
        }else{
            var routeUrl = routes[route];
        }

        let append = [];

        for (let x in param) {
            let search = '{' + x + '}';

            if (routeUrl.indexOf(search) >= 0) {
                routeUrl = routeUrl.replace('{' + x + '}', param[x]);
            } else {
                if (typeof param[x] != 'object') {
                    append.push(x + '=' + encodeURIComponent(param[x]));
                } else if (param[x] instanceof Array) {
                    param[x].forEach(item => {
                        append.push(x + '[]=' + encodeURIComponent(item));
                    })
                } else {
                    for (let key in param[x]) {
                        append.push(x + '[' + key + ']=' + encodeURIComponent(param[x][key]));
                    }
                }
            }
        }

        let url = '/' + routeUrl;

        if (append.length == 0) {
            return url;
        }

        if (url.indexOf('?') >= 0) {
            url += '&';
        } else {
            url += '?';
        }

        url += append.join('&');

        return url;
    }
};
