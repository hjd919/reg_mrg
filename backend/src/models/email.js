import { getTodayEmailNum, stateImport } from '../services/task';
import { routerRedux } from 'dva/router';

export default  {
  namespace: 'email',

  state: {
    state_import: {
      list: [],
      pagination: {},
    },
    query_params: {},
    loading: true,
  },

  effects: {
    *getTodayEmailNum({ payload }, { call, put }) {
      const response = yield call(getTodayEmailNum, payload)

      yield put({
        type: 'getTodayEmailNumSuccess',
        payload: response.today_email_num,
      });
    },
    *stateImport({ payload }, { select, call, put }) {
      yield put({
        type: 'changeLoading',
        payload: true,
      });

      // 合并store查询参数到payload
      const query_params = yield select(state => state.email.query_params);
      payload = { ...query_params, ...payload }

      // 设置查询参数到store
      yield put({
        type: 'setQueryParams',
        payload: payload,
      });

      const response = yield call(stateImport, payload);

      yield put({
        type: 'stateImportSuccess',
        payload: response,
      });

      yield put({
        type: 'changeLoading',
        payload: false,
      });
    },
  },

  reducers: {
    getTodayEmailNumSuccess(state, action) {
      return {
        ...state,
        today_email_num: action.payload,
      };
    },
    stateImportSuccess(state, action) {
      return {
        ...state,
        state_import: action.payload,
      };
    },
    changeLoading(state, action) {
      return {
        ...state,
        loading: action.payload,
      };
    },
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
    }
  },
};
