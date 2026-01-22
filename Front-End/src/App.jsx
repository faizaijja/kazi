import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom'
import { AuthProvider } from './contexts/AuthContext'
import { Toaster } from 'react-hot-toast'
import PrivateRoute from './components/PrivateRoute'

// Pages
import Login from './pages/Login'
import Register from './pages/Register'
import Dashboard from './pages/Dashboard'
import Jobs from './pages/Jobs'
import JobDetail from './pages/JobDetail'
import CreateJob from './pages/CreateJob'
import Providers from './pages/Providers'
import ProviderDetail from './pages/ProviderDetail'
import Profile from './pages/Profile'
import MyJobs from './pages/MyJobs'

function App() {
  return (
    <AuthProvider>
      <Router>
        <div className="min-h-screen bg-gray-50">
          <Routes>
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            <Route
              path="/dashboard"
              element={
                <PrivateRoute>
                  <Dashboard />
                </PrivateRoute>
              }
            />
            <Route
              path="/jobs"
              element={
                <PrivateRoute>
                  <Jobs />
                </PrivateRoute>
              }
            />
         
            <Route
              path="/jobs/create"
              element={
                <PrivateRoute>
                  <CreateJob />
                </PrivateRoute>
              }
            />
          
            <Route
              path="/jobs/:id"
              element={
                <PrivateRoute>
                  <JobDetail />
                </PrivateRoute>
              }
            />
            <Route
              path="/my-jobs"
              element={
                <PrivateRoute>
                  <MyJobs />
                </PrivateRoute>
              }
            />
            <Route
              path="/providers"
              element={
                <PrivateRoute>
                  <Providers />
                </PrivateRoute>
              }
            />
            <Route
              path="/providers/:id"
              element={
                <PrivateRoute>
                  <ProviderDetail />
                </PrivateRoute>
              }
            />
            <Route
              path="/profile"
              element={
                <PrivateRoute>
                  <Profile />
                </PrivateRoute>
              }
            />
            <Route path="/" element={<Navigate to="/dashboard" replace />} />
          </Routes>
          <Toaster position="top-right" />
        </div>
      </Router>
    </AuthProvider>
  )
}

export default App