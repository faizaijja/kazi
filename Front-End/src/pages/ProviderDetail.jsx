import { useEffect, useState } from 'react'
import { useParams, Link, useNavigate } from 'react-router-dom'
import { providerService } from '../services/providerService'
import { Star, MapPin, CheckCircle, Clock, DollarSign, Award } from 'lucide-react'
import toast from 'react-hot-toast'

const ProviderDetail = () => {
  const { id } = useParams()
  const navigate = useNavigate()
  const [provider, setProvider] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    fetchProvider()
  }, [id])

  const fetchProvider = async () => {
    try {
      setLoading(true)
      const response = await providerService.getProviderById(id)
      if (response.success) {
        setProvider(response.data)
      } else {
        toast.error(response.message || 'Provider not found')
        navigate('/providers')
      }
    } catch (error) {
      toast.error('Failed to load provider details')
      navigate('/providers')
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

  if (!provider) {
    return null
  }

  return (
    <div className="space-y-6">
      <Link
        to="/providers"
        className="inline-flex items-center space-x-2 text-azure-500 hover:text-azure-600 font-semibold"
      >
        <span>← Back to Providers</span>
      </Link>

      <div className="card">
        <div className="flex flex-col md:flex-row gap-6 mb-6">
          <div className="w-24 h-24 bg-azure-100 rounded-full flex items-center justify-center flex-shrink-0">
            <span className="text-4xl font-bold text-azure-600">
              {provider.business_name?.charAt(0) || 'P'}
            </span>
          </div>
          <div className="flex-1">
            <div className="flex items-start justify-between mb-2">
              <div>
                <h1 className="text-3xl font-bold text-gray-900 mb-2">
                  {provider.business_name}
                </h1>
                {provider.verification_status === 'verified' && (
                  <div className="flex items-center space-x-2 text-azure-600 mb-2">
                    <CheckCircle size={18} />
                    <span className="font-semibold">Verified Provider</span>
                  </div>
                )}
              </div>
              <div className="flex items-center space-x-2">
                <Star size={24} className="text-yellow-400 fill-yellow-400" />
                <span className="text-2xl font-bold text-gray-900">
                  {provider.rating_average?.toFixed(1) || 'N/A'}
                </span>
              </div>
            </div>
            <p className="text-gray-700 text-lg">{provider.bio}</p>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6 pt-6 border-t border-gray-200">
          <div className="flex items-start space-x-3">
            <Award className="text-azure-500 mt-1" size={20} />
            <div>
              <p className="text-sm text-gray-500">Experience</p>
              <p className="font-semibold text-gray-900">
                {provider.years_of_experience || 0} years
              </p>
            </div>
          </div>

          <div className="flex items-start space-x-3">
            <DollarSign className="text-azure-500 mt-1" size={20} />
            <div>
              <p className="text-sm text-gray-500">Hourly Rate</p>
              <p className="font-semibold text-gray-900">
                {provider.hourly_rate?.toLocaleString() || 'N/A'} RWF/hr
              </p>
            </div>
          </div>

          <div className="flex items-start space-x-3">
            <Clock className="text-azure-500 mt-1" size={20} />
            <div>
              <p className="text-sm text-gray-500">Status</p>
              <p
                className={`font-semibold capitalize ${
                  provider.availability_status === 'available'
                    ? 'text-green-600'
                    : 'text-gray-600'
                }`}
              >
                {provider.availability_status || 'Unknown'}
              </p>
            </div>
          </div>

          <div className="flex items-start space-x-3">
            <CheckCircle className="text-azure-500 mt-1" size={20} />
            <div>
              <p className="text-sm text-gray-500">Jobs Completed</p>
              <p className="font-semibold text-gray-900">
                {provider.total_jobs_completed || 0}
              </p>
            </div>
          </div>
        </div>

        <div className="pt-6 border-t border-gray-200">
          <button className="btn-primary">
            Contact Provider
          </button>
        </div>
      </div>
    </div>
  )
}

export default ProviderDetail

