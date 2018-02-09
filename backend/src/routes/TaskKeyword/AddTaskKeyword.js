import React, { PureComponent } from 'react';
import KeywordForm from '../../components/KeywordForm';
import { connect } from 'dva';
import { Form } from 'antd';
import querystring from 'querystring';

@connect(state => ({
    task: state.task,
}))
@Form.create()
export default class AddTaskKeyword extends PureComponent {
    constructor(props) {
        super(props)
        // const task_id = new URLSearchParams(props.location.search)
        if (!props.location.search){
            alert('请从下单列表-操作-添加相关下单的关键词');
            return false
        }
        const query_params = querystring.parse(props.location.search.substr(1))
        const pa = {
            type: 'task/saveSuccess',
            payload: query_params.task_id
        }
        props.dispatch({
            type: 'task/saveSuccess',
            payload: query_params.task_id
        })
    }
    render() {
        const formItemLayout = {
            labelCol: {
                span: 5,
            },
            wrapperCol: {
                span: 19,
            },
        };
        return (
            <KeywordForm
                {...this.props}
                formItemLayout={formItemLayout}
            />
        );
    };
}