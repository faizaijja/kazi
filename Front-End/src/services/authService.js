import api from './api'

export const authService = {
  async login(email, password) {
    try {
      const response = await api.post('/api/auth/login', {
        email,
        password,
      })
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Login failed')
    }
  },

  async register(userData) {
    try {
      const response = await api.post('/api/auth/register', userData)
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Registration failed')
    }
  },
}

