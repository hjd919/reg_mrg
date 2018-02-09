import { notification } from 'antd';
import fetch from 'dva/fetch';
import querystring from 'querystring';

import { domain } from '../config';

function checkStatus(response) {
  if (response.status >= 200 && response.status < 300) {
    return response;
  }

  notification.error({
    message: `请求错误 ${response.status}: ${response.url}`,
    description: response.statusText,
  });
  const error = new Error(response.statusText);
  error.response = response;
  throw error;
}

/**
 * Requests a URL, returning a promise.
 *
 * @param  {string} url       The URL we want to request
 * @param  {object} [options] The options we want to pass to "fetch"
 * @return {object}           An object containing either "data" or "err"
 */
export default function request(url, options) {
  const defaultOptions = {
    credentials: 'include',
  };
  const newOptions = { ...defaultOptions, ...options };
  if (newOptions.method === 'POST' || newOptions.method === 'PUT') {
    newOptions.headers = {
      Accept: 'application/json',
      'Content-Type': 'application/json; charset=utf-8',
      ...newOptions.headers,
    };
    newOptions.body = JSON.stringify(newOptions.body);
  }

  url = newOptions.query ? `${url}?${querystring.stringify(newOptions.query)}` : url;

  return fetch(url, newOptions)
    .then(checkStatus)
    .then(response => response.json())
    .catch((error) => {
      if (error.code) {
        notification.error({
          message: error.name,
          description: error.message,
        });
      }
      if ('stack' in error && 'message' in error) {
        notification.error({
          message: `请求错误: ${url}`,
          description: error.message,
        });
      }
      return error;
    });
}

export function requestApi(url, options) {
  return request(domain + url, options)
}

export function requestAuthApi(url, options = {}) {

  // 从localStorage获取token,附加到请求header参数中
  options.headers = {
    Authorization: 'Bearer ' + localStorage.token
  }

  return requestApi(url, options)
}