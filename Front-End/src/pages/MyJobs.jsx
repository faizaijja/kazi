import { useEffect, useState } from 'react'
import { jobService } from '../services/jobService'
import JobCard from '../components/JobCard'
import { useAuth } from '../contexts/AuthContext'
import toast from 'react-hot-toast'

const MyJobs = () => {
  const { user } = useAuth()
  const [jobs, setJobs] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchMyJobs()
  }, [])

  const fetchMyJobs = async () => {
    try {
      setLoading(true)
      const response = await jobService.getJobs({ client_id: user?.user_id })
      setJobs(response.data || [])
    } catch (error) {
      toast.error('Failed to load your jobs')
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

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-gray-900 mb-2">My Jobs</h1>
        <p className="text-gray-600">Manage your posted jobs</p>
      </div>

      {jobs.length > 0 ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {jobs.map((job) => (
            <JobCard key={job.job_id} job={job} />
          ))}
        </div>
      ) : (
        <div className="card text-center py-12">
          <p className="text-gray-500 text-lg">You haven't posted any jobs yet.</p>
        </div>
      )}
    </div>
  )
}

export default MyJobs

