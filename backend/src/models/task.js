import { message } from 'antd';
import { addSpareTask, queryTask, getFreeMobileNum, saveTaskKeyword, saveTask, stopAllTask } from '../services/task';
import { routerRedux } from 'dva/router';

export default {
  namespace: 'task',

  state: {
    query_params: {},
    data: {
      list: [],
      pagination: {},
    },
    form: {
      task_id: 0,
      app_name: '',
      keywords: {},
      free_mobile_num: 0,
      usable_brush_num: 10000,
      real_used_mobile_num:0,
      useful_comment_num:0,
    },
    success_keyword_list: [],
    loading: true,
  },

  effects: {
    *fetch({ payload }, { call, put, select }) {
      yield put({
        type: 'changeLoading',
        payload: true,
      });

      // 合并store查询参数到payload
      const query_params = yield select(state => state.task.query_params);
      payload = { ...query_params, ...payload }

      // 设置查询参数到store
      yield put({
        type: 'setQueryParams',
        payload: payload,
      });

      const response = yield call(queryTask, payload);
      yield put({
        type: 'fetchSuccess',
        payload: response,
      });
      yield put({
        type: 'changeLoading',
        payload: false,
      });
    },
    *save({ payload }, { call, put }) {
      const response = yield call(saveTask, payload);

      yield put({
        type: 'saveSuccess',
        payload: response.task_id,
      });

      yield put(routerRedux.push('/task/step_add_task/step2'));
    },
    *getFreeMobileNum({ }, { call, put, select }) {
      const task_id = yield select(state => state.task.form.task_id);
      const response = yield call(getFreeMobileNum, { task_id });

      yield put({
        type: 'getFreeMobileNumSuccess',
        payload: {
          app_name: response.app_name,// 可以打的量
          free_mobile_num: response.free_mobile_num,// 可以打的量
          usable_brush_num: response.usable_brush_num,// 可以打的量
          exception_mobile_num: response.exception_mobile_num,// 可以打的量
          real_used_mobile_num: response.real_used_mobile_num,
          usable_brush_device: response.usable_brush_device,
          useful_comment_num: response.useful_comment_num,
        },
      });

      return response.app_name
    },
    *saveTaskKeyword({ payload }, { call, put }) {
      const response = yield call(saveTaskKeyword, payload);
      const { error_code, message } = response
      if (error_code) {
        message.error(message)
        return false;
      }

      for (let row of response.app_ids) {
        yield put({
          type: 'saveTaskKeywordSuccess',
          payload: { app_id: row.app_id, keyword: row.keyword, app_name: response.app_name },
        });
      }

      return true
    },
    *stop_task({ payload }, { call }) {
      const res = yield call(stopAllTask, payload);
      message.success(res.message);
    },
    *addSpareTask({ payload }, { call, put }) {
      const response = yield call(addSpareTask, payload);
      const { error_code, message } = response
      if (error_code) {
        message.error(message)
        return false;
      }
      return true
    }
  },

  reducers: {
    setQueryParams(state, action) {
      return {
        ...state,
        query_params: action.payload,
      };
    },
    clearQueryParams(state, action) {
      return {
        ...state,
        query_params: {},
      };
    },
    saveUsableBrushNum(state, action) {
      return {
        ...state,
        form: {
          ...state.form,
          usable_brush_num: action.payload
        },
      };
    },
    fetchSuccess(state, action) {
      return {
        ...state,
        data: action.payload,
      };
    },
    getFreeMobileNumSuccess(state, action) {
      return {
        ...state,
        form: {
          ...state.form,
          ...action.payload
        },
      };
    },
    saveTaskKeywordSuccess(state, action) {
      return {
        ...state,
        success_keyword_list: state.success_keyword_list.concat(action.payload)
      }
    },
    saveSuccess(state, action) {
      return {
        ...state,
        form: {
          ...state.form,
          task_id: action.payload,
        },
      }
    },
    changeLoading(state, action) {
      return {
        ...state,
        loading: action.payload,
      };
    },
    clearAddedRecord(state, action) {
      return {
        ...state,
        success_keyword_list: []
      }
    },
  },
};
