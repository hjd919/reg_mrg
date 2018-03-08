import { queryNotices, refreshToken } from '../services/api';
import { routerRedux } from 'dva/router';

export default {
  namespace: 'global',

  state: {
    collapsed: false,
    notices: [],
    fetchingNotices: false,
  },

  effects: {
    *fetchNotices(_, { call, put }) {
      yield put({
        type: 'changeNoticeLoading',
        payload: true,
      });
      const data = yield call(queryNotices);
      yield put({
        type: 'saveNotices',
        payload: data,
      });
    },
    *clearNotices({ payload }, { put, select }) {
      const count = yield select(state => state.global.notices.length);
      yield put({
        type: 'user/changeNotifyCount',
        payload: count,
      });

      yield put({
        type: 'saveClearedNotices',
        payload,
      });
    },
    *refreshToken({ }, { call }) {
      const data = yield call(refreshToken);
      console.log('refreshtoken', data)
      if (!data) {
        let from = location.pathname
        dispatch(routerRedux.push('/user/login?from=' + from))
      }

      localStorage.token = data.access_token
      localStorage.token_expire = (new Date).getTime() + data.expires_in * 1000
    },
  },

  reducers: {
    changeLayoutCollapsed(state, { payload }) {
      return {
        ...state,
        collapsed: payload,
      };
    },
    saveNotices(state, { payload }) {
      return {
        ...state,
        notices: payload,
        fetchingNotices: false,
      };
    },
    saveClearedNotices(state, { payload }) {
      return {
        ...state,
        notices: state.notices.filter(item => item.type !== payload),
      };
    },
    changeNoticeLoading(state, { payload }) {
      return {
        ...state,
        fetchingNotices: payload,
      };
    },
  },

  subscriptions: {
    setup({ dispatch, history }) {
      // Subscribe history(url) change, trigger `load` action if pathname is `/`
      return history.listen(({ pathname, search }) => {
        if (typeof window.ga !== 'undefined') {
          window.ga('send', 'pageview', pathname + search);
        }

        // 判断是否过期
        const token_expire = localStorage.token_expire
        if (token_expire) {
          // 异常
          if (token_expire == 'NaN') {
            let from = history.location.pathname
            if (from !== '/user/login') {
              dispatch(routerRedux.push('/user/login?from=' + from))
            }
            return true
          }
          const expire_diff = token_expire - (new Date).getTime()
          if (expire_diff <= 1800000 && expire_diff > 0) {
            // 刷新token
            dispatch({ type: 'refreshToken' })
          } else if (expire_diff <= 0) {
            // 过期了,跳到登录页
            let from = history.location.pathname
            if (from !== '/user/login') {
              dispatch(routerRedux.push('/user/login?from=' + from))
            }
          }
        }
      });
    },
  },
};
