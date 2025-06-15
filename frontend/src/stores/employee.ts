import { defineStore } from 'pinia'
import axios from '@/plugins/axios'

interface Employee {
  id: number
  name: string
  email: string
  position: string
  department: string
  status: string
  // Add other fields as needed
}

interface Pagination {
  current_page: number
  total_pages: number
  total_items: number
  limit: number
}

interface EmployeeState {
  employees: Employee[]
  currentEmployee: Employee | null
  loading: boolean
  error: string | null
  pagination: Pagination
  departments: string[]
}

export const useEmployeeStore = defineStore('employee', {
  state: (): EmployeeState => ({
    employees: [],
    currentEmployee: null,
    loading: false,
    error: null,
    pagination: {
      current_page: 1,
      total_pages: 1,
      total_items: 0,
      limit: 10
    },
    departments: []
  }),

  actions: {
    async fetchEmployees(page = 1, limit = 10, filters = {}) {
      this.loading = true
      this.error = null
      try {
        const params = new URLSearchParams({
          page: page.toString(),
          limit: limit.toString(),
          ...filters
        })
        const response = await axios.get(`/employees?${params}`)
        this.employees = response.data.data
        this.pagination = response.data.pagination
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch employees'
      } finally {
        this.loading = false
      }
    },

    async fetchEmployee(id: number) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get(`/employees/${id}`)
        this.currentEmployee = response.data.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch employee'
      } finally {
        this.loading = false
      }
    },

    async createEmployee(employeeData: Partial<Employee>) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.post('/employees', employeeData)
        return response.data.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to create employee'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateEmployee(id: number, employeeData: Partial<Employee>) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.put(`/employees/${id}`, employeeData)
        return response.data.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to update employee'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteEmployee(id: number) {
      this.loading = true
      this.error = null
      try {
        await axios.delete(`/employees/${id}`)
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to delete employee'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchDepartments() {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get('/departments')
        this.departments = response.data.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch departments'
      } finally {
        this.loading = false
      }
    }
  }
}) 