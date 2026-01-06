import { useState, useEffect } from 'react'
import { useAuth } from '../contexts/AuthContext'
import { uploadService } from '../services/uploadService'
import { User, Mail, Phone, Camera } from 'lucide-react'
import toast from 'react-hot-toast'

const Profile = () => {
  const { user } = useAuth()
  const [formData, setFormData] = useState({
    full_name: user?.full_name || '',
    email: user?.email || '',
    phone_number: user?.phone_number || '',
  })
  const [loading, setLoading] = useState(false)
  const [uploading, setUploading] = useState(false)

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    })
  }

  const handleFileUpload = async (e) => {
    const file = e.target.files[0]
    if (!file) return

    if (!file.type.startsWith('image/')) {
      toast.error('Please select an image file')
      return
    }

    try {
      setUploading(true)
      await uploadService.uploadProfilePicture(file, user.user_id)
      toast.success('Profile picture uploaded successfully')
      // Refresh user data
      window.location.reload()
    } catch (error) {
      toast.error('Failed to upload profile picture')
    } finally {
      setUploading(false)
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)

    try {
      // Update profile logic would go here
      toast.success('Profile updated successfully')
    } catch (error) {
      toast.error('Failed to update profile')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="max-w-4xl mx-auto space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-gray-900 mb-2">My Profile</h1>
        <p className="text-gray-600">Manage your account information</p>
      </div>

      <div className="card">
        <div className="flex flex-col md:flex-row gap-6 mb-8">
          <div className="flex flex-col items-center">
            <div className="relative">
              <div className="w-32 h-32 bg-azure-100 rounded-full flex items-center justify-center">
                {user?.profile_picture ? (
                  <img
                    src={user.profile_picture}
                    alt="Profile"
                    className="w-32 h-32 rounded-full object-cover"
                  />
                ) : (
                  <span className="text-4xl font-bold text-azure-600">
                    {user?.full_name?.charAt(0) || 'U'}
                  </span>
                )}
              </div>
              <label
                htmlFor="profile-upload"
                className="absolute bottom-0 right-0 bg-azure-500 text-white p-2 rounded-full cursor-pointer hover:bg-azure-600 transition-colors"
              >
                <Camera size={16} />
                <input
                  id="profile-upload"
                  type="file"
                  accept="image/*"
                  onChange={handleFileUpload}
                  className="hidden"
                  disabled={uploading}
                />
              </label>
            </div>
            {uploading && (
              <p className="text-sm text-gray-500 mt-2">Uploading...</p>
            )}
          </div>

          <div className="flex-1">
            <div className="mb-4">
              <p className="text-sm text-gray-500 mb-1">Account Type</p>
              <p className="font-semibold text-gray-900 capitalize">
                {user?.user_type?.replace('_', ' ') || 'Client'}
              </p>
            </div>
            <div className="mb-4">
              <p className="text-sm text-gray-500 mb-1">Member Since</p>
              <p className="font-semibold text-gray-900">
                {user?.created_at
                  ? new Date(user.created_at).toLocaleDateString()
                  : 'N/A'}
              </p>
            </div>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          <div>
            <label htmlFor="full_name" className="block text-sm font-medium text-gray-700 mb-2">
              <User size={16} className="inline mr-2" />
              Full Name
            </label>
            <input
              id="full_name"
              name="full_name"
              type="text"
              value={formData.full_name}
              onChange={handleChange}
              className="input-field"
            />
          </div>

          <div>
            <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
              <Mail size={16} className="inline mr-2" />
              Email Address
            </label>
            <input
              id="email"
              name="email"
              type="email"
              value={formData.email}
              onChange={handleChange}
              className="input-field"
              disabled
            />
            <p className="text-xs text-gray-500 mt-1">Email cannot be changed</p>
          </div>

          <div>
            <label htmlFor="phone_number" className="block text-sm font-medium text-gray-700 mb-2">
              <Phone size={16} className="inline mr-2" />
              Phone Number
            </label>
            <input
              id="phone_number"
              name="phone_number"
              type="tel"
              value={formData.phone_number}
              onChange={handleChange}
              className="input-field"
            />
          </div>

          <div className="flex space-x-4 pt-4">
            <button
              type="submit"
              disabled={loading}
              className="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {loading ? 'Saving...' : 'Save Changes'}
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}

export default Profile

