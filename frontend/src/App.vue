<script setup>
import { computed, onMounted, reactive, ref } from 'vue'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL?.trim() || '/api'

const categories = ref([])
const loading = ref(false)
const saving = ref(false)
const error = ref('')
const notice = ref('')
const editingId = ref(null)

const form = reactive({
  name: '',
  description: '',
  is_active: false,
})

const sortedCategories = computed(() => {
  return [...categories.value].sort((a, b) => Number(b.id) - Number(a.id))
})

const formTitle = computed(() => (editingId.value ? 'Edit category' : 'Create category'))
const submitText = computed(() => (editingId.value ? 'Update' : 'Create'))

function resetForm() {
  editingId.value = null
  form.name = ''
  form.description = ''
  form.is_active = false
}

function fillForm(category) {
  editingId.value = category.id
  form.name = category.name || ''
  form.description = category.description || ''
  form.is_active = Boolean(category.is_active)
  notice.value = ''
  error.value = ''
}

function formatDate(value) {
  if (!value) return 'No date'

  return new Intl.DateTimeFormat('en', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))
}

async function apiRequest(path, options = {}) {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...options.headers,
    },
    ...options,
  })

  const data = await response.json().catch(() => null)

  if (!response.ok) {
    const message = data?.message || `Request failed with status ${response.status}`
    throw new Error(message)
  }

  return data
}

async function loadCategories() {
  loading.value = true
  error.value = ''

  try {
    const response = await apiRequest('/categories')
    categories.value = response.data || []
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

async function submitCategory() {
  saving.value = true
  error.value = ''
  notice.value = ''

  const payload = {
    name: form.name,
    description: form.description,
    is_active: form.is_active,
  }

  try {
    if (editingId.value) {
      await apiRequest(`/categories/${editingId.value}`, {
        method: 'PUT',
        body: JSON.stringify(payload),
      })
      notice.value = 'Category updated.'
    } else {
      await apiRequest('/categories', {
        method: 'POST',
        body: JSON.stringify(payload),
      })
      notice.value = 'Category created.'
    }

    resetForm()
    await loadCategories()
  } catch (err) {
    error.value = err.message
  } finally {
    saving.value = false
  }
}

async function deleteCategory(category) {
  const confirmed = window.confirm(`Delete "${category.name}"?`)
  if (!confirmed) return

  error.value = ''
  notice.value = ''

  try {
    await apiRequest(`/categories/${category.id}`, {
      method: 'DELETE',
    })
    notice.value = 'Category deleted.'

    if (editingId.value === category.id) {
      resetForm()
    }

    await loadCategories()
  } catch (err) {
    error.value = err.message
  }
}

onMounted(loadCategories)
</script>

<template>
  <main class="page-shell">
    <section class="toolbar">
      <div>
        <p class="eyebrow">Laravel API</p>
        <h1>Categories</h1>
      </div>

      <button class="icon-button" type="button" :disabled="loading" @click="loadCategories" title="Refresh">
        <span aria-hidden="true">↻</span>
      </button>
    </section>

    <section class="content-grid">
      <form class="panel form-panel" @submit.prevent="submitCategory">
        <div class="panel-header">
          <h2>{{ formTitle }}</h2>
          <button v-if="editingId" class="text-button" type="button" @click="resetForm">Cancel</button>
        </div>

        <label>
          <span>Name</span>
          <input v-model.trim="form.name" required type="text" placeholder="Fruit" />
        </label>

        <label>
          <span>Description</span>
          <textarea v-model.trim="form.description" rows="4" placeholder="Add category detail"></textarea>
        </label>

        <label class="check-row">
          <input v-model="form.is_active" type="checkbox" />
          <span>Active</span>
        </label>

        <button class="primary-button" type="submit" :disabled="saving">
          {{ saving ? 'Saving...' : submitText }}
        </button>

        <p v-if="error" class="message error">{{ error }}</p>
        <p v-if="notice" class="message success">{{ notice }}</p>
      </form>

      <section class="panel list-panel">
        <div class="panel-header">
          <h2>All categories</h2>
          <span class="count">{{ categories.length }}</span>
        </div>

        <div v-if="loading" class="empty-state">Loading categories...</div>

        <div v-else-if="!sortedCategories.length" class="empty-state">No categories found.</div>

        <div v-else class="category-list">
          <article v-for="category in sortedCategories" :key="category.id" class="category-card">
            <div class="category-main">
              <div>
                <h3>{{ category.name }}</h3>
                <p>{{ category.description || 'No description' }}</p>
              </div>
              <span :class="['status', category.is_active ? 'active' : 'inactive']">
                {{ category.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>

            <dl class="meta-grid">
              <div>
                <dt>ID</dt>
                <dd>{{ category.id }}</dd>
              </div>
              <div>
                <dt>Updated</dt>
                <dd>{{ formatDate(category.updated_at) }}</dd>
              </div>
            </dl>

            <div class="card-actions">
              <button type="button" @click="fillForm(category)">Edit</button>
              <button class="danger-button" type="button" @click="deleteCategory(category)">Delete</button>
            </div>
          </article>
        </div>
      </section>
    </section>
  </main>
</template>
