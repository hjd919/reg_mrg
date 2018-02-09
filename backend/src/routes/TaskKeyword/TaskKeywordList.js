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
  Modal,
  Row,
  Select,
  message,
} from 'antd';
import { connect } from 'dva';
import { Link } from 'dva/router';
import React, { PureComponent } from 'react';
import moment from 'moment';

import PageHeaderLayout from '../../layouts/PageHeaderLayout';
import StandardTable from '../../components/StandardTable';
import styles from './TaskKeyword.less';

import querystring from 'querystring';

const FormItem = Form.Item;
const { Option } = Select;
const getValue = obj => Object.keys(obj).map(key => obj[key]).join(',');

@connect(state => ({
  task_keyword: state.task_keyword,
}))
@Form.create()
export default class TaskList extends PureComponent {
  state = {
    addInputValue: '',
    modalVisible: false,
    expandForm: false,
    selectedRows: [],
    formValues: {},
  };

  componentDidMount() {
    const { dispatch, location } = this.props;

    // 从url获取查询参数
    let query_params = {};
    if (location.search) {
      query_params = querystring.parse(location.search.replace('?',''))
    }
    dispatch({
      type: 'task_keyword/setQueryParams',
      payload: query_params,
    });

    dispatch({
      type: 'task_keyword/fetch',
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
      type: 'task_keyword/fetch',
      payload: params,
    });
  }

  handleFormReset = () => {
    const { form, dispatch } = this.props;
    form.resetFields();
    dispatch({
      type: 'task/fetch',
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

      const values = {
        ...fieldsValue,
        updatedAt: fieldsValue.updatedAt && fieldsValue.updatedAt.valueOf(),
      };

      this.setState({
        formValues: values,
      });

      dispatch({
        type: 'task/fetch',
        payload: values,
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
    console.log('handleAdd')
    this.props.form.validateFieldsAndScroll((err, values) => {
      console.log(values)
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

  // 停止任务
  stopTask = (e) => {
    const app_id = e.target.getAttribute('data-app-id')
    this.props.dispatch({
      type:'task_keyword/stop',
      payload: {app_id}
    })
  }

  renderSimpleForm() {
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

  render() {
    const { task_keyword: { loading: loading, data }, form: { getFieldDecorator, getFieldValue } } = this.props;
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
        width: 100,
        title: 'appid',
        dataIndex: 'appid',
      },
      {
        width: 100,
        title: 'app名',
        dataIndex: 'app_name',
      },
      {
        title: '关键词',
        width: 90,
        dataIndex: 'keyword',
      },
      {
        title: '下单人',
        dataIndex: 'user_name',
      },
      {
        title: '量级',
        dataIndex: 'success_num',
      },
      {
        title: '实际结束',
        width: 100,
        dataIndex: 'real_end_time',
        render: val => moment(val).format('YYYY-MM-DD HH:mm') == '2000-01-01 00:00' ? '进行中' : moment(val).format('YYYY-MM-DD HH:mm'),
      },
      {
        title: '剩余打量',
        dataIndex: 'remain_brush_num',
        render: (val, record) => record.is_finish ? val : '进行中',
      },
      {
        title: '实际总打量',
        dataIndex: 'brushed_num',
        render: (val,record) => {
          return record.is_finish ? val : '进行中'
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
        title: '上量开始',
        width: 100,
        dataIndex: 'start_time',
      },
      {
        title: '上量结束',
        width: 100,
        dataIndex: 'end_time',
      },
      // {
      //   title: '上量前排名',
      //   dataindex: 'before_rank',
      // },
      // {
      //   title: '热度',
      //   width:100,
      //   dataIndex: 'hot',
      // },
      // {
      //   title: '上量后排名',
      //   dataIndex: 'after_rank',
      // },
      // {
      //   title: '在榜时间',
      //   dataIndex: 'on_rank_time',
      //   render: val => val + '时',
      // },
      {
        fixed: 'right',
        width: 100,
        title: '操作',
        render: (text, record) => (
          <p>
            {/* <Link to="/task_keyword/list">停止</Link> */}
            <Button type="danger" onClick={this.stopTask} data-app-id={record.app_id}>停止</Button>
          </p>
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
      <PageHeaderLayout title="关键词列表">
        <Card bordered={false}>
          <div className={styles.tableList}>
            {/*<div className={styles.tableListForm}>
              {this.renderForm()}
    </div>
            <div className={styles.tableListOperator}>
              <Link to="/task/step_add_task"><Button icon="plus" type="primary">新建</Button></Link>
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
            </div>*/}
            <StandardTable
              selectedRows={selectedRows}
              loading={loading}
              id="task_keyword"
              data={data}
              columns={columns}
              onSelectRow={this.handleSelectRows}
              onChange={this.handleStandardTableChange}
            />
          </div>
        </Card>
        <Modal
          title="新建"
          visible={modalVisible}
          onOk={this.handleAdd}
          onCancel={() => this.handleModalVisible()}
        >
          <Form
            onSubmit={this.handleAdd}
            hideRequiredMark
            style={{ marginTop: 8 }}
          >
            <FormItem
              {...formItemLayout}
              label="app名称"
            >
              {getFieldDecorator('app_name', {
                rules: [{
                  required: true, message: '必填',
                }],
              })(
                <Input placeholder="请输入app名称" />
                )}
            </FormItem>
            <FormItem
              {...formItemLayout}
              label="appid"
            >
              {getFieldDecorator('appid', {
                rules: [{
                  required: true, message: '必填',
                }],
              })(
                <Input placeholder="请输入appid" />
                )}
            </FormItem>
          </Form>
        </Modal>
      </PageHeaderLayout>
    );
  }
}
