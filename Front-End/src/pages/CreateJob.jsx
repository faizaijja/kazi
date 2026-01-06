import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { jobService } from '../services/jobService'
import { ArrowLeft } from 'lucide-react'
import { useAuth } from '../contexts/AuthContext'
import toast from 'react-hot-toast'

const CreateJob = () => {
  const { user } = useAuth()
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    category_id: '',
    budget_min: '',
    budget_max: '',
    preferred_date: '',
    urgency: 'medium',
  })

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    })
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)

    try {
      const jobData = {
        ...formData,
        client_id: user.user_id,
        category_id: parseInt(formData.category_id),
        budget_min: parseFloat(formData.budget_min),
        budget_max: parseFloat(formData.budget_max),
      }

      const response = await jobService.createJob(jobData)
      if (response.success) {
        toast.success('Job posted successfully!')
        navigate('/jobs')
      } else {
        toast.error(response.message || 'Failed to create job')
      }
    } catch (error) {
      toast.error(error.message || 'An error occurred')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="max-w-3xl mx-auto space-y-6">
      <div className="flex items-center space-x-4">
        <button
          onClick={() => navigate('/jobs')}
          className="p-2 hover:bg-gray-100 rounded-lg"
        >
          <ArrowLeft size={20} />
        </button>
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Post a New Job</h1>
          <p className="text-gray-600">Fill in the details to post your job</p>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="card space-y-6">
        <div>
          <label htmlFor="title" className="block text-sm font-medium text-gray-700 mb-2">
            Job Title *
          </label>
          <input
            id="title"
            name="title"
            type="text"
            value={formData.title}
            onChange={handleChange}
            required
            className="input-field"
            placeholder="e.g., Need a plumber for bathroom repair"
          />
        </div>

        <div>
          <label htmlFor="category_id" className="block text-sm font-medium text-gray-700 mb-2">
            Category *
          </label>
          <select
            id="category_id"
            name="category_id"
            value={formData.category_id}
            onChange={handleChange}
            required
            className="input-field"
          >
            <option value="">Select a category</option>
            <option value="1">Plumbing</option>
            <option value="2">Electrical</option>
            <option value="3">Landscaping</option>
            <option value="4">Carpentry</option>
            <option value="5">Cleaning</option>
            <option value="6">Painting</option>
            <option value="7">IT Support</option>
            <option value="8">Appliance Repair</option>
            <option value="9">Auto Mechanic</option>
            <option value="10">Security</option>
          </select>
        </div>

        <div>
          <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-2">
            Description *
          </label>
          <textarea
            id="description"
            name="description"
            value={formData.description}
            onChange={handleChange}
            required
            rows={6}
            className="input-field"
            placeholder="Describe the job in detail..."
          />
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label htmlFor="budget_min" className="block text-sm font-medium text-gray-700 mb-2">
              Minimum Budget (RWF)
            </label>
            <input
              id="budget_min"
              name="budget_min"
              type="number"
              value={formData.budget_min}
              onChange={handleChange}
              className="input-field"
              placeholder="0"
            />
          </div>

          <div>
            <label htmlFor="budget_max" className="block text-sm font-medium text-gray-700 mb-2">
              Maximum Budget (RWF)
            </label>
            <input
              id="budget_max"
              name="budget_max"
              type="number"
              value={formData.budget_max}
              onChange={handleChange}
              className="input-field"
              placeholder="0"
            />
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label htmlFor="preferred_date" className="block text-sm font-medium text-gray-700 mb-2">
              Preferred Date
            </label>
            <input
              id="preferred_date"
              name="preferred_date"
              type="date"
              value={formData.preferred_date}
              onChange={handleChange}
              className="input-field"
            />
          </div>

          <div>
            <label htmlFor="urgency" className="block text-sm font-medium text-gray-700 mb-2">
              Urgency Level
            </label>
            <select
              id="urgency"
              name="urgency"
              value={formData.urgency}
              onChange={handleChange}
              className="input-field"
            >
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="emergency">Emergency</option>
            </select>
          </div>
        </div>

        <div className="flex space-x-4 pt-4">
          <button
            type="submit"
            disabled={loading}
            className="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {loading ? 'Posting...' : 'Post Job'}
          </button>
          <button
            type="button"
            onClick={() => navigate('/jobs')}
            className="btn-secondary"
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
  )
}

export default CreateJob

