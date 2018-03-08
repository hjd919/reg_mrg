import {
  Button,
  Card,
  Col,
  DatePicker,
  Dropdown,
  Form,
  Icon,
  Input,
  InputNumber,
  Menu,
  Row,
  Select,
  Tag,
  Upload,
  message,
} from 'antd';
import { Link } from 'dva/router';
import { connect } from 'dva';
import React, { PureComponent } from 'react';
import moment from 'moment';

const { RangePicker } = DatePicker; // 日期

import { domain } from '../../config'
import PageHeaderLayout from '../../layouts/PageHeaderLayout';
import StandardTable from '../../components/StandardTable';
import styles from './AppList.less';

const uploadUrl = domain + '/backend/app/import_rank'

import querystring from 'querystring';

const FormItem = Form.Item;
const { Option } = Select;
const getValue = obj => Object.keys(obj).map(key => obj[key]).join(',');

@connect(state => ({
  app: state.app,
}))
@Form.create()
export default class AppList extends PureComponent {
  state = {
    addInputValue: '',
    modalVisible: false,
    expandForm: false,
    selectedRows: [],
    formValues: {},
  };

  componentDidMount() {
    const { dispatch, location } = this.props;

    // 清除查询参数
    dispatch({
      type: 'app/clearQueryParams',
      payload: {},
    });

    // 从url获取查询参数
    let query_params = {};
    if (location.search) {
      query_params = querystring.parse(location.search.replace('?', ''))
    }

    dispatch({
      type: 'app/fetch',
      payload: query_params,
    });
  }

  handleStandardTableChange = (pagination, filtersArg, sorter) => {
    const { dispatch } = this.props;
    const { formValues } = this.state;

    const filters = Object.keys(filtersArg).reduce((obj, key) => {
      const newObj = { ...obj };
      newObj[key] = getValue(filtersArg[key]);
      return newObj;
    }, {});

    const params = {
      currentPage: pagination.current,
      pageSize: pagination.pageSize,
      ...formValues,
      ...filters,
    };
    if (sorter.field) {
      params.sorter = `${sorter.field}_${sorter.order}`;
    }

    dispatch({
      type: 'app/fetch',
      payload: params,
    });
  }

  handleFormReset = () => {
    const { form, dispatch } = this.props;

    form.resetFields();

    dispatch({
      type: 'app/clearQueryParams',
      payload: {},
    });

    dispatch({
      type: 'app/fetch',
      payload: {},
    });
  }

  toggleForm = () => {
    this.setState({
      expandForm: !this.state.expandForm,
    });
  }

  handleMenuClick = (e) => {
    const { dispatch } = this.props;
    const { selectedRows } = this.state;

    if (!selectedRows) return;

    switch (e.key) {
      case 'remove':
        dispatch({
          type: 'task/remove',
          payload: {
            no: selectedRows.map(row => row.no).join(','),
          },
          callback: () => {
            this.setState({
              selectedRows: [],
            });
          },
        });
        break;
      default:
        break;
    }
  }

  handleSelectRows = (rows) => {
    this.setState({
      selectedRows: rows,
    });
  }

  handleSearch = (e) => {
    e.preventDefault();

    const { dispatch, form } = this.props;

    form.validateFields((err, fieldsValue) => {
      if (err) return;
      console.log('fieldsValue', fieldsValue)
      let values = {}
      if (fieldsValue.range_date){
        values['start_date'] = fieldsValue.range_date[0].format('YYYY-MM-DD')
        values['end_date'] = fieldsValue.range_date[1].format('YYYY-MM-DD')
      }
      delete fieldsValue.range_date

      fieldsValue = Object.assign({}, fieldsValue, values)
      console.log('fieldsValue', fieldsValue)
      this.setState({
        formValues: fieldsValue,
      });

      dispatch({
        type: 'app/fetch',
        payload: fieldsValue,
      });
    });
  }

  handleModalVisible = (flag) => {
    this.setState({
      modalVisible: !!flag,
    });
  }

  handleAddInput = (e) => {
    this.setState({
      addInputValue: e.target.value,
    });
  }

  handleAdd = () => {
    this.props.form.validateFieldsAndScroll((err, values) => {
      return false;
      if (!err) {
        this.props.dispatch({
          type: 'task/add',
          payload: values,
        });
      }
    });
    return false

    this.props.dispatch({
      type: 'task/add',
      payload: {
        description: this.state.addInputValue,
      },
    });

    message.success('添加成功');
    this.setState({
      modalVisible: false,
    });
  }

  exportApp = (e) => {
    const export_url = domain + '/backend/app/export?token=' + localStorage.token + '&' + querystring.stringify(this.state.formValues)
    location.href = export_url
  }

