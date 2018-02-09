import BasicLayout from '../layouts/BasicLayout';
import UserLayout from '../layouts/UserLayout';
import BlankLayout from '../layouts/BlankLayout';

import Analysis from '../routes/Dashboard/Analysis';
import Monitor from '../routes/Dashboard/Monitor';
import Workplace from '../routes/Dashboard/Workplace';

import TableList from '../routes/List/TableList';
import CoverCardList from '../routes/List/CoverCardList';
import CardList from '../routes/List/CardList';
import FilterCardList from '../routes/List/FilterCardList';
import SearchList from '../routes/List/SearchList';
import BasicList from '../routes/List/BasicList';

import BasicProfile from '../routes/Profile/BasicProfile';
import AdvancedProfile from '../routes/Profile/AdvancedProfile';

import BasicForm from '../routes/Forms/BasicForm';
import AdvancedForm from '../routes/Forms/AdvancedForm';
import StepForm from '../routes/Forms/StepForm';
import Step2 from '../routes/Forms/StepForm/Step2';
import Step3 from '../routes/Forms/StepForm/Step3';

import Exception403 from '../routes/Exception/403';
import Exception404 from '../routes/Exception/404';
import Exception500 from '../routes/Exception/500';

import Success from '../routes/Result/Success';
import Error from '../routes/Result/Error';

import Login from '../routes/User/Login';
import Register from '../routes/User/Register';
import RegisterResult from '../routes/User/RegisterResult';

import TaskList from '../routes/Task/TaskList';
import StepAddTask from '../routes/Task/StepAddTask';
import AddTaskStep2 from '../routes/Task/StepAddTask/Step2';
import AddSpareTask from '../routes/Task/AddSpareTask';

// import TaskKeywordList from '../routes/TaskKeyword/TaskKeywordList';
import AddTaskKeyword from '../routes/TaskKeyword/AddTaskKeyword';

import AppList from '../routes/App/AppList';
import HourlStat from '../routes/App/HourlStat';
import DailyStat from '../routes/App/DailyStat';

import EmailImport from '../routes/Email/EmailImport';
import AppleidImport from '../routes/Email/AppleidImport';
import StateImport from '../routes/Email/StateImport';

const data = [{
  component: BasicLayout,
  layout: 'BasicLayout',
  name: '首页', // for breadcrumb
  path: '',
  children: [{
    name: 'Dashboard',
    icon: 'dashboard',
    path: 'dashboard',
    children: [{
      name: '分析页',
      path: 'analysis',
      component: Analysis,
      // }, {
      //   name: '监控页',
      //   path: 'monitor',
      //   component: Monitor,
      // }, {
      //   name: '任务管理',
      //   path: 'task',
      //   component: TaskList,
    }],
  }, {
    name: '下单管理',
    path: 'task',
    icon: 'table',
    children: [{
      name: '下单列表',
      path: 'list',
      component: TaskList,
    }, {
      name: '新建下单',
      path: 'step_add_task',
      component: StepAddTask,
      children: [{
        path: 'step2',
        component: AddTaskStep2,
      }],
    }, {
      path: 'add_task_keyword',
      component: AddTaskKeyword,
    }, {
      name: '新建空闲任务',
      path: 'add_spare_task',
        component: AddSpareTask,
    }],

  }, {
    name: '任务管理',
    path: 'app',
    icon: 'table',
    children: [{
      name: '任务列表',
      path: 'list',
      component: AppList,
    },
    {
      name: '每小时统计',
      path: 'hourl_stat',
      component: HourlStat,

    }, {
      name: '每天统计',
      path: 'daily_stat',
      component: DailyStat,

    }],
  }, {
    name: '苹果账号管理',
    path: 'email',
    icon: 'form',
    children: [{
      name: '导入苹果账号',
      path: 'import_email',
      component: EmailImport,
    },
    {
      name: '导入注册邮箱',
      path: 'import_appleid',
      component: AppleidImport,
    },
    {
      name: '统计导入账号',
      path: 'state_import',
      component: StateImport,
    }],
  },
    /* }, {
        name: '表单页',
        path: 'form',
        icon: 'form',
        children: [{
          name: '基础表单',
          path: 'basic-form',
          component: BasicForm,
        }, {
          name: '分步表单',
          path: 'step-form',
          component: StepForm,
          children: [{
            path: 'confirm',
            component: Step2,
          }, {
            path: 'result',
            component: Step3,
          }],
        }, {
          name: '高级表单',
          path: 'advanced-form',
          component: AdvancedForm,
        }],
      }, {
        name: '列表页',
        path: 'list',
        icon: 'table',
        children: [{
          name: '查询表格',
          path: 'table-list',
          component: TableList,
        }, {
          name: '标准列表',
          path: 'basic-list',
          component: BasicList,
        }, {
          name: '卡片列表',
          path: 'card-list',
          component: CardList,
        }, {
          name: '搜索列表（项目）',
          path: 'cover-card-list',
          component: CoverCardList,
        }, {
          name: '搜索列表（应用）',
          path: 'filter-card-list',
          component: FilterCardList,
        }, {
          name: '搜索列表（文章）',
          path: 'search',
          component: SearchList,
        }],
      }, {
        name: '详情页',
        path: 'profile',
        icon: 'profile',
        children: [{
          name: '基础详情页',
          path: 'basic',
          component: BasicProfile,
        }, {
          name: '高级详情页',
          path: 'advanced',
          component: AdvancedProfile,
        }],
      }, {
        name: '结果',
        path: 'result',
        icon: 'check-circle-o',
        children: [{
          name: '成功',
          path: 'success',
          component: Success,
        }, {
          name: '失败',
          path: 'fail',
          component: Error,
        }],
      }, {
        name: '异常',
        path: 'exception',
        icon: 'warning',
        children: [{
          name: '403',
          path: '403',
          component: Exception403,
        }, {
          name: '404',
          path: '404',
          component: Exception404,
        }, {
          name: '500',
          path: '500',
          component: Exception500,
        }],
        */
  ],
}, {
  component: UserLayout,
  layout: 'UserLayout',
  children: [{
    // name: '帐户',
    icon: 'user',
    path: 'user',
    children: [{
      name: '登录',
      path: 'login',
      component: Login,
    }, {
      // name: '注册',
      path: 'register',
      component: Register,
    }, {
      // name: '注册结果',
      path: 'register-result',
      component: RegisterResult,
    }],
  }],
}, /*,{
  component: BlankLayout,
  layout: 'BlankLayout',
  children: {
    name: '使用文档',
    path: 'http://pro.ant.design/docs/getting-started',
    target: '_blank',
    icon: 'book',
    
}}*/];

export function getNavData() {
  return data;
}

export default data;
