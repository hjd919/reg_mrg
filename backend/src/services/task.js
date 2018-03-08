import { requestAuthApi } from '../utils/request';

export async function queryTask(params) {
  return requestAuthApi('/backend/task/query', { query: params });
}

export async function saveTask(params) {
  return requestAuthApi('/backend/task/save', {
    method: 'POST',
    body: params,
  });
}

export async function getFreeMobileNum(params) {
  return requestAuthApi('/backend/task/getFreeMobileNum',{query:params});
}

export async function saveTaskKeyword(params) {
  return requestAuthApi('/backend/task/saveTaskKeyword', {
    method: 'POST',
    body: params,
  });
}

export async function queryTaskKeyword(params) {
  return requestAuthApi('/backend/task_keyword/query', { query: params });
}

export async function queryApp(params) {
  return requestAuthApi('/backend/app/query', { query: params });
}

export async function stopTaskKeyword(params) {
  return requestAuthApi('/backend/task_keyword/stop', { query: params });
}

// 查询每小时统计
export async function queryHourlyStat(params){
  return requestAuthApi('/backend/app/query_hourly_stat', { query: params });
}

export async function stopAllTask(params) {
  return requestAuthApi('/backend/task/stop', { query: params });
}

export async function getTodayAppleidNum() {
  return requestAuthApi('/backend/appleid/get_today_num');
}
export async function getTodayEmailNum() {
  return requestAuthApi('/backend/email/get_today_num');
}

export async function stateImport() {
  return requestAuthApi('/backend/email/state_import');
}

export async function fetchDailyStat(params) {
  return requestAuthApi('/backend/app/query_daily_stat', { query: params });
}

export async function addSpareTask(params) {
  return requestAuthApi('/backend/task/addSpareTask', {
    method: 'POST',
    body: params,
  });
}