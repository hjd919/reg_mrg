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
import styles from './StateImport.less';


import querystring from 'querystring';

const FormItem = Form.Item;
const { Option } = Select;
const getValue = obj => Object.keys(obj).map(key => obj[key]).join(',');

@connect(state => ({
  email: state.email,
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
      type: 'email/clearQueryParams',
      payload: {},
    });

    // 从url获取查询参数
    let query_params = {};
    if (location.search) {
      query_params = querystring.parse(location.search.replace('?', ''))
    }

    dispatch({
      type: 'email/stateImport',
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
      type: 'email/stateImport',
      payload: params,
    });
  }

  handleFormReset = () => {
    const { form, dispatch } = this.props;

    form.resetFields();

    dispatch({
      type: 'email/clearQueryParams',
      payload: {},
    });

    dispatch({
      type: 'email/stateImport',
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
        id: fieldsValue.id ? fieldsValue.id.replace(' ', '') : 0,
        appid: fieldsValue.appid ? fieldsValue.appid.replace(' ', '') : 0,
        start_date: fieldsValue.range_date ? fieldsValue.range_date[0].format('YYYY-MM-DD') : 0,
        end_date: fieldsValue.range_date ? fieldsValue.range_date[1].format('YYYY-MM-DD') : 0,
      };

      this.setState({
        formValues: values,
      });

      dispatch({
        type: 'email/stateImport',
        payload: values,
      });
    });
  }

  handleModalVisible = (flag) => {
    this.setState({
      modalVisible: !!flag,
    });
  }

  exportApp = (e) => {
    const export_url = domain + '/backend/app/export?token=' + localStorage.token + '&' + querystring.stringify(this.state.formValues)
    location.href = export_url
  }

  renderSimpleForm() {
    const { form: { getFieldDecorator }, app: { query_params } } = this.props;
    return (
      <Form onSubmit={this.handleSearch} layout="inline">
        <Row gutter={{ md: 8, lg: 24, xl: 48 }}>
          <Col md={6} sm={24}>
            <FormItem label="任务id">
              {getFieldDecorator('id')(
                <Input placeholder="" />
              )}
            </FormItem>
          </Col>
          <Col md={6} sm={24}>
            <FormItem label="appid">
              {getFieldDecorator('appid')(
                <Input placeholder="" />
              )}
            </FormItem>
          </Col>
          <Col md={6} sm={24}>
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
      type: 'email/stateImport',
      payload: {},
    });
  }

  render() {
    const { email: { loading: loading, state_import }, form: { getFieldDecorator, getFieldValue } } = this.props;
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
        title: '日期',
        width:150,
        key:'import_date',
        dataIndex: 'import_date',
      },
      {
        title: '导入账号数',
        key:'total',
        dataIndex: 'total',
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
      <PageHeaderLayout title="统计导入账号">
        <Card bordered={false}>
          <div className={styles.tableList}>
            <div className={styles.tableListForm}>
              {/*this.renderForm()*/}
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
              id="state_import"
              data={state_import}
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
