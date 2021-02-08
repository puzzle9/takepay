<template>
    <van-loading class="loading" v-if="loading" type="spinner" />
    <div class="app" v-else>
        <van-nav-bar class="nav" :title="title" />
        <br />
        <!-- <van-divider content-position="left">支付</van-divider> -->
        <van-form @submit="submit" validate-trigger="onChange" colon scroll-to-error>
            <van-field
                v-model="form.amount"
                label="金额"
                placeholder="请填写待支付金额"
                :rules="[
                    { required: true, message: '请输入金额' },
                    { validator: (value) => /(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/.test(value), message: '金额输入有误' },
                ]"
                readonly
                clickable
                @touchstart.stop="showMoneyKeyboard = true"
            />
            <van-number-keyboard v-model="form.amount" :show="showMoneyKeyboard" theme="custom" extra-key="." close-button-text="完成" @blur="showMoneyKeyboard = false" />
            <van-field v-model="form.remark" autosize label="备注" type="textarea" placeholder="备注..." maxlength="32" show-word-limit />
            <div style="margin: 16px">
                <van-button type="primary" round block native-type="submit" :loading="submitLoading">提交订单</van-button>
            </div>
        </van-form>

        <van-divider content-position="left">历史订单</van-divider>
        <van-skeleton title :row="3" :loading="orderLoading">
            <van-empty v-if="!orderDatas.length && order_finished" description="空空如也" />
            <van-pull-refresh v-else v-model="order_refreshing" @refresh="orderRefresh">
                <van-list :loading="order_loading" :finished="order_finished" finished-text="没有了..." @load="orderLoad">
                    <van-cell v-for="item in orderDatas" :key="item.no" :title="item.created_at" :value="`${item.amount} ¥`" @click="orderClick(item)">
                        <template #label>
                            <van-tag :type="order_pay_status[item.status]" plain>{{ item.status_string }}</van-tag>
                            {{ item.remark }}
                        </template>
                    </van-cell>
                </van-list>
            </van-pull-refresh>
        </van-skeleton>

        <van-action-sheet v-model:show="orderActionShow" close-on-click-action :title="`${order_info.no} ${order_info.status_string}`">
            <div class="order_action_content">
                <van-cell title="金额" :value="`${order_info.amount} ¥`" />
                <van-cell v-if="order_info.remark" title="备注" :value="order_info.remark" />
                <van-cell title="创建时间" :value="order_info.created_at" />
                <van-cell title="更新时间" :value="order_info.updated_at" />
                <van-row justify="space-between" class="buttons">
                    <van-button :block="order_info.status != 'create'" type="danger" @click="orderDelete">删除订单</van-button>
                    <van-button v-if="order_info.status == 'create'" type="primary" @click="pay(order_info.no)">继续支付</van-button>
                </van-row>
            </div>
        </van-action-sheet>
    </div>
</template>

<script>
    import fly from './fly'
    export default {
        name: 'App',
        data() {
            return {
                title: '',
                loading: true,
                showMoneyKeyboard: false,
                form: {
                    amount: '',
                },
                submitLoading: false,
                orderLoading: true,
                order_page: 1,
                order_refreshing: false,
                order_loading: false,
                order_finished: false,
                orderDatas: [],
                order_pay_status: {
                    create: 'primary',
                    paid: 'success',
                    close: 'warning',
                },
                order_info: {},
                orderActionShow: false,
            }
        },
        mounted() {
            this.getBase()
        },
        methods: {
            getBase() {
                let url = window.location.origin + '/'
                fly.get('/base', {
                    url,
                }).then((res) => {
                    window.history.replaceState({}, document.title, url)
                    this.title = res.title

                    let last_amount = res.last_amount
                    if (last_amount) {
                        this.form.amount = last_amount
                    }

                    this.loading = false
                    this.orderLoading = false
                    let wechat_jssdk = res.wechat_jssdk
                    if (wechat_jssdk) {
                        wx.config(wechat_jssdk)
                        wx.hideAllNonBaseMenuItem()
                    }
                })
            },

            submit() {
                this.submitLoading = true
                fly.post('/order/create', this.form).then((res) => {
                    // this.orderRefresh()
                    this.pay(res.no)
                })
            },

            orderRefresh() {
                this.order_finished = false
                this.order_refreshing = true
                this.orderLoad()
            },

            orderLoad() {
                if (this.order_refreshing) {
                    this.orderDatas = []
                    this.order_refreshing = false
                    this.order_page = 1
                }

                this.order_loading = true
                fly.get('/order/lists', {
                    page: this.order_page,
                }).then((res) => {
                    this.order_page = res.current + 1
                    // TODO: 批量插入
                    res.data.map((info) => this.orderDatas.push(info))
                    this.order_loading = false
                    this.order_finished = !res.has_more_page
                })
            },

            orderClick(info) {
                this.order_info = info
                this.orderActionShow = true
            },

            pay(no) {
                this.orderActionShow = false
                fly.post('/order/pay', {
                    no,
                })
                    .then((res) => {
                        switch (res.type) {
                            case 'wechat':
                                let data = res.data
                                wx.chooseWXPay({
                                    timestamp: data.timeStamp,
                                    ...data,
                                    success: () => {
                                        vant.Notify({ type: 'success', message: '支付成功' })
                                        this.orderRefresh()
                                    },
                                    cancel: () => {
                                        vant.Notify({ type: 'warning', message: '已取消支付' })
                                    },
                                    fail: () => {
                                        vant.Notify({ type: 'danger', message: '支付拉起错误 请联系管理员' })
                                    },
                                })
                                break
                            default:
                                this.Notify({ type: 'warning', message: '暂不支持此协议' })
                        }
                    })
                    .finally(() => {
                        this.submitLoading = false
                    })
            },

            orderDelete() {
                let info = this.order_info,
                    no = info.no
                vant.Dialog.confirm({
                    title: '将删除此订单',
                    message: no,
                    beforeClose: (action) =>
                        new Promise(async (resolve) => {
                            if (action === 'confirm') {
                                await fly.delete(`/order/${no}`)
                                this.orderActionShow = false
                                this.orderRefresh()
                            }

                            resolve(true)
                        }),
                })
            },
        },
    }
</script>

<style lang="css">
    body {
        max-width: 500px;
        margin: auto;
    }
    .loading {
        margin-top: 30px;
        text-align: center;
    }
    .app {
        margin: 10px;
    }

    .app .nav {
        margin: -10px;
    }

    .order_action_content {
        padding: 20px;
    }

    .order_action_content .buttons {
        padding: 10px 40px;
    }
</style>
