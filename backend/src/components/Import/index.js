import { Button, Card, Col, Input, Row, Form, Upload, Icon, message } from 'antd';
import React, { Component } from 'react';
import PageHeaderLayout from '../../layouts/PageHeaderLayout';
import { domain } from '../../config';

const uploadUrl = domain + '/backend/appleid/import'

export default class BasicProfile extends Component {
    constructor(props) {
        super(props)
        this.state = {
            'message': '',
        }
    }

    // 上传文件
    handleUploadEmail = ({ file, fileList }) => {
        if (file.status !== 'uploading') {
            const response = file.response
            if (response.error_code != 0) {
                message.success('导入邮箱成功');
                this.setState({
                    message: '结果：' + response.content
                })
            } else {
                message.error('导入邮箱失败');
            }
            return response
        }
    }

    render() {
        return (
            <PageHeaderLayout title={"导入邮箱" + this.state.message}>
                <Card bordered={false}>
                    <Row gutter={{ md: 8, lg: 24, xl: 48 }}>
                        <Col md={8} sm={24}>
                            <Upload
                                name="upload_email"
                                headers={{ Authorization: 'Bearer ' + localStorage.token }}
                                onChange={this.handleUploadEmail}
                                action={uploadUrl}>
                                <Button>
                                    <Icon type="upload" /> 点击上传
                  </Button>
                            </Upload>
                        </Col>
                    </Row>
                </Card>
            </PageHeaderLayout>
        );
    }
}