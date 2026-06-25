import axios from 'axios';
import type { AxiosInstance, AxiosError } from 'axios';
import type { AuthContextType } from '../contexts/AuthContext';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

let authContext: AuthContextType | null = null;

export const setAuthContext = (context: AuthContextType) => {
  authContext = context;
};

const instance: AxiosInstance = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request interceptor to add token
instance.interceptors.request.use(
  (config) => {
    const token = authContext?.token || localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor to handle errors and token expiration
instance.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    if (error.response?.status === 401) {
      // Token expired or invalid
      if (authContext) {
        authContext.logout();
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

export const apiClient = instance;
