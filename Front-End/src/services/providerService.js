const API_URL = "http://localhost/Kazi";

export const providerService = {
  async getProviders(filters = {}) {
    try {
      const response = await api.get('/providers.php', { params: filters })
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch providers')
    }
  },

  async getProviderById(providerId) {
    try {
      const response = await api.get(`/providers.php/${providerId}`)
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch provider')
    }
  },

  async updateProvider(providerId, providerData) {
    try {
      const response = await api.put(`/providers.php/${providerId}`, providerData)
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to update provider')
    }
  },
}

