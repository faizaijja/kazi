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
    recentJobs: [],
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

      const jobs = jobsResponse.data || []
      const providers = providersResponse.data || []

      setStats({
        totalJobs: jobs.length,
        totalProviders: providers.length,
        recentJobs: jobs.slice(0, 3),
        featuredProviders: providers.slice(0, 3),
      })
    } catch (error) {
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
        <h1 className="text-3xl font-bold text-gray-900 mb-2">
          Welcome back, {user?.full_name}!
        </h1>
        <p className="text-gray-600">
          Here's what's happening on Kazi today.
        </p>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="card bg-gradient-to-br from-azure-500 to-azure-600 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-azure-100 text-sm font-medium">Total Jobs</p>
              <p className="text-3xl font-bold mt-2">{stats.totalJobs}</p>
            </div>
            <Briefcase size={40} className="opacity-80" />
          </div>
        </div>

        <div className="card bg-gradient-to-br from-green-500 to-green-600 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-green-100 text-sm font-medium">Providers</p>
              <p className="text-3xl font-bold mt-2">{stats.totalProviders}</p>
            </div>
            <Users size={40} className="opacity-80" />
          </div>
        </div>

        <div className="card bg-gradient-to-br from-purple-500 to-purple-600 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-purple-100 text-sm font-medium">Active Jobs</p>
              <p className="text-3xl font-bold mt-2">{stats.recentJobs.length}</p>
            </div>
            <TrendingUp size={40} className="opacity-80" />
          </div>
        </div>

        <div className="card bg-gradient-to-br from-orange-500 to-orange-600 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-orange-100 text-sm font-medium">Pending</p>
              <p className="text-3xl font-bold mt-2">0</p>
            </div>
            <Clock size={40} className="opacity-80" />
          </div>
        </div>
      </div>

      {/* Quick Actions */}
      {user?.user_type === 'client' && (
        <div className="card">
          <h2 className="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
          <Link to="/jobs/create" className="btn-primary inline-block">
            Post a New Job
          </Link>
        </div>
      )}

      {/* Recent Jobs */}
      <div>
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-2xl font-bold text-gray-900">Recent Jobs</h2>
          <Link
            to="/jobs"
            className="text-azure-500 hover:text-azure-600 font-semibold text-sm"
          >
            View All →
          </Link>
        </div>
        {stats.recentJobs.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {stats.recentJobs.map((job) => (
              <JobCard key={job.job_id} job={job} />
            ))}
          </div>
        ) : (
          <div className="card text-center py-12">
            <p className="text-gray-500">No jobs available at the moment.</p>
          </div>
        )}
      </div>

      {/* Featured Providers */}
      <div>
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-2xl font-bold text-gray-900">Featured Providers</h2>
          <Link
            to="/providers"
            className="text-azure-500 hover:text-azure-600 font-semibold text-sm"
          >
            View All →
          </Link>
        </div>
        {stats.featuredProviders.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {stats.featuredProviders.map((provider) => (
              <ProviderCard key={provider.provider_id} provider={provider} />
            ))}
          </div>
        ) : (
          <div className="card text-center py-12">
            <p className="text-gray-500">No providers available at the moment.</p>
          </div>
        )}
      </div>
    </div>
  )
}

export default Dashboard