  // 停止任务
  stopTask = (e) => {
    if (!confirm('是否要停止该任务?')) {
      return true
    }

    const app_id = e.target.getAttribute('data-app-id')
    this.props.dispatch({
      type: 'app/stop',
      payload: { app_id }
    }).then(() => {
      message.success('执行成功!');
    })
  }

  renderSimpleForm() {
    const { form: { getFieldDecorator }, app: { query_params } } = this.props;
    return (
      <Form onSubmit={this.handleSearch} layout="inline">
        <Row gutter={{ md: 8, lg: 24, xl: 48 }}>
          <Col md={5} sm={24}>
            <FormItem label="任务id">
              {getFieldDecorator('id')(
                <Input placeholder="" />
              )}
            </FormItem>
          </Col>
          <Col md={5} sm={24}>
            <FormItem label="关键词">
              {getFieldDecorator('keyword')(
                <Input placeholder="" />
              )}
            </FormItem>
          </Col>
          <Col md={8} sm={24}>
            <FormItem label="创建时间">
              {getFieldDecorator('range_date')(
                <RangePicker />
              )}
            </FormItem>
          </Col>
          <Col md={6} sm={24}>
            <span className={styles.submitButtons}>
              <Button type="primary" htmlType="submit">查询</Button>
              <Button style={{ marginLeft: 8 }} onClick={this.handleFormReset}>重置</Button>
              <a style={{ marginLeft: 8 }} onClick={this.toggleForm}>
                展开 <Icon type="down" />
              </a>
            </span>
          </Col>
        </Row>
      </Form>
    );
  }

  renderAdvancedForm() {
    const { getFieldDecorator } = this.props.form;
    return (
      <Form onSubmit={this.handleSearch} layout="inline">
        <Row gutter={{ md: 8, lg: 24, xl: 48 }}>
          <Col md={8} sm={24}>
            <FormItem label="规则编号">
              {getFieldDecorator('no')(
                <Input placeholder="请输入" />
              )}
            </FormItem>
          </Col>
          <Col md={8} sm={24}>
            <FormItem label="使用状态">
              {getFieldDecorator('status')(
                <Select placeholder="请选择" style={{ width: '100%' }}>
                  <Option value="0">关闭</Option>
                  <Option value="1">运行中</Option>
                </Select>
              )}
            </FormItem>
          </Col>
          <Col md={8} sm={24}>
            <FormItem label="调用次数">
              {getFieldDecorator('number')(
                <InputNumber style={{ width: '100%' }} />
              )}
            </FormItem>
          </Col>
        </Row>
        <Row gutter={{ md: 8, lg: 24, xl: 48 }}>
          <Col md={8} sm={24}>
            <FormItem label="更新日期">
              {getFieldDecorator('date')(
                <DatePicker style={{ width: '100%' }} placeholder="请输入更新日期" />
              )}
            </FormItem>
          </Col>
          <Col md={8} sm={24}>
            <FormItem label="使用状态">
              {getFieldDecorator('status3')(
                <Select placeholder="请选择" style={{ width: '100%' }}>
                  <Option value="0">关闭</Option>
                  <Option value="1">运行中</Option>
                </Select>
              )}
            </FormItem>
          </Col>
          <Col md={8} sm={24}>
            <FormItem label="使用状态">
              {getFieldDecorator('status4')(
                <Select placeholder="请选择" style={{ width: '100%' }}>
                  <Option value="0">关闭</Option>
                  <Option value="1">运行中</Option>
                </Select>
              )}
            </FormItem>
          </Col>
        </Row>
        <div style={{ overflow: 'hidden' }}>
          <span style={{ float: 'right', marginBottom: 24 }}>
            <Button type="primary" htmlType="submit">查询</Button>
            <Button style={{ marginLeft: 8 }} onClick={this.handleFormReset}>重置</Button>
            <a style={{ marginLeft: 8 }} onClick={this.toggleForm}>
              收起 <Icon type="up" />
            </a>
          </span>
        </div>
      </Form>
    );
  }

  renderForm() {
    return this.state.expandForm ? this.renderAdvancedForm() : this.renderSimpleForm();
  }

  // 刷新列表
  reloadList = () => {
    this.props.dispatch({
      type: 'app/fetch',
      payload: {},
    });
  }

  openComment = () => {
    this.props.dispatch({
      type: 'app/openComment',
      payload: {},
    });
  }

  // 上传文件
  handleUploadFile = ({ file, fileList }) => {
    if (file.status !== 'uploading') {
      const response = file.response
      if (response.error_code != 0) {
        message.success('导入机刷结果成功,成功数:' + response.success_num, 5);
      } else {
        message.error('导入机刷结果失败');
      }
      return response
    }
  }

