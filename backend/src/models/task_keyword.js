import { queryTaskKeyword, saveTask, stopTaskKeyword } from '../services/task';
import { routerRedux } from 'dva/router';

export default {
  namespace: 'task_keyword',

  state: {
    query_params: {},
    data: {
      list: [],
      pagination: {},
    },
    loading: true,
  },

  effects: {
    *fetch({ payload }, { call, put, select }) {
      yield put({
        type: 'changeLoading',
        payload: true,
      });

      // 合并url的查询参数
      const query_params = yield select(state => state.task_keyword.query_params);
      payload = { ...payload, ...query_params }

      const response = yield call(queryTaskKeyword, payload);
      yield put({
        type: 'fetchSuccess',
        payload: response,
      });
      yield put({
        type: 'changeLoading',
        payload: false,
      });
    },
  },

  reducers: {
    setQueryParams(state, action) {
      return {
        ...state,
        query_params: action.payload,
      };
    },
    fetchSuccess(state, action) {
      return {
        ...state,
        data: action.payload,
      };
    },
    changeLoading(state, action) {
      return {
        ...state,
        loading: action.payload,
      };
    },
  },
};
