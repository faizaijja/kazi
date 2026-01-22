import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../contexts/AuthContext'
import { jobService } from '../services/jobService'
import { providerService } from '../services/providerService'
import { Briefcase, Users, TrendingUp, Clock } from 'lucide-react'
import JobCard from '../components/JobCard'
import ProviderCard from '../components/ProviderCard'
import toast from 'react-hot-toast'

const Dashboard = () => {
  const { user } = useAuth()

  const [stats, setStats] = useState({
    totalJobs: 0,
    totalProviders: 0,
    jobs: [],
    featuredProviders: [],
  })

  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchDashboardData()
  }, [])

  const fetchDashboardData = async () => {
    try {
      setLoading(true)

      const [jobsResponse, providersResponse] = await Promise.all([
        jobService.getJobs(),
        providerService.getProviders(),
      ])

      // ✅ THIS MATCHES YOUR jobs.php EXACTLY
      const jobs = jobsResponse.data?.data || []
      const providers = providersResponse.data?.data || []

      setStats({
        totalJobs: jobs.length,
        totalProviders: providers.length,
        jobs: jobs, // 👈 ALL jobs
        featuredProviders: providers.slice(0, 3),
      })
    } catch (error) {
      console.error(error)
      toast.error('Failed to load dashboard data')
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
    <div className="space-y-8">
      <div>
        <h1 className="text-3xl font-bold mb-2">
          Welcome, {user?.full_name}!
        </h1>
        <p className="text-gray-600">
          Here's what's happening on Kazi today.
        </p>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="card bg-gradient-to-br from-azure-500 to-azure-600 text-white">
          <p className="text-sm">Total Jobs</p>
          <p className="text-3xl font-bold">{stats.totalJobs}</p>
        </div>

        <div className="card bg-gradient-to-br from-green-500 to-green-600 text-white">
          <p className="text-sm">Providers</p>
          <p className="text-3xl font-bold">{stats.totalProviders}</p>
        </div>

        <div className="card bg-gradient-to-br from-purple-500 to-purple-600 text-white">
          <p className="text-sm">Active Jobs</p>
          <p className="text-3xl font-bold">{stats.totalJobs}</p>
        </div>

        <div className="card bg-gradient-to-br from-orange-500 to-orange-600 text-white">
          <p className="text-sm">Pending</p>
          <p className="text-3xl font-bold">0</p>
        </div>
      </div>

      {/* Quick Actions */}
      {user?.user_type === 'client' && (
        <div className="card">
          <h2 className="text-xl font-semibold mb-4">Quick Actions</h2>
          <Link to="/jobs/create" className="btn-primary">
            Post a New Job
          </Link>
        </div>
      )}

      {/* Jobs */}
      <div>
        <h2 className="text-2xl font-bold mb-6">Jobs</h2>

        {stats.jobs.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {stats.jobs.map((job) => (
              <JobCard key={job.job_id} job={job} />
            ))}
          </div>
        ) : (
          <div className="card text-center py-12">
            <p className="text-gray-500">No jobs available.</p>
          </div>
        )}
      </div>
    </div>
  )
}

export default Dashboard
