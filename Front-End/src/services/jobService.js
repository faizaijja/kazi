import api from './api'

export const jobService = {
  async getJobs(filters = {}) {
    try {
      const response = await api.get('/api/jobs', { params: filters })
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch jobs')
    }
  },

  async getJobById(jobId) {
    try {
      const response = await api.get(`/api/jobs/${jobId}`)
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch job')
    }
  },

  async createJob(jobData) {
    try {
      const response = await api.post('/api/jobs', jobData)
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to create job')
    }
  },

  async updateJob(jobId, jobData) {
    try {
      const response = await api.put(`/api/jobs/${jobId}`, jobData)
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to update job')
    }
  },

  async deleteJob(jobId) {
    try {
      const response = await api.delete(`/api/jobs/${jobId}`)
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to delete job')
    }
  },
}

