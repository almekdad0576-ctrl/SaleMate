export interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  created_at?: string;
  updated_at?: string;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface LoginResponseData {
  user: User;
  access_token: string;
  token_type: string;
}

export interface ApiResponse<T> {
  success: boolean;
  msg: string;
  data: T;
  code: number;
}
