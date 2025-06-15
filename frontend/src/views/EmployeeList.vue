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
    <div v-if="storeLoading.fetch" class="text-center py-4">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
      {{ error }}
    </div>

    <!-- Employee Table -->
    <div v-if="!storeLoading.fetch" class="bg-white shadow-md rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th 
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group"
              @click="handleSort('nip')"
            >
              <div class="flex items-center">
                NIP
                <span class="ml-1">
                  <svg 
                    v-if="sortField === 'nip'" 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4" 
                    :class="{ 'transform rotate-180': sortOrder === 'desc' }" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                  <svg 
                    v-else 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4 opacity-0 group-hover:opacity-100" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                </span>
              </div>
            </th>
            <th 
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group"
              @click="handleSort('nama_lengkap')"
            >
              <div class="flex items-center">
                Name
                <span class="ml-1">
                  <svg 
                    v-if="sortField === 'nama_lengkap'" 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4" 
                    :class="{ 'transform rotate-180': sortOrder === 'desc' }" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                  <svg 
                    v-else 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4 opacity-0 group-hover:opacity-100" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                </span>
              </div>
            </th>
            <th 
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group"
              @click="handleSort('email')"
            >
              <div class="flex items-center">
                Email
                <span class="ml-1">
                  <svg 
                    v-if="sortField === 'email'" 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4" 
                    :class="{ 'transform rotate-180': sortOrder === 'desc' }" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                  <svg 
                    v-else 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4 opacity-0 group-hover:opacity-100" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                </span>
              </div>
            </th>
            <th 
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group"
              @click="handleSort('no_telepon')"
            >
              <div class="flex items-center">
                Phone
                <span class="ml-1">
                  <svg 
                    v-if="sortField === 'no_telepon'" 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4" 
                    :class="{ 'transform rotate-180': sortOrder === 'desc' }" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                  <svg 
                    v-else 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4 opacity-0 group-hover:opacity-100" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                </span>
              </div>
            </th>
            <th 
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group"
              @click="handleSort('jabatan')"
            >
              <div class="flex items-center">
                Position
                <span class="ml-1">
                  <svg 
                    v-if="sortField === 'jabatan'" 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4" 
                    :class="{ 'transform rotate-180': sortOrder === 'desc' }" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                  <svg 
                    v-else 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4 opacity-0 group-hover:opacity-100" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                </span>
              </div>
            </th>
            <th 
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group"
              @click="handleSort('departemen')"
            >
              <div class="flex items-center">
                Department
                <span class="ml-1">
                  <svg 
                    v-if="sortField === 'departemen'" 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4" 
                    :class="{ 'transform rotate-180': sortOrder === 'desc' }" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                  <svg 
                    v-else 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4 opacity-0 group-hover:opacity-100" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                </span>
              </div>
            </th>
            <th 
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group"
              @click="handleSort('tanggal_masuk')"
            >
              <div class="flex items-center">
                Join Date
                <span class="ml-1">
                  <svg 
                    v-if="sortField === 'tanggal_masuk'" 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4" 
                    :class="{ 'transform rotate-180': sortOrder === 'desc' }" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                  <svg 
                    v-else 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4 opacity-0 group-hover:opacity-100" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                </span>
              </div>
            </th>
            <th 
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer group"
              @click="handleSort('status')"
            >
              <div class="flex items-center">
                Status
                <span class="ml-1">
                  <svg 
                    v-if="sortField === 'status'" 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4" 
                    :class="{ 'transform rotate-180': sortOrder === 'desc' }" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                  <svg 
                    v-else 
                    xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4 opacity-0 group-hover:opacity-100" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                </span>
              </div>
            </th>
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
    <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" @click.self="closeModal">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
            {{ isEditing ? 'Edit Employee' : 'Add Employee' }}
          </h3>
          <form @submit.prevent="handleSubmit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">NIP</label>
                <input
                  v-model="form.nip"
                  type="text"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                <input
                  v-model="form.nama_lengkap"
                  type="text"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input
                  v-model="form.no_telepon"
                  type="tel"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
              </div>
              <div>
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
              <div>
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
              <div>
                <label class="block text-sm font-medium text-gray-700">Join Date</label>
                <input
                  v-model="form.tanggal_masuk"
                  type="date"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Salary</label>
                <input
                  v-model="form.gaji"
                  type="number"
                  step="0.01"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
              </div>
              <div>
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
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Address</label>
                <textarea
                  v-model="form.alamat"
                  rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                ></textarea>
              </div>
            </div>
            <div class="flex justify-end space-x-3 mt-4">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-50"
                :disabled="storeLoading.create || storeLoading.update"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 flex items-center"
                :disabled="storeLoading.create || storeLoading.update"
              >
                <span v-if="storeLoading.create || storeLoading.update" class="mr-2">
                  <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                {{ isEditing ? 'Update' : 'Add' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" @click.self="closeDeleteModal">
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
              :disabled="storeLoading.delete"
            >
              Cancel
            </button>
            <button
              @click="handleDelete"
              class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 flex items-center"
              :disabled="storeLoading.delete"
            >
              <span v-if="storeLoading.delete" class="mr-2">
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed, onUnmounted } from 'vue'
import { useEmployeeStore } from '@/stores/employee'
import { storeToRefs } from 'pinia'

const store = useEmployeeStore()
const error = ref<string | null>(null)
const showModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const selectedEmployee = ref<any>(null)

// Add loading states from store
const { loading: storeLoading } = storeToRefs(store)

const filters = ref({
  search: '',
  departemen: '',
  status: '',
  jabatan: ''
})

// Add debounce timer ref
const searchTimeout = ref<number | null>(null)

// Add debounced search function
const debouncedSearch = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  searchTimeout.value = window.setTimeout(() => {
    store.fetchEmployees(1, pagination.value.limit, filters.value)
  }, 1000)
}

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

// Add these new refs for sorting
const sortField = ref('')
const sortOrder = ref<'asc' | 'desc'>('asc')

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
  try {
    await store.fetchEmployees(
      pagination.value.current_page, 
      pagination.value.limit, 
      {
        ...filters.value,
        sort_field: sortField.value,
        sort_order: sortOrder.value
      }
    )
    await store.fetchDepartments()
    await store.fetchPositionList()
  } catch (err: any) {
    error.value = err.message
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
watch(filters, (newFilters) => {
  if (newFilters.search !== undefined) {
    debouncedSearch()
  } else {
    store.fetchEmployees(1, pagination.value.limit, filters.value)
  }
}, { deep: true })

// Clean up timeout on component unmount
onUnmounted(() => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
})

// Lifecycle hooks
onMounted(() => {
  fetchData()
})

// Add the handleSort function
const handleSort = (field: string) => {
  if (sortField.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortOrder.value = 'asc'
  }
  fetchData()
}
</script> 