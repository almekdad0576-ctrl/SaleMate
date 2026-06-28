import React from 'react';
import { useAuth } from '../shared/hooks/useAuth';
import { useNavigate } from 'react-router-dom';
import './HomePage.css';

export const HomePage: React.FC = () => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    logout();
    navigate('/login');
  };

  return (
    <div className="home-container">
      <nav className="navbar">
        <div className="navbar-content">
          <h1>App</h1>
          {user && (
            <div className="user-menu">
              <span>Welcome, {user.name}</span>
              <button onClick={handleLogout} className="logout-btn">
                Logout
              </button>
            </div>
          )}
        </div>
      </nav>

      <main className="home-content">
        {user ? (
          <>
            <h2>Dashboard</h2>
            <div className="user-info">
              <p>
                <strong>Name:</strong> {user.name}
              </p>
              <p>
                <strong>Email:</strong> {user.email}
              </p>
              <p>
                <strong>Role:</strong> {user.role}
              </p>
            </div>
          </>
        ) : (
          <>
            <h2>Welcome</h2>
            <p>Please log in to access the dashboard.</p>
          </>
        )}
      </main>
    </div>
  );
};
