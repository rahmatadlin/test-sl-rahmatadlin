import { defineStore } from 'pinia'
import axios from '@/plugins/axios'

interface Employee {
  id: number
  nip: string
  nama_lengkap: string
  email: string
  no_telepon: string
  jabatan: string
  departemen: string
  tanggal_masuk: string
  gaji: number
  status: 'aktif' | 'non_aktif' | 'cuti'
  alamat: string
  created_at: string
  updated_at: string
}

interface EmployeeState {
  employees: Employee[]
  departments: string[]
  jabatanList: string[]
  statistics: any
  pagination: {
    current_page: number
    total_pages: number
    total_items: number
    limit: number
  }
}

export const useEmployeeStore = defineStore('employee', {
  state: (): EmployeeState => ({
    employees: [],
    departments: [],
    jabatanList: [],
    statistics: null,
    pagination: {
      current_page: 1,
      total_pages: 1,
      total_items: 0,
      limit: 10
    }
  }),

  actions: {
    async fetchEmployees(page = 1, limit = 10, filters = {}) {
      try {
        const response = await axios.get(`/employees`, {
          params: {
            page,
            limit,
            ...filters
          }
        })
        const data = response.data
        this.employees = data.data
        this.pagination = {
          current_page: data.pagination.current_page,
          total_pages: data.pagination.total_pages,
          total_items: data.pagination.total,
          limit: data.pagination.per_page
        }
      } catch (error) {
        console.error('Error fetching employees:', error)
        throw error
      }
    },

    async fetchDepartments() {
      try {
        const response = await axios.get('/departments')
        this.departments = response.data.data
      } catch (error) {
        console.error('Error fetching departments:', error)
        throw error
      }
    },

    async fetchPositionList() {
      try {
        const response = await axios.get('/positions')
        this.jabatanList = response.data.data
      } catch (error) {
        console.error('Error fetching jabatan list:', error)
        throw error
      }
    },

    async fetchStatistics() {
      try {
        const response = await axios.get('/statistics')
        this.statistics = response.data.data
      } catch (error) {
        console.error('Error fetching statistics:', error)
        throw error
      }
    },

    async createEmployee(employeeData: Partial<Employee>) {
      try {
        const response = await axios.post('/employees', employeeData)
        return response.data
      } catch (error) {
        console.error('Error creating employee:', error)
        throw error
      }
    },

    async updateEmployee(id: number, employeeData: Partial<Employee>) {
      try {
        const response = await axios.put(`/employees/${id}`, employeeData)
        return response.data
      } catch (error) {
        console.error('Error updating employee:', error)
        throw error
      }
    },

    async deleteEmployee(id: number) {
      try {
        const response = await axios.delete(`/employees/${id}`)
        return response.data
      } catch (error) {
        console.error('Error deleting employee:', error)
        throw error
      }
    }
  }
}) 