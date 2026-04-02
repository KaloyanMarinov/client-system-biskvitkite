import {
  RequestResult,
  DEFAULT_REQUEST_OPTIONS,
  RequestOptions
} from '../interfaces/_request';

export default class HttpService {
  private BASE_URL = window.vars.ajax_url;
  xhr: XMLHttpRequest;

  // eslint-disable-next-line @typescript-eslint/no-empty-function
  constructor() {
    this.xhr = new XMLHttpRequest();
  }

  queryParams(params: any = []): string {
    return Object.keys(params).map(k => {
      if (typeof params[k] === 'object') {
        return this.queryParams(params[k]);
      } else {
        return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
      }
    }).join('&');
  }

  withQuery(url: string, params: any = {}): string {
    const queryString = this.queryParams(params);
    return queryString ? url + (url.indexOf('?') === -1 ? '?' : '&') + queryString : url;
  }

  parseXHRResult(xhr: XMLHttpRequest): RequestResult {
    return {
      ok: xhr.status >= 200 && xhr.status < 300,
      status: xhr.status,
      statusText: xhr.statusText,
      headers: xhr.getAllResponseHeaders(),
      data: xhr.responseText,
      json: <T>() => JSON.parse(xhr.responseText) as T,
    };
  }

  errorResponse(xhr: XMLHttpRequest, message: string | null = null): RequestResult {
    return {
      ok: false,
      status: xhr.status,
      statusText: xhr.statusText,
      headers: xhr.getAllResponseHeaders(),
      data: message || xhr.statusText,
      json: <T>() => JSON.parse(message || xhr.statusText) as T,
    };
  }

  request(
    method: 'get' | 'post',
    queryParams: any = {},
    body: any = null,
    options: RequestOptions = DEFAULT_REQUEST_OPTIONS) : Promise<RequestResult> {
    
    const ignoreCache = options.ignoreCache || DEFAULT_REQUEST_OPTIONS.ignoreCache;
    const headers = options.headers || DEFAULT_REQUEST_OPTIONS.headers;
    const timeout = options.timeout || DEFAULT_REQUEST_OPTIONS.timeout;

    this.xhr.abort();

    return new Promise<RequestResult>((resolve) => {
      const xhr = this.xhr;

      xhr.open(method, this.withQuery(this.BASE_URL, queryParams));

      if (headers) {
        Object.keys(headers).forEach(key => xhr.setRequestHeader(key, headers[key]));
      }

      if (ignoreCache) {
        xhr.setRequestHeader('Cache-Control', 'no-cache');
      }

      xhr.timeout = timeout;

      xhr.onload = () => {
        resolve(this.parseXHRResult(xhr));
      };

      xhr.onerror = () => {
        resolve(this.errorResponse(xhr, 'Failed to make request.'));
      };

      xhr.ontimeout = () => {
        resolve(this.errorResponse(xhr, 'Request took longer than expected.'));
      };

      if (method === 'post' && body) {
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify(body));
      } else {
        xhr.send();
      }
    });
  }
}