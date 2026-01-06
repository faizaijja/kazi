import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { jobService } from '../services/jobService'
import JobCard from '../components/JobCard'
import { Plus, Search, Filter } from 'lucide-react'
import { useAuth } from '../contexts/AuthContext'
import toast from 'react-hot-toast'

const Jobs = () => {
  const { user } = useAuth()
  const [jobs, setJobs] = useState([])
  const [loading, setLoading] = useState(true)
  const [searchTerm, setSearchTerm] = useState('')
  const [filterCategory, setFilterCategory] = useState('')

  useEffect(() => {
    fetchJobs()
  }, [])

  const fetchJobs = async () => {
    try {
      setLoading(true)
      const response = await jobService.getJobs()
      setJobs(response.data || [])
    } catch (error) {
      toast.error('Failed to load jobs')
    } finally {
      setLoading(false)
    }
  }

  const filteredJobs = jobs.filter((job) => {
    const matchesSearch =
      job.title?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      job.description?.toLowerCase().includes(searchTerm.toLowerCase())
    const matchesCategory = !filterCategory || job.category_id == filterCategory
    return matchesSearch && matchesCategory
  })

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-azure-500"></div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 className="text-3xl font-bold text-gray-900 mb-2">Job Listings</h1>
          <p className="text-gray-600">Browse available job opportunities</p>
        </div>
        {user?.user_type === 'client' && (
          <Link to="/jobs/create" className="btn-primary inline-flex items-center space-x-2">
            <Plus size={20} />
            <span>Post a Job</span>
          </Link>
        )}
      </div>

      {/* Search and Filter */}
      <div className="card">
        <div className="flex flex-col md:flex-row gap-4">
          <div className="flex-1 relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={20} />
            <input
              type="text"
              placeholder="Search jobs..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="input-field pl-10"
            />
          </div>
          <div className="relative">
            <Filter className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={20} />
            <select
              value={filterCategory}
              onChange={(e) => setFilterCategory(e.target.value)}
              className="input-field pl-10 pr-8"
            >
              <option value="">All Categories</option>
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
        </div>
      </div>

      {/* Jobs Grid */}
      {filteredJobs.length > 0 ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredJobs.map((job) => (
            <JobCard key={job.job_id} job={job} />
          ))}
        </div>
      ) : (
        <div className="card text-center py-12">
          <p className="text-gray-500 text-lg">
            {searchTerm || filterCategory
              ? 'No jobs found matching your criteria.'
              : 'No jobs available at the moment.'}
          </p>
        </div>
      )}
    </div>
  )
}

export default Jobs

