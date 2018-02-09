import { routerRedux } from 'dva/router';

import { refreshToken, accountLogin, fakeMobileLogin } from '../services/api';

export default {
  namespace: 'login',

  state: {
    status: undefined,
  },

  effects: {
    *refreshToken({ }, { call, put }) {
      const response = yield call(refreshToken);
      const { access_token, expires_in } = response
      if (!access_token || !expires_in) {
        return false
      }

      if (!response.status && response.status !== 'error') {
        response.status = 'success'
        response.type = 'account'
      }

      yield put({
        type: 'changeLoginStatus',
        payload: response,
      });
    },
    *accountSubmit({ payload }, { call, put }) {
      yield put({
        type: 'changeSubmitting',
        payload: true,
      });
      const response = yield call(accountLogin, payload);

      // 判断是否登录成功
      if (!response.status && response.status !== 'error') {
        response.status = 'success'
        response.type = 'account'
      }

      yield put({
        type: 'changeLoginStatus',
        payload: response,
      });
      yield put({
        type: 'changeSubmitting',
        payload: false,
      });

      // 成功则跳转
      if (response.status == 'success') {

        // 判断是否存在from，则跳转到from
        if (location.search) {
          const search = querystring.parse(location.search)
          if (search.from) {
            yield put(routerRedux.push(from))
            return true
          }
        }

        // 不存在from，则跳转主页
        yield put(routerRedux.push('/dashboard/analysis'))
      }
    },
    *mobileSubmit(_, { call, put }) {
      yield put({
        type: 'changeSubmitting',
        payload: true,
      });
      const response = yield call(fakeMobileLogin);
      yield put({
        type: 'changeLoginStatus',
        payload: response,
      });
      yield put({
        type: 'changeSubmitting',
        payload: false,
      });
    },
    *logout(_, { put }) {
      yield put({
        type: 'changeLoginStatus',
        payload: {
          status: false,
        },
      });
      yield put(routerRedux.push('/user/login'));
    },
  },

  reducers: {
    changeLoginStatus(state, { payload: { access_token, expires_in, status, type } }) {

      if(access_token == 'undefined'){
        let from = '/'
        dispatch(routerRedux.push('/user/login?from=' + from))
      }

      // 在localStorage添加登录凭证
      localStorage.token = access_token
      localStorage.token_expire = (new Date).getTime() + expires_in * 1000

      return {
        ...state,
        status,
        type,
      };
    },
    changeSubmitting(state, { payload }) {
      return {
        ...state,
        submitting: payload,
      };
    },
  },
};
