import { defineStore } from 'pinia'
import axios from '@/plugins/axios'
import { useNotificationStore } from './notification'

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
  loading: {
    create: boolean
    update: boolean
    delete: boolean
    fetch: boolean
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
    },
    loading: {
      create: false,
      update: false,
      delete: false,
      fetch: false
    }
  }),

  actions: {
    async fetchEmployees(page = 1, limit = 10, filters = {}) {
      this.loading.fetch = true
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
        const notificationStore = useNotificationStore()
        notificationStore.addNotification('Failed to fetch employees', 'error')
        throw error
      } finally {
        this.loading.fetch = false
      }
    },

    async fetchDepartments() {
      try {
        const response = await axios.get('/departments')
        this.departments = response.data.data
      } catch (error) {
        console.error('Error fetching departments:', error)
        const notificationStore = useNotificationStore()
        notificationStore.addNotification('Failed to fetch departments', 'error')
        throw error
      }
    },

    async fetchPositionList() {
      try {
        const response = await axios.get('/positions')
        this.jabatanList = response.data.data
      } catch (error) {
        console.error('Error fetching positions:', error)
        const notificationStore = useNotificationStore()
        notificationStore.addNotification('Failed to fetch positions', 'error')
        throw error
      }
    },

    async fetchStatistics() {
      try {
        const response = await axios.get('/statistics')
        this.statistics = response.data.data
      } catch (error) {
        console.error('Error fetching statistics:', error)
        const notificationStore = useNotificationStore()
        notificationStore.addNotification('Failed to fetch statistics', 'error')
        throw error
      }
    },

    async createEmployee(employeeData: Omit<Employee, 'id' | 'created_at' | 'updated_at'>) {
      this.loading.create = true
      try {
        const response = await axios.post('/employees', employeeData)
        await new Promise(resolve => setTimeout(resolve, 1000)) // Add 3 second delay
        const notificationStore = useNotificationStore()
        notificationStore.addNotification('Employee created successfully')
        return response.data.data
      } catch (error) {
        console.error('Error creating employee:', error)
        const notificationStore = useNotificationStore()
        notificationStore.addNotification('Failed to create employee', 'error')
        throw error
      } finally {
        this.loading.create = false
      }
    },

    async updateEmployee(id: number, employeeData: Partial<Employee>) {
      this.loading.update = true
      try {
        const response = await axios.put(`/employees/${id}`, employeeData)
        await new Promise(resolve => setTimeout(resolve, 3000)) 
        const notificationStore = useNotificationStore()
        notificationStore.addNotification('Employee updated successfully')
        return response.data.data
      } catch (error) {
        console.error('Error updating employee:', error)
        const notificationStore = useNotificationStore()
        notificationStore.addNotification('Failed to update employee', 'error')
        throw error
      } finally {
        this.loading.update = false
      }
    },

    async deleteEmployee(id: number) {
      this.loading.delete = true
      try {
        await axios.delete(`/employees/${id}`)
        await new Promise(resolve => setTimeout(resolve, 1000))
        const notificationStore = useNotificationStore()
        notificationStore.addNotification('Employee deleted successfully')
      } catch (error) {
        console.error('Error deleting employee:', error)
        const notificationStore = useNotificationStore()
        notificationStore.addNotification('Failed to delete employee', 'error')
        throw error
      } finally {
        this.loading.delete = false
      }
    }
  }
}) 