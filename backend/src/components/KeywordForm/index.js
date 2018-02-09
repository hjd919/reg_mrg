import React, { PureComponent } from 'react';
import {
    Form,
    Input,
    Button,
    InputNumber,
    DatePicker,
    Divider,
    Table,
    message,
    Checkbox,
    TimePicker
} from 'antd';
import { routerRedux } from 'dva/router';
// import styles from './index.less';
import moment from 'moment';

const FormItem = Form.Item;
const InputGroup = Input.Group;

const { TextArea } = Input;

export default class Step2 extends PureComponent {
    constructor(props) {
        super(props)
    }
    componentWillMount() {

        // 获取手机空闲数
        this.props.dispatch({
            type: 'task/getFreeMobileNum'
        }).then(data => {
            message.info('正在添加任务（' + data + ')', 4);
        })
    }

    componentWillUnmount() {
        // 清空已经添加的记录
        this.props.dispatch({
            type: 'task/clearAddedRecord'
        })
    }

    onBlurMobileNum = (e) => {
        // 判断手机数量是否足够
        const { free_mobile_num } = this.props.task.form

        const input_mobile_num = e.target.value

        if (input_mobile_num > free_mobile_num) {
            message.error('不能超过空闲手机数，最大可填' + free_mobile_num);
            return false
        }
    }
    render() {
        const { formItemLayout, form, dispatch, task: { success_keyword_list, form: { app_name, free_mobile_num, task_id, usable_brush_num, exception_mobile_num, usable_brush_device, real_used_mobile_num, useful_comment_num } } } = this.props
        const { getFieldDecorator, validateFields } = form;

        // 提交表单
        const onValidateForm = (e) => {
            const is_continue = e.target.getAttribute('data-continue')

            validateFields((err, values) => {

                // 构造日期
                values.start_time = values.start_time_date.format('YYYY-MM-DD') + ' ' + values.start_time_time.format('HH:mm:00')
                delete values.start_time_date
                delete values.start_time_time

                values.end_time = values.end_time_date.format('YYYY-MM-DD') + ' ' + values.end_time_time.format('HH:mm:00')
                delete values.end_time_date
                delete values.end_time_time

                // 附带任务id:task_id
                values.task_id = task_id

                if (!err) {
                    dispatch({
                        type: 'task/saveTaskKeyword',
                        payload: values,
                    }).then((res) => {

                        if (!res) {
                            return false
                        }

                        message.success('添加成功', 2, () => {

                            // 判断是否继续
                            if (is_continue) {
                                form.resetFields();
                            } else {
                                dispatch(routerRedux.push('/task/list'))
                            }
                        })
                    })
                }
            });
        };

        const columns = [{
            title: '任务id',
            dataIndex: 'app_id',
            key: 'app_id',
        }, {
            title: 'app名',
            dataIndex: 'app_name',
            key: 'app_name',
        }, {
            title: '关键词',
            dataIndex: 'keyword',
            key: 'keyword',
        }];

        const data = success_keyword_list.reduce((arr, current, index) => {
            current.key = index
            return arr.concat(current)
        }, []);

        return (
            <div>
                <Table columns={columns} dataSource={data} title={() => '已添加关键词（app名:' + app_name + ')'} />

                <Divider style={{ margin: '20px 0 10px' }} />

                <Form layout="horizontal" hideRequiredMark>
                    <FormItem
                        {...formItemLayout}
                        label="关键词 排名 热度 量级 {手机组id}"
                        help={"可刷账号:" + usable_brush_num + '-每小时35量分配手机数量' + usable_brush_device + "-已用手机:" + real_used_mobile_num + "-可用手机:" + free_mobile_num + '(异常手机:' + exception_mobile_num + ')'}
                    >
                        {getFieldDecorator('app_info', {
                            initialValue: '',
                            rules: [],
                        })(
                            <TextArea style={{ minHeight: 40 }} placeholder="必填" rows={8} />
                            )}
                    </FormItem>
                    {/* <FormItem
                        {...formItemLayout}
                        label="关键词"
                    >
                        {getFieldDecorator('keyword', {
                            initialValue: '',
                            rules: [{ required: true, message: '请填写信息' }],
                        })(
                            <Input placeholder="必填" onBlur={this.onBlurAppid} />
                            )}
                    </FormItem>
                    <FormItem
                        {...formItemLayout}
                        label="量级"
                    >
                        {getFieldDecorator('success_num', {
                            initialValue: '',
                            rules: [{ required: true, message: '请填写信息' }],
                        })(
                            <InputNumber placeholder="必填" min={0} />
                            )}
                    </FormItem> */}
                    <FormItem
                        {...formItemLayout}
                        label="开始时间"
                    >
                        <InputGroup compact>
                            {getFieldDecorator('start_time_date', {
                                initialValue: moment(),
                                rules: [{ required: true, message: '请填写信息' }],
                            })(
                                <DatePicker />
                                )}
                            {getFieldDecorator('start_time_time', {
                                initialValue: moment(),
                                rules: [{ required: true, message: '请填写信息' }],
                            })(
                                <TimePicker format='HH:mm' />
                                )}
                        </InputGroup>
                    </FormItem>
                    <FormItem
                        {...formItemLayout}
                        label="结束时间"
                        help={"结束时间需大于开始时间"}
                    >
                        <InputGroup compact>
                            {getFieldDecorator('end_time_date', {
                                initialValue: moment().add('1', 'days'),
                                rules: [{ required: true, message: '请填写信息' }],
                            })(
                                <DatePicker />
                                )}
                            {getFieldDecorator('end_time_time', {
                                initialValue: moment(),
                                rules: [{ required: true, message: '请填写信息' }],
                            })(
                                <TimePicker format='HH:00' />
                                )}
                        </InputGroup>
                    </FormItem>
                    <FormItem
                        {...formItemLayout}
                        label="开启评论"
                        help={"剩余评论数:" + useful_comment_num}
                    >
                        {getFieldDecorator('is_comment', {
                            initialValue: false,
                            rules: [],
                        })(
                            <Checkbox>是</Checkbox>
                            )}
                    </FormItem> 
                    {/* <FormItem
                        {...formItemLayout}
                        label="手机组id"
                        help={"生产不用理"}
                    >
                        {getFieldDecorator('mobile_group_id', {
                            initialValue: '0',
                            rules: [],
                        })(
                            <InputNumber placeholder="" min={0} />
                            )}
                    </FormItem> */}
                    {/*<FormItem
                        {...formItemLayout}
                        label="打量前排名"
                    >
                        {getFieldDecorator('before_rank', {
                            initialValue: '',
                            rules: [{ required: true, message: '请填写信息' }],
                        })(
                            <InputNumber placeholder="必填" min={0} />
                            )}
                    </FormItem>
                    <FormItem
                        {...formItemLayout}
                        label="热度"
                    >
                        {getFieldDecorator('hot', {
                            initialValue: '',
                            rules: [{ required: true, message: '请填写信息' }],
                        })(
                            <InputNumber placeholder="必填" min={0} />
                            )}
                        </FormItem>
                    */}
                    <FormItem
                        wrapperCol={{
                            xs: { span: 24, offset: 0 },
                            sm: { span: formItemLayout.wrapperCol.span, offset: formItemLayout.labelCol.span },
                        }}
                        label=""
                    >
                        <Button type="primary" onClick={onValidateForm} data-continue={true}>继续添加</Button>
                        <Button type="primary" onClick={onValidateForm} data-continue={false} style={{ marginLeft: 15 }}>完成</Button>
                    </FormItem>
                </Form>

            </div>
        )
    }
}