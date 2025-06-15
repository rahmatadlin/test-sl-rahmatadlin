<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Employee Management</h1>
      <button
        @click="openCreateModal"
        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
      >
        Add Employee
      </button>
    </div>

    <!-- Dashboard Chart -->
    <div class="mb-8">
      <DashboardChart />
    </div>

    <!-- Filters -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Search</label>
        <input
          v-model="filters.search"
          type="text"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          placeholder="Search by name, NIP, or email"
        />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Department</label>
        <select
          v-model="filters.departemen"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="">All Departments</option>
          <option v-for="dept in departments" :key="dept" :value="dept">
            {{ dept }}
          </option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Status</label>
        <select
          v-model="filters.status"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="">All Status</option>
          <option value="aktif">Aktif</option>
          <option value="non_aktif">Non Aktif</option>
          <option value="cuti">Cuti</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Jabatan</label>
        <select
          v-model="filters.jabatan"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="">All Positions</option>
          <option v-for="jabatan in jabatanList" :key="jabatan" :value="jabatan">
            {{ jabatan }}
          </option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-4">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      {{ error }}
    </div>

    <!-- Employee Table -->
    <div v-if="!loading" class="bg-white shadow-md rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Join Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="employee in employees" :key="employee.id">
            <td class="px-6 py-4 whitespace-nowrap">{{ employee.nip }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ employee.nama_lengkap }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ employee.email }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ employee.no_telepon }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ employee.jabatan }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ employee.departemen }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(employee.tanggal_masuk) }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="{
                  'px-2 inline-flex text-xs leading-5 font-semibold rounded-full': true,
                  'bg-green-100 text-green-800': employee.status === 'aktif',
                  'bg-red-100 text-red-800': employee.status === 'non_aktif',
                  'bg-yellow-100 text-yellow-800': employee.status === 'cuti'
                }"
              >
                {{ employee.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button
                @click="openEditModal(employee)"
                class="text-blue-600 hover:text-blue-900 mr-3"
              >
                Edit
              </button>
              <button
                @click="confirmDelete(employee)"
                class="text-red-600 hover:text-red-900"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex justify-between items-center">
      <div class="text-sm text-gray-700">
        Showing {{ pagination.total_items > 0 ? (pagination.current_page - 1) * pagination.limit + 1 : 0 }}
        to {{ Math.min(pagination.current_page * pagination.limit, pagination.total_items) }}
        of {{ pagination.total_items }} entries
      </div>
      <div class="flex space-x-2">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="px-3 py-1 rounded border"
          :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === 1 }"
        >
          Previous
        </button>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.total_pages"
          class="px-3 py-1 rounded border"
          :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === pagination.total_pages }"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
            {{ isEditing ? 'Edit Employee' : 'Add Employee' }}
          </h3>
          <form @submit.prevent="handleSubmit">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">NIP</label>
              <input
                v-model="form.nip"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Full Name</label>
              <input
                v-model="form.nama_lengkap"
                type="text"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Email</label>
              <input
                v-model="form.email"
                type="email"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Phone Number</label>
              <input
                v-model="form.no_telepon"
                type="tel"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Position</label>
              <select
                v-model="form.jabatan"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
                <option v-for="jabatan in jabatanList" :key="jabatan" :value="jabatan">
                  {{ jabatan }}
                </option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Department</label>
              <select
                v-model="form.departemen"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
                <option v-for="dept in departments" :key="dept" :value="dept">
                  {{ dept }}
                </option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Join Date</label>
              <input
                v-model="form.tanggal_masuk"
                type="date"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Salary</label>
              <input
                v-model="form.gaji"
                type="number"
                step="0.01"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Status</label>
              <select
                v-model="form.status"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
                <option value="aktif">Aktif</option>
                <option value="non_aktif">Non Aktif</option>
                <option value="cuti">Cuti</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Address</label>
              <textarea
                v-model="form.alamat"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              ></textarea>
            </div>
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
              >
                {{ isEditing ? 'Update' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Confirm Delete</h3>
          <p class="text-sm text-gray-500 mb-4">
            Are you sure you want to delete this employee? This action cannot be undone.
          </p>
          <div class="flex justify-end space-x-3">
            <button
              @click="closeDeleteModal"
              class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="handleDelete"
              class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { useEmployeeStore } from '@/stores/employee'
import DashboardChart from '@/components/DashboardChart.vue'

const store = useEmployeeStore()
const loading = ref(false)
const error = ref<string | null>(null)
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const selectedEmployee = ref<any>(null)

const filters = ref({
  search: '',
  departemen: '',
  status: '',
  jabatan: ''
})

const form = ref({
  nip: '',
  nama_lengkap: '',
  email: '',
  no_telepon: '',
  jabatan: '',
  departemen: '',
  tanggal_masuk: '',
  gaji: '',
  status: 'aktif' as 'aktif' | 'non_aktif' | 'cuti',
  alamat: ''
})

// Computed properties
const employees = computed(() => store.employees)
const departments = computed(() => store.departments)
const jabatanList = computed(() => store.jabatanList)
const pagination = computed(() => store.pagination)

// Helper function to format date
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Methods
const fetchData = async () => {
  loading.value = true
  try {
    await store.fetchEmployees(pagination.value.current_page, pagination.value.limit, filters.value)
    await store.fetchDepartments()
    await store.fetchPositionList()
  } catch (err: any) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.total_pages) {
    store.fetchEmployees(page, pagination.value.limit, filters.value)
  }
}

const openCreateModal = () => {
  isEditing.value = false
  form.value = {
    nip: '',
    nama_lengkap: '',
    email: '',
    no_telepon: '',
    jabatan: '',
    departemen: '',
    tanggal_masuk: '',
    gaji: '',
    status: 'aktif',
    alamat: ''
  }
  showModal.value = true
}

const openEditModal = (employee: any) => {
  isEditing.value = true
  selectedEmployee.value = employee
  form.value = { ...employee }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  form.value = {
    nip: '',
    nama_lengkap: '',
    email: '',
    no_telepon: '',
    jabatan: '',
    departemen: '',
    tanggal_masuk: '',
    gaji: '',
    status: 'aktif',
    alamat: ''
  }
}

const handleSubmit = async () => {
  try {
    const formData = {
      ...form.value,
      gaji: parseFloat(form.value.gaji)
    }
    
    if (isEditing.value) {
      await store.updateEmployee(selectedEmployee.value.id, formData)
    } else {
      await store.createEmployee(formData)
    }
    closeModal()
    fetchData()
  } catch (err: any) {
    error.value = err.message
  }
}

const confirmDelete = (employee: any) => {
  selectedEmployee.value = employee
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  selectedEmployee.value = null
}

const handleDelete = async () => {
  try {
    await store.deleteEmployee(selectedEmployee.value.id)
    closeDeleteModal()
    fetchData()
  } catch (err: any) {
    error.value = err.message
  }
}

// Watch for filter changes
watch(filters, () => {
  store.fetchEmployees(1, pagination.value.limit, filters.value)
}, { deep: true })

// Lifecycle hooks
onMounted(() => {
  fetchData()
})
</script> 