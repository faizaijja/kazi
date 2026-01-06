import { Link } from 'react-router-dom'
import { Star, MapPin, CheckCircle } from 'lucide-react'

const ProviderCard = ({ provider }) => {
  return (
    <Link to={`/providers/${provider.provider_id}`}>
      <div className="card hover:shadow-lg transition-shadow duration-200 cursor-pointer">
        <div className="flex items-start space-x-4">
          <div className="w-16 h-16 bg-azure-100 rounded-full flex items-center justify-center flex-shrink-0">
            <span className="text-2xl font-bold text-azure-600">
              {provider.business_name?.charAt(0) || 'P'}
            </span>
          </div>
          <div className="flex-1 min-w-0">
            <div className="flex items-start justify-between mb-2">
              <div>
                <h3 className="text-lg font-semibold text-gray-900">
                  {provider.business_name}
                </h3>
                {provider.verification_status === 'verified' && (
                  <div className="flex items-center space-x-1 text-azure-600 mt-1">
                    <CheckCircle size={14} />
                    <span className="text-xs font-medium">Verified</span>
                  </div>
                )}
              </div>
              <div className="flex items-center space-x-1">
                <Star size={16} className="text-yellow-400 fill-yellow-400" />
                <span className="font-semibold text-gray-900">
                  {provider.rating_average?.toFixed(1) || 'N/A'}
                </span>
              </div>
            </div>

            <p className="text-sm text-gray-600 line-clamp-2 mb-3">
              {provider.bio}
            </p>

            <div className="flex flex-wrap gap-2 text-xs text-gray-500">
              <span>
                {provider.years_of_experience || 0} years experience
              </span>
              <span>•</span>
              <span>
                {provider.hourly_rate?.toLocaleString() || 'N/A'} RWF/hr
              </span>
              <span>•</span>
              <span
                className={`px-2 py-1 rounded-full ${
                  provider.availability_status === 'available'
                    ? 'bg-green-100 text-green-800'
                    : 'bg-gray-100 text-gray-800'
                }`}
              >
                {provider.availability_status || 'Unknown'}
              </span>
            </div>
          </div>
        </div>
      </div>
    </Link>
  )
}

export default ProviderCard

