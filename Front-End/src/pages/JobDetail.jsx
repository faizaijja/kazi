import { useEffect, useState } from 'react'
import { useParams, Link, useNavigate } from 'react-router-dom'
import { jobService } from '../services/jobService'
import { MapPin, Calendar, DollarSign, Clock, ArrowLeft, User } from 'lucide-react'
import { useAuth } from '../contexts/AuthContext'
import toast from 'react-hot-toast'

const JobDetail = () => {
  const { id } = useParams()
  const { user } = useAuth()
  const navigate = useNavigate()
  const [job, setJob] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchJob()
  }, [id])

  const fetchJob = async () => {
    try {
      setLoading(true)
      const response = await jobService.getJobById(id)
      if (response.success) {
        setJob(response.data)
      } else {
        toast.error(response.message || 'Job not found')
        navigate('/jobs')
      }
    } catch (error) {
      toast.error('Failed to load job details')
      navigate('/jobs')
    } finally {
      setLoading(false)
    }
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-azure-500"></div>
      </div>
    )
  }

  if (!job) {
    return null
  }

  const urgencyColors = {
    low: 'bg-green-100 text-green-800',
    medium: 'bg-yellow-100 text-yellow-800',
    high: 'bg-orange-100 text-orange-800',
    emergency: 'bg-red-100 text-red-800',
  }

  return (
    <div className="space-y-6">
      <Link
        to="/jobs"
        className="inline-flex items-center space-x-2 text-azure-500 hover:text-azure-600 font-semibold"
      >
        <ArrowLeft size={20} />
        <span>Back to Jobs</span>
      </Link>

      <div className="card">
        <div className="flex items-start justify-between mb-6">
          <div className="flex-1">
            <h1 className="text-3xl font-bold text-gray-900 mb-2">{job.title}</h1>
            <div className="flex items-center space-x-4 text-sm text-gray-600">
              {job.category_name && (
                <span className="px-3 py-1 bg-azure-50 text-azure-700 rounded-full">
                  {job.category_name}
                </span>
              )}
              <span
                className={`px-3 py-1 rounded-full font-semibold ${
                  urgencyColors[job.urgency] || urgencyColors.medium
                }`}
              >
                {job.urgency?.toUpperCase() || 'MEDIUM'}
              </span>
            </div>
          </div>
        </div>

        <div className="prose max-w-none mb-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-2">Description</h3>
          <p className="text-gray-700 whitespace-pre-wrap">{job.description}</p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div className="flex items-start space-x-3">
            <DollarSign className="text-azure-500 mt-1" size={20} />
            <div>
              <p className="text-sm text-gray-500">Budget</p>
              <p className="font-semibold text-gray-900">
                {job.budget_min?.toLocaleString() || 'N/A'} - {job.budget_max?.toLocaleString() || 'N/A'} RWF
              </p>
            </div>
          </div>

          <div className="flex items-start space-x-3">
            <Calendar className="text-azure-500 mt-1" size={20} />
            <div>
              <p className="text-sm text-gray-500">Preferred Date</p>
              <p className="font-semibold text-gray-900">
                {job.preferred_date
                  ? new Date(job.preferred_date).toLocaleDateString()
                  : 'Not specified'}
              </p>
            </div>
          </div>

          {job.client_name && (
            <div className="flex items-start space-x-3">
              <User className="text-azure-500 mt-1" size={20} />
              <div>
                <p className="text-sm text-gray-500">Posted by</p>
                <p className="font-semibold text-gray-900">{job.client_name}</p>
              </div>
            </div>
          )}

          {job.status && (
            <div className="flex items-start space-x-3">
              <Clock className="text-azure-500 mt-1" size={20} />
              <div>
                <p className="text-sm text-gray-500">Status</p>
                <p className="font-semibold text-gray-900 capitalize">{job.status}</p>
              </div>
            </div>
          )}
        </div>

        {user?.user_type === 'service_provider' && job.status === 'open' && (
          <div className="pt-6 border-t border-gray-200">
            <button className="btn-primary">
              Apply for this Job
            </button>
          </div>
        )}
      </div>
    </div>
  )
}

export default JobDetail

