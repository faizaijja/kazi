const API_URL = "http://localhost/Kazi";

export const authService = {
  register: async (userData) => {
    try {
      const response = await fetch(`${API_URL}/signup.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(userData),
      });

      const data = await response.json();
      return data; // { success: true/false, message, field? }
      
    } catch (error) {
      console.error('Registration error:', error);
      return {
        success: false,
        message: "Network error. Please check your connection.",
      };
    }
  },

  login: async (email, password) => {
    try {
      const response = await fetch(`${API_URL}/login.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ email, password }),
      });

      const data = await response.json();
      return data;
      
    } catch (error) {
      console.error('Login error:', error);
      return {
        success: false,
        message: "Network error. Please check your connection.",
      };
    }
  },
};