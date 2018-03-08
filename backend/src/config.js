// 根据环境切换
let domain
if (process.env.NODE_ENV == 'development') {
	domain = 'http://192.168.1.100:89'
} else {
	domain = 'http://192.168.1.100:89'
}

export default {
	domain
}