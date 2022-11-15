//实例级配置
fly.config.timeout = 5000;
fly.config.headers = {
    "X-SOFT-NAME": "SAPI++ SaaS Framework",
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    'content-type':"application/json;charset=utf-8"
}
fly.config.mode    = "no-cors"
fly.config.baseURL = ""
//添加请求拦截器
fly.interceptors.request.use((request) => {
    return request;
})
//添加响应拦截器，响应拦截器会在then/catch处理之前执行
fly.interceptors.response.use((response) => {
        rel = response.data
        switch (rel.code) {
            case 200: //请求成功,并返回状态
                parent.layer.toast(rel.message, {skin: 'success'});
                if (rel.hasOwnProperty('is_parent') && rel.is_parent == true) {
                    rel.hasOwnProperty('url') ? parent.window.location.replace(rel.url) : parent.window.location.reload();
                } else {
                    rel.hasOwnProperty('url') ? window.location.replace(rel.url) : window.location.reload();
                }
                break;
            case 202: //Debug
                parent.layer.toast(rel.message,{skin:'success'});
            case 204:
                console.log(rel.message)
                break;
            case 302:
                if (rel.hasOwnProperty('is_parent') && rel.is_parent == true) {
                    rel.hasOwnProperty('url') ? parent.window.location.replace(rel.url) : parent.window.location.reload();
                } else {
                    rel.hasOwnProperty('url') ? window.location.replace(rel.url) : window.location.reload();
                }
                break;
            default:
                parent.layer.toast(rel.message,{skin:'error'});
                break;
        }
    },(err) => {
        console.log(err)
    }
)
var http = new Fly;
http.get = (url,params,callback) => {
    var param = {};"function"!=typeof params&&(param = params);
    return fly.get(url,param).then((response) => {
            "function" == typeof params?params(response.data):"function"==typeof callback&&callback(response.data);
        }).catch((error) => {
            console.log(error)
        })
}
http.post = (url,params,callback) => {
    var param = {};"function"!=typeof params&&(param = params);
    return fly.post(url,params).then((response) => {
            "function"==typeof params?params(response.data):"function"==typeof callback&&callback(response.data);
        }).catch((error) => {
            console.log(error)
        })
}
http.put = (url,params,callback) => {
    var param = {};"function"!=typeof params&&(param = params);
    return fly.put(url,params).then((response) => {
            "function"==typeof params?params(response.data):"function"==typeof callback&&callback(response.data);
        }).catch((error) => {
            console.log(error)
        })
}