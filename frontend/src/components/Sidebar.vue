<template>
  <aside class="fixed top-0 left-0 h-screen bg-gray-800 text-white w-72 p-4 overflow-y-auto">
    <div class="mb-8">
      <h1 class="text-2xl font-bold">SL Test</h1>
    </div>
    
    <nav>
      <ul class="space-y-2">
        <li>
          <router-link 
            to="/dashboard" 
            class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors"
            :class="{ 'bg-gray-700': $route.path === '/dashboard' }"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Dashboard
          </router-link>
        </li>
        <li>
          <router-link 
            to="/employees" 
            class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors"
            :class="{ 'bg-gray-700': $route.path === '/employees' }"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Employees
          </router-link>
        </li>
      </ul>
    </nav>
  </aside>
</template>

<script setup lang="ts">
// Component logic can be added here if needed
</script> 