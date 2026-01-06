import { Link } from 'react-router-dom'
import { MapPin, Calendar, DollarSign, Clock } from 'lucide-react'

const JobCard = ({ job }) => {
  const urgencyColors = {
    low: 'bg-green-100 text-green-800',
    medium: 'bg-yellow-100 text-yellow-800',
    high: 'bg-orange-100 text-orange-800',
    emergency: 'bg-red-100 text-red-800',
  }

  return (
    <Link to={`/jobs/${job.job_id}`}>
      <div className="card hover:shadow-lg transition-shadow duration-200 cursor-pointer">
        <div className="flex justify-between items-start mb-4">
          <div className="flex-1">
            <h3 className="text-xl font-semibold text-gray-900 mb-2">
              {job.title}
            </h3>
            <p className="text-gray-600 text-sm line-clamp-2">
              {job.description}
            </p>
          </div>
          <span
            className={`px-3 py-1 rounded-full text-xs font-semibold ${
              urgencyColors[job.urgency] || urgencyColors.medium
            }`}
          >
            {job.urgency?.toUpperCase()}
          </span>
        </div>

        <div className="flex flex-wrap gap-4 text-sm text-gray-600 mb-4">
          {job.category_name && (
            <span className="px-3 py-1 bg-azure-50 text-azure-700 rounded-full">
              {job.category_name}
            </span>
          )}
          {job.budget_min && job.budget_max && (
            <div className="flex items-center space-x-1">
              <DollarSign size={16} />
              <span>
                {job.budget_min.toLocaleString()} - {job.budget_max.toLocaleString()} RWF
              </span>
            </div>
          )}
        </div>

        <div className="flex items-center justify-between text-sm text-gray-500 pt-4 border-t border-gray-200">
          <div className="flex items-center space-x-1">
            <Calendar size={16} />
            <span>
              {job.preferred_date
                ? new Date(job.preferred_date).toLocaleDateString()
                : 'Not specified'}
            </span>
          </div>
          {job.client_name && (
            <span className="text-gray-700">Posted by {job.client_name}</span>
          )}
        </div>
      </div>
    </Link>
  )
}

export default JobCard

