import { createContext, useContext, useState, useEffect } from 'react'
import { authService } from '../services/authService'

const AuthContext = createContext(null)

export const useAuth = () => {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider')
  }
  return context
}

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const storedUser = localStorage.getItem('user')
    const storedToken = localStorage.getItem('token')
    
    if (storedUser && storedToken) {
      setUser(JSON.parse(storedUser))
    }
    setLoading(false)
  }, [])

  const login = async (email, password) => {
    try {
      const response = await authService.login(email, password)
      if (response.success) {
        setUser(response.user)
        localStorage.setItem('user', JSON.stringify(response.user))
        localStorage.setItem('token', response.token)
        return { success: true }
      }
      return { success: false, message: response.message }
    } catch (error) {
      return { success: false, message: error.message || 'Login failed' }
    }
  }

  const register = async (userData) => {
    try {
      const response = await authService.register(userData)
      if (response.success) {
        return { success: true, message: response.message }
      }
      return { success: false, message: response.message }
    } catch (error) {
      return { success: false, message: error.message || 'Registration failed' }
    }
  }

  const logout = () => {
    setUser(null)
    localStorage.removeItem('user')
    localStorage.removeItem('token')
  }

  const value = {
    user,
    login,
    register,
    logout,
    loading,
    isAuthenticated: !!user,
  }

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

