import { Card } from 'antd';
import { connect } from 'dva';
import React, { Component } from 'react';

import Import from '../../components/Import';

@connect(state => ({
  appleid: state.appleid
}))
export default class BasicProfile extends Component {
  inter = null

  componentDidMount() {
    const { dispatch } = this.props

    // 开启循环获取今天
    this.inter = setInterval(() => {
      dispatch({
        type:'appleid/getTodayAppleidNum'
      })
    }, 3000)
  }

  componentWillUnmount() {
    clearInterval(this.inter)
  }
  

  render() {
    const { appleid: { today_appleid_num } } = this.props
    return (
      <div>
        <Import />
        <Card bordered={false}>
          <span>今日新增邮箱数：{today_appleid_num}</span>
        </Card>
      </div>
    );
  }
}
