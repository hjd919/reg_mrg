import { queryApp, queryHourlyStat, stopTaskKeyword, fetchDailyStat } from '../services/task';
import { routerRedux } from 'dva/router';

export default {
  namespace: 'app',

  state: {
    query_params: {},
    data: {
      list: [],
      pagination: {},
    },
    hourl_stat: {
      list: [],
      pagination: {},
    },
    daily_stat:{
      list: [],
      pagination: {},
    },
    loading: true,
  },

  effects: {
    *stop({ payload }, { call, put }) {
      const response = yield call(stopTaskKeyword, payload)

      yield put({
        type: 'fetch',
        payload: {},
      });
    },
    *fetch({ payload }, { call, put, select }) {
      yield put({
        type: 'changeLoading',
        payload: true,
      });

      // 合并store查询参数到payload
      const query_params = yield select(state => state.app.query_params);
      payload = { ...query_params, ...payload }

      // 设置查询参数到store
      yield put({
        type: 'setQueryParams',
        payload: payload,
      });

      const response = yield call(queryApp, payload);
      yield put({
        type: 'fetchSuccess',
        payload: response,
      });
      yield put({
        type: 'changeLoading',
        payload: false,
      });

      // 设置查询参数到storage

    },
    *fetchDailyStat({ payload }, { call, put, select }) {
      yield put({
        type: 'changeLoading',
        payload: true,
      });

      // 合并store查询参数到payload
      const query_params = yield select(state => state.app.query_params);
      payload = { ...query_params, ...payload }

      // 设置查询参数到store
      yield put({
        type: 'setQueryParams',
        payload: payload,
      });
      console.log(2)

      const response = yield call(fetchDailyStat, payload);
      console.log(response)
      yield put({
        type: 'fetchDailyStatSuccess',
        payload: response,
      });


      yield put({
        type: 'changeLoading',
        payload: false,
      });
    },
    *fetchHourlyStat({ payload }, { call, put, select }) {
      yield put({
        type: 'changeLoading',
        payload: true,
      });

      // 合并store查询参数到payload
      const query_params = yield select(state => state.app.query_params);
      payload = { ...query_params, ...payload }

      // 设置查询参数到store
      yield put({
        type: 'setQueryParams',
        payload: payload,
      });

      const response = yield call(queryHourlyStat, payload);
      yield put({
        type: 'fetchHourlyStatSuccess',
        payload: response,
      });


      yield put({
        type: 'changeLoading',
        payload: false,
      });
    }
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
    fetchHourlyStatSuccess(state, action) {
      return {
        ...state,
        hourl_stat: action.payload,
      };
    },
    fetchDailyStatSuccess(state, action) {
      return {
        ...state,
        daily_stat: action.payload,
      };
    },
    changeLoading(state, action) {
      return {
        ...state,
        loading: action.payload,
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
