import { apiClient } from '../utils/apiClient';
import type { User, LoginCredentials, LoginResponseData, ApiResponse } from '../../features/users/types';

export const authService = {
  async login(credentials: LoginCredentials): Promise<{ token: string; user: User }> {
    const response = await apiClient.post<ApiResponse<LoginResponseData>>(
      '/login',
      credentials
    );
    
    return {
      token: response.data.data.access_token,
      user: response.data.data.user,
    };
  },

  async logout(): Promise<void> {
    await apiClient.post('/logout');
  },

  async getProfile(): Promise<User> {
    const response = await apiClient.get<ApiResponse<{ user: User }>>('/profile');
    return response.data.data.user;
  },

  async updateProfile(data: Partial<User>): Promise<User> {
    const response = await apiClient.put<ApiResponse<{ user: User }>>('/profile', data);
    return response.data.data.user;
  },

  async changePassword(data: {
    current_password: string;
    password: string;
  }): Promise<void> {
    await apiClient.put('/password', data);
  },
};
