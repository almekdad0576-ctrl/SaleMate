import './App.css'
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './shared/contexts/AuthContext';
import { ProtectedRoute, PublicRoute } from './app/routes';
import { HomePage } from './app/HomePage';
import { NotFoundPage } from './app/NotFoundPage';
import { LoginPage } from './features/users/components/LoginPage';

function App() {
  return (
    <Router>
      <AuthProvider>
        <Routes>
          <Route path="/login" element={<PublicRoute component={LoginPage} />} />
          <Route path="/" element={<ProtectedRoute component={HomePage} />} />
          <Route path="*" element={<NotFoundPage />} />
        </Routes>
      </AuthProvider>
    </Router>
  )
}

export default App
