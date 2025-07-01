<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import axios from 'axios'

const students = ref([])
const selected = ref([])
const selectAll = ref(false)
const loading = ref(false)
const loadingId = ref(null)

onMounted(async () => {
  const { data } = await axios.get('http://127.0.0.1:8000/api/students')
  students.value = data
})

const hasEvaluatedSelected = computed(() => {
  return selected.value.some(id => {
    const student = students.value.find(s => s.id === id)
    return student && student.evaluation_reason && student.evaluation_reason.trim() !== ''
  })
})

function toggleAll() {
  selected.value = selectAll.value ? students.value.map(s => s.id) : []
}

watch(selected, () => {
  selectAll.value = selected.value.length === students.value.length
})

async function evaluate(id) {
  loadingId.value = id
  try {
    const { data } = await axios.post(`http://127.0.0.1:8000/api/students/${id}/evaluate`)
    const idx = students.value.findIndex(s => s.id === id)
    if (idx !== -1) {
      students.value[idx].is_qualified = data.qualified
      students.value[idx].recommended_class = { name: data.recommended_class }
      students.value[idx].evaluation_reason = data.reason
    }
  } catch (error) {
    console.error('Evaluation failed', error)
  } finally {
    loadingId.value = null
  }
}

async function evaluateSelected() {
  loading.value = true
  try {
    const { data } = await axios.post('http://127.0.0.1:8000/api/students/evaluate-bulk', {
      ids: selected.value
    })
    data.results.forEach(updated => {
      const idx = students.value.findIndex(s => s.id === updated.student_id)
      if (idx !== -1) {
        students.value[idx].is_qualified = updated.qualified
        students.value[idx].recommended_class = { name: updated.recommended_class }
        students.value[idx].evaluation_reason = updated.reason
      }
    })
  } catch (error) {
    console.error('Bulk evaluation failed', error)
  } finally {
    loading.value = false
  }
}
</script>

<template >
  <div class="p-6 bg-white rounded-lg shadow-md w-10/12 ">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Student Evaluations</h2>

    <!-- Bulk Evaluate Button -->
    <div class="flex justify-between items-center mb-4">
      <div class="text-sm text-gray-500">
        Selected: {{ selected.length }} / {{ students.length }}
      </div>
      <button
        @click="evaluateSelected"
        :disabled="selected.length === 0 || hasEvaluatedSelected || loading"
        class="bg-blue-600 text-white px-4 py-2 rounded flex items-center gap-2 disabled:opacity-50"
      >
        <svg
          v-if="loading"
          class="animate-spin h-4 w-4 text-white"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
        </svg>
        <span>Evaluate Selected ({{ selected.length }})</span>
      </button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto relative">
      <!-- Full overlay loader -->
      <div v-if="loading" class="absolute inset-0 bg-white bg-opacity-70 flex items-center justify-center z-10">
        <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
        </svg>
      </div>

      <table class="w-full border-collapse">
        <thead class="bg-gray-50">
          <tr class="text-left text-gray-600 text-sm font-medium">
            <th class="px-6 py-3 border-b border-gray-200">
              <input type="checkbox" v-model="selectAll" @change="toggleAll" />
            </th>
            <th class="px-6 py-3 border-b border-gray-200">Name</th>
            <th class="px-6 py-3 border-b border-gray-200">Age</th>
            <th class="px-6 py-3 border-b border-gray-200">Skill</th>
            <th class="px-6 py-3 border-b border-gray-200">Score</th>
            <th class="px-6 py-3 border-b border-gray-200">Qualified</th>
            <th class="px-6 py-3 border-b border-gray-200">Class</th>

            <th class="px-6 py-3 border-b border-gray-200">Action</th>
          </tr>
        </thead>


        <tbody class="divide-y divide-gray-200">
        <template v-for="s in students" :key="s.id">
            <!-- Main row -->
            <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4">
                <input type="checkbox" v-model="selected" :value="s.id" />
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-medium">{{ s.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ s.age }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs rounded-full"
                :class="{
                    'bg-blue-100 text-blue-800': s.skill_level === 'Beginner',
                    'bg-purple-100 text-purple-800': s.skill_level === 'Intermediate',
                    'bg-green-100 text-green-800': s.skill_level === 'Advanced'
                }">
                {{ s.skill_level }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap font-mono">
                <span :class="{
                'text-red-600': s.math_score < 50,
                'text-yellow-600': s.math_score >= 50 && s.math_score < 80,
                'text-green-600': s.math_score >= 80
                }">
                {{ s.math_score }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span :class="s.is_qualified ? 'text-green-600' : 'text-red-600'">
                {{ s.is_qualified ? 'Yes' : 'No' }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                {{ s.recommended_class?.name || '-' }}
            </td>

            <td class="px-6 py-4 whitespace-nowrap">
                <button
                v-if="!selected.includes(s.id) && !s.evaluation_reason"
                @click="evaluate(s.id)"
                class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                Evaluate
                </button>
            </td>
            </tr>

            <!-- Reason row -->
            <tr v-if="s.evaluation_reason">
            <td colspan="9" class="bg-gray-100 text-gray-700 text-sm italic px-6 py-3">
                {{ s.evaluation_reason }}
            </td>
            </tr>
        </template>
        </tbody>



      </table>
    </div>
  </div>
</template>
