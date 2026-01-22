import axios from 'axios'

const API_URL = "http://localhost/Kazi";

// Create axios instance
const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
  withCredentials: true, // if you need cookies/sessions
})

export const jobService = {
  async getJobs(filters = {}) {
    try {
      const response = await api.get('/jobs.php', { params: filters })
      return response.data // Returns { success, data, count }
    } catch (error) {
      console.error('getJobs error:', error)
      throw new Error(error.response?.data?.message || 'Failed to fetch jobs')
    }
  },

  async getJobById(jobId) {
    try {
      const response = await api.get(`/jobs.php?id=${jobId}`)
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to fetch job')
    }
  },

  


  async createJob(jobData) {
    try {
      const response = await api.post('/jobs.php', jobData)
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to create job')
    }
  },

  async updateJob(jobId, jobData) {
    try {
      const response = await api.put(`/jobs.php/${jobId}`, jobData)
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to update job')
    }
  },

  async deleteJob(jobId) {
    try {
      const response = await api.delete(`/jobs.php/${jobId}`)
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Failed to delete job')
    }
  },
}

