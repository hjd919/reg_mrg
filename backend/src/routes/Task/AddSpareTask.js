import React, { PureComponent } from 'react';
import { connect } from 'dva';
import {
  Form, Input, DatePicker, Select, Button, Card, InputNumber, Radio, Icon, Tooltip,
} from 'antd';
import PageHeaderLayout from '../../layouts/PageHeaderLayout';
import styles from './style.less';
import { requestAuthApi } from '../../utils/request';

const FormItem = Form.Item;
const { Option } = Select;
const { RangePicker } = DatePicker;
const { TextArea } = Input;

@connect(state => ({
  task: state.task,
  submitting: state.form.regularFormSubmitting,
}))
@Form.create()
export default class BasicForms extends PureComponent {
  // 提交表单
  handleSubmit = (e) => {
    e.preventDefault();
    this.props.form.validateFieldsAndScroll((err, values) => {
      if (!err) {
        this.props.dispatch({
          type: 'task/addSpareTask',
          payload: values,
        });
      }
    });
  }

  onBlurAppid = (e) => {
    const appid = e.target.value
    const { dispatch } = this.props

    // 请求服务器获取appid信息
    requestAuthApi('/backend/app/query_one', { query: { appid } })
      .then((data) => {
        const ios_app = data.ios_app
        if (ios_app) {
          this.props.form.setFieldsValue(ios_app)
        } else {
          console.log('no data')
        }

        // 可刷量
        dispatch({
          type: 'task/saveUsableBrushNum',
          payload: data.usable_brush_num,
        })
      })
  }

  render() {
    const { form, dispatch, task } = this.props
    const { getFieldDecorator, validateFields } = form;
    const {
      form: {
        app_name,
      free_mobile_num,
      task_id,
      usable_brush_num,
      exception_mobile_num,
      usable_brush_device,
      real_used_mobile_num,
      useful_comment_num
      }
    } = task;

    const formItemLayout = {
      labelCol: {
        xs: { span: 24 },
        sm: { span: 7 },
      },
      wrapperCol: {
        xs: { span: 24 },
        sm: { span: 12 },
        md: { span: 10 },
      },
    };

    const submitFormLayout = {
      wrapperCol: {
        xs: { span: 24, offset: 0 },
        sm: { span: 10, offset: 7 },
      },
    };

    return (
      <PageHeaderLayout title="新建空闲任务">
        <Card bordered={false}>
          <Form
            onSubmit={this.handleSubmit}
            hideRequiredMark
            style={{ marginTop: 8 }}
          >
            <FormItem
              {...formItemLayout}
              label="appid"
            >
              {getFieldDecorator('appid', {
                initialValue: '',
                rules: [{ required: true, message: '请填写信息' }],
              })(
                <Input placeholder="必填" onBlur={this.onBlurAppid} />
                )}
            </FormItem>
            <Form.Item
              {...formItemLayout}
              label="app名称"
            >
              {getFieldDecorator('app_name', {
                initialValue: '',
                rules: [{ required: true, message: '请填写信息' }],
              })(
                <Input placeholder="必填" />
                )}
            </Form.Item>
            <Form.Item
              {...formItemLayout}
              label="bundle_id"
            >
              {getFieldDecorator('bundle_id', {
                initialValue: '',
                rules: [{ required: true, message: '请填写信息' }],
              })(
                <Input placeholder="必填" />
                )}
            </Form.Item>
            <FormItem
              {...formItemLayout}
              label="类别"
              help={'每小时35量分配手机数量'}
            >
              {getFieldDecorator('order_type', {
                initialValue: '',
              })(
                <Radio.Group>
                  <Radio value="1">自家</Radio>
                  <Radio value="2">联运</Radio>
                  <Radio value="3">商业</Radio>
                </Radio.Group>
              )}
            </FormItem>
            <FormItem
              {...formItemLayout}
              label="关键词 排名 热度 量级 时长"
              help={"可用账号:" + usable_brush_num}
            >
              {getFieldDecorator('app_info', {
                initialValue: '',
                rules: [],
              })(
                <TextArea style={{ minHeight: 40 }} placeholder="必填" rows={8} />
                )}
            </FormItem>
            <FormItem {...submitFormLayout} style={{ marginTop: 32 }}>
              <Button type="primary" htmlType="submit">
                提交
              </Button>
            </FormItem>
          </Form>
        </Card>
      </PageHeaderLayout>
    );
  }
}
