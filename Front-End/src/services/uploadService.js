import api from './api'

export const uploadService = {
  async uploadDocument(file, providerId, documentType) {
    try {
      const formData = new FormData()
      formData.append('document', file)
      formData.append('provider_id', providerId)
      formData.append('document_type', documentType)

      const response = await api.post('/api/upload/document', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to upload document')
    }
  },

  async uploadProfilePicture(file, userId) {
    try {
      const formData = new FormData()
      formData.append('profile_picture', file)
      formData.append('user_id', userId)

      const response = await api.post('/api/upload/profile', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to upload profile picture')
    }
  },
}

