export const DEFAULT_REQUEST_OPTIONS = {
  ignoreCache: false,
  headers: {
    Accept: 'application/json, text/javascript, text/plain',
  },
  timeout: 5000,
};

export interface RequestOptions {
  ignoreCache?: boolean;
  headers    ?: {[key: string]:string};
  timeout    ?: number;
}

export interface RequestResult {
  ok        : boolean;
  status    : number;
  statusText: string;
  data      : string;
  json      : <T>() => T;
  headers   : string;
}

export interface RequestData {
  html    ?: string;
  has_more?: boolean;
}
