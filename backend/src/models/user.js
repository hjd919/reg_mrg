import { query as queryUsers, queryCurrent } from '../services/user';
import { routerRedux } from 'dva/router'

export default {
  namespace: 'user',

  state: {
    list: [],
    loading: false,
    currentUser: {},
  },

  effects: {
    *fetch(_, { call, put }) {
      yield put({
        type: 'changeLoading',
        payload: true,
      });
      const response = yield call(queryUsers);
      yield put({
        type: 'save',
        payload: response,
      });
      yield put({
        type: 'changeLoading',
        payload: false,
      });
    },
    *fetchCurrent(_, { call, put }) {
      // *利用token获取用户信息

      // 获取token
      const getToken = () => {

        // 判断localStorage中是否存在token
        const token = localStorage.token
        if (!token) {
          // console.log('判断localStorage中是否存在token')
          return false
        }

        // 判断localStorage中token_expire是否小于当前时间，否则过期了
        const token_expire = localStorage.token_expire
        if (token_expire < (new Date).getTime()) {
          console.log('判断localStorage中token_expire是否大于当前时间，否则过期了')
          return false
        }

        return token
      }
      const token = getToken()

      // 获取不到token,没有登录，则跳转到登录页
      if (!token) {
        let from = location.pathname
        yield put(routerRedux.push('/user/login?from=' + from))
        return false
      }

      const response = yield call(queryCurrent);
      yield put({
        type: 'saveCurrentUser',
        payload: response,
      });
    },
  },

  reducers: {
    save(state, action) {
      return {
        ...state,
        list: action.payload,
      };
    },
    changeLoading(state, action) {
      return {
        ...state,
        loading: action.payload,
      };
    },
    saveCurrentUser(state, action) {
      return {
        ...state,
        currentUser: action.payload,
      };
    },
    changeNotifyCount(state, action) {
      return {
        ...state,
        currentUser: {
          ...state.currentUser,
          notifyCount: action.payload,
        },
      };
    },
  },
};
