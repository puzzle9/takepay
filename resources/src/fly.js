fly.config.headers.Accept = 'application/json, text/plain, */*'
fly.config.timeout = 10000
fly.config.baseURL = '/'

fly.interceptors.request.use((request) => {
    request.headers.token = window.token
    return request
})

fly.interceptors.response.use(
    (res) => {
        return res.data
    },
    (err) => {
        console.log(err)
        let response = err.response,
            data = response ? response.data : null
        switch (err.status) {
            case 500:
                vant.Dialog.alert({
                    title: '系统错误 请稍后再试',
                    message: data.message,
                    theme: 'round-button',
                })
                break

            case 422:
                vant.Notify({ type: 'warning', message: data.message })
                break

            case 415:
                vant.Dialog.alert({
                    message: '暂不支持此浏览器',
                    theme: 'round-button',
                }).then(() => {
                    // TODO: 优化关闭
                    window.close()
                })
                break

            case 401:
                window.location.href = data.sign_in_url
                break

            case 0:

            default:
        }
        return err
    },
)

export default fly
