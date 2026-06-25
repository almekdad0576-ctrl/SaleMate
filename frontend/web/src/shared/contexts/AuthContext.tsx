import React, { createContext, useState, useCallback, useEffect, type ReactNode } from 'react';
import type { User } from '../../features/users/types';
import { setAuthContext as setAxiosAuthContext } from '../utils/apiClient';

export interface AuthContextType {
  user: User | null;
  token: string | null;
  loading: boolean;
  error: string | null;
  setUser: (user: User | null) => void;
  setToken: (token: string | null) => void;
  setLoading: (loading: boolean) => void;
  setError: (error: string | null) => void;
  clearError: () => void;
  logout: () => void;
}

export const AuthContext = createContext<AuthContextType | undefined>(undefined);

interface AuthProviderProps {
  children: ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(
    localStorage.getItem('auth_token')
  );
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const clearError = useCallback(() => {
    setError(null);
  }, []);

  const logout = useCallback(() => {
    setUser(null);
    setToken(null);
    localStorage.removeItem('auth_token');
    clearError();
  }, [clearError]);

  const value: AuthContextType = {
    user,
    token,
    loading,
    error,
    setUser,
    setToken,
    setLoading,
    setError,
    clearError,
    logout,
  };

  // Initialize axios interceptors with auth context
  useEffect(() => {
    setAxiosAuthContext(value);
  }, [value]);

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};
