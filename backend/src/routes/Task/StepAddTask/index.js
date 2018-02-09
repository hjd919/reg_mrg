import React, { PureComponent } from 'react';
import { connect } from 'dva';
import { Card, Steps, Form } from 'antd';
import PageHeaderLayout from '../../../layouts/PageHeaderLayout';
import Step1 from './Step1';
import Step2 from './Step2';
import styles from './style.less';

const { Step } = Steps;

@Form.create()
class StepAddTask extends PureComponent {
  getCurrentStep() {
    const { location } = this.props;
    const { pathname } = location;
    const pathList = pathname.split('/');
    switch (pathList[pathList.length - 1]) {
      case 'step_add_task': return 0;
      case 'step2': return 1;
      default: return 0;
    }
  }
  getCurrentComponent() {
    const componentMap = {
      0: Step1,
      1: Step2,
    };
    return componentMap[this.getCurrentStep()];
  }
  render() {
    const { form, task, submitting, dispatch } = this.props;
    const formItemLayout = {
      labelCol: {
        span: 5,
      },
      wrapperCol: {
        span: 19,
      },
    };
    const CurrentComponent = this.getCurrentComponent();
    return (
      <PageHeaderLayout title="新建下单">
        <Card bordered={false}>
          <div>
            <Steps current={this.getCurrentStep()} className={styles.steps}>
              <Step title="添加app信息" />
              <Step title="添加下单关键词" />
              <Step title="完成" />
            </Steps>
            <CurrentComponent
              formItemLayout={formItemLayout}
              form={form}
              dispatch={dispatch}
              task={task}
              submitting={submitting}
            />
          </div>
        </Card>
      </PageHeaderLayout>
    );
  }
}

export default connect(state => ({
  task: state.task,
  submitting: state.form.stepFormSubmitting,
}))(StepAddTask);
