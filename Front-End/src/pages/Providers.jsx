import { useEffect, useState } from 'react'
import { providerService } from '../services/providerService'
import ProviderCard from '../components/ProviderCard'
import { Search, Filter } from 'lucide-react'
import toast from 'react-hot-toast'

const Providers = () => {
  const [providers, setProviders] = useState([])
  const [loading, setLoading] = useState(true)
  const [searchTerm, setSearchTerm] = useState('')
  const [filterCategory, setFilterCategory] = useState('')
  const [filterAvailability, setFilterAvailability] = useState('')

  useEffect(() => {
    fetchProviders()
  }, [])

  const fetchProviders = async () => {
    try {
      setLoading(true)
      const response = await providerService.getProviders()
      setProviders(response.data || [])
    } catch (error) {
      toast.error('Failed to load providers')
    } finally {
      setLoading(false)
    }
  }

  const filteredProviders = providers.filter((provider) => {
    const matchesSearch =
      provider.business_name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
      provider.bio?.toLowerCase().includes(searchTerm.toLowerCase())
    const matchesCategory = !filterCategory || provider.category_id == filterCategory
    const matchesAvailability =
      !filterAvailability || provider.availability_status === filterAvailability
    return matchesSearch && matchesCategory && matchesAvailability
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
      <div>
        <h1 className="text-3xl font-bold text-gray-900 mb-2">Service Providers</h1>
        <p className="text-gray-600">Find trusted professionals for your needs</p>
      </div>

      {/* Search and Filter */}
      <div className="card">
        <div className="flex flex-col md:flex-row gap-4">
          <div className="flex-1 relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={20} />
            <input
              type="text"
              placeholder="Search providers..."
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
          <div className="relative">
            <select
              value={filterAvailability}
              onChange={(e) => setFilterAvailability(e.target.value)}
              className="input-field pr-8"
            >
              <option value="">All Status</option>
              <option value="available">Available</option>
              <option value="busy">Busy</option>
              <option value="unavailable">Unavailable</option>
            </select>
          </div>
        </div>
      </div>

      {/* Providers Grid */}
      {filteredProviders.length > 0 ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredProviders.map((provider) => (
            <ProviderCard key={provider.provider_id} provider={provider} />
          ))}
        </div>
      ) : (
        <div className="card text-center py-12">
          <p className="text-gray-500 text-lg">
            {searchTerm || filterCategory || filterAvailability
              ? 'No providers found matching your criteria.'
              : 'No providers available at the moment.'}
          </p>
        </div>
      )}
    </div>
  )
}

export default Providers

