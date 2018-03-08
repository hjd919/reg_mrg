import { getTodayAppleidNum } from '../services/task';
import { routerRedux } from 'dva/router';

export default {
  namespace: 'appleid',

  state: {
    today_appleid_num: 0,
  },

  effects: {
    *getTodayAppleidNum({ payload }, { call, put }) {
      const response = yield call(getTodayAppleidNum, payload)

      yield put({
        type: 'getTodayAppleidNumSuccess',
        payload: response.today_appleid_num,
      });
    },
  },

  reducers: {
    getTodayAppleidNumSuccess(state, action) {
      return {
        ...state,
        today_appleid_num: action.payload,
      };
    },
  },
};