  render() {
    const { app: { loading: loading, data }, form: { getFieldDecorator, getFieldValue } } = this.props;
    const { selectedRows, modalVisible, addInputValue } = this.state;

    const menu = (
      <Menu onClick={this.handleMenuClick} selectedKeys={[]}>
        <Menu.Item key="remove">删除</Menu.Item>
        <Menu.Item key="approval">批量审批</Menu.Item>
      </Menu>
    );

    // 配置栏目
    const columns = [
      {
        fixed: 'left',
        title: '关键词',
        width: 90,
        dataIndex: 'keyword',
        render: (val, record) => {
          return <Link to={"/app/hourl_stat?app_id=" + record.id}>{val}</Link>
        },
      },
      {
        title: 'app名',
        width: 90,
        dataIndex: 'app_name',
      },
      {
        width: 100,
        title: 'appid',
        dataIndex: 'appid',
      },
      {
        width: 60,
        title: '任务id',
        dataIndex: 'id',
      },
      {
        title: '下单人',
        width: 80,
        dataIndex: 'user_name',
      },
      {
        title: '剩余量',
        width: 50,
        dataIndex: 'brush_num',
      },
      {
        title: '已完成量',
        width: 50,
        dataIndex: 'hour_success_num',
      },
      {
        title: '下单量',
        width: 50,
        dataIndex: 'success_num',
      },
      {
        title: '实际结束',
        width: 100,
        dataIndex: 'real_end_time',
        render: val => !val ? '进行中' : moment(val).format('YYYY-MM-DD HH:mm'),
      },
      {
        title: '实际总打量',
        dataIndex: 'brushed_num',
        render: (val, record) => {
          return !record.is_brushing ? val : '进行中'
        }
      },
      {
        title: '成功打量',
        dataIndex: 'success_brushed_num',
        render: val => val ? val : '进行中',
      },
      {
        title: '失败打量',
        dataIndex: 'fail_brushed_num',
        render: val => val ? val : '进行中',
      },
      {
        title: '手机数量',
        dataIndex: 'mobile_num',
      },
      {
        title: '打量开始',
        dataIndex: 'start_time',
        render: val => moment(val).format('YYYY-MM-DD HH:mm'),
      },
      {
        title: '打量结束',
        dataIndex: 'end_time',
        render: val => moment(val).format('YYYY-MM-DD HH:mm'),
      },
      {
        title: '手机组id',
        dataIndex: 'mobile_group_id',
      },
      {
        title: '创建时间',
        dataIndex: 'create_time',
        render: val => moment(val).format('YYYY-MM-DD HH:mm'),
      },
      {
        fixed: 'right',
        width: 60,
        title: '操作',
        render: (text, record) => (
          <div style={{ display:'flex'}}>
          <p>
            {moment(record.end_time).isBefore(moment())
              ? ''
              : <Button type="danger" onClick={this.stopTask} data-app-id={record.id}>停止</Button>
            }
          </p>
          {/*
          <p>
              <Button type="default" onClick={this.openComment} data-app-id={record.id}>开启评论</Button>
            </p>
            */}
            </div>
        ),
      },
    ];

    // 设置form_style
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

    return (
      <PageHeaderLayout title="任务列表">
        <Card bordered={false}>
          <div className={styles.tableList}>
            <div className={styles.tableListForm}>
              {this.renderForm()}
            </div>
            <div className={styles.tableListOperator}>
              <Button
                type="primary"
                onClick={this.reloadList}
                disabled={selectedRows.length}
                loading={loading}
              >
                <Icon type="reload" /> 刷新
              </Button>
              <Button icon="export" type="primary" onClick={this.exportApp}>导出机刷统计</Button>
              {/*<Upload
                name="upload_file"
                headers={{ Authorization: 'Bearer ' + localStorage.token }}
                onChange={this.handleUploadFile}
                action={uploadUrl}>
                <Button type="primary">
                  <Icon type="upload" /> 导入机刷结果
                </Button>
              </Upload>
   <Tag color="red">先点击“导出机刷统计”，填写好机刷结果(现排名，在榜时长，在榜开始，在榜结束)，再点击“导入机刷结果”</Tag>*/}
              {
                selectedRows.length > 0 && (
                  <span>
                    <Button>批量操作</Button>
                    <Dropdown overlay={menu}>
                      <Button>
                        更多操作 <Icon type="down" />
                      </Button>
                    </Dropdown>
                  </span>
                )
              }
            </div>
            <StandardTable
              selectedRows={selectedRows}
              loading={loading}
              id="app_list"
              data={data}
              columns={columns}
              onSelectRow={this.handleSelectRows}
              onChange={this.handleStandardTableChange}
            />
          </div>
        </Card>
      </PageHeaderLayout>
    );
  }
}
