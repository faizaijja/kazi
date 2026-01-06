import api from './api'

export const providerService = {
  async getProviders(filters = {}) {
    try {
      const response = await api.get('/api/providers', { params: filters })
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch providers')
    }
  },

  async getProviderById(providerId) {
    try {
      const response = await api.get(`/api/providers/${providerId}`)
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch provider')
    }
  },

  async updateProvider(providerId, providerData) {
    try {
      const response = await api.put(`/api/providers/${providerId}`, providerData)
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to update provider')
    }
  },
}

