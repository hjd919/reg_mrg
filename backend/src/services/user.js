import request, { requestAuthApi } from '../utils/request';

export async function query() {
  return request('/api/users');
}

export async function queryCurrent() {
  return requestAuthApi('/backend/auth/me');
}
