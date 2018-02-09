// 根据环境切换
let domain
if (process.env.NODE_ENV == 'development') {
	domain = 'http://jishua_api.com'
} else {
	domain = 'http://jsapi.yz210.com'
}

export default {
	domain
}