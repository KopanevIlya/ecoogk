<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'

const props = defineProps({
  rows: Array,
  filters: Object,
  companies: Array,
  summary: Object,
  userRole: String,
})

const form = reactive({
  month: props.filters.month ?? '',
  company_id: props.filters.company_id ?? '',
})

const submit = () => {
  router.get(route('monthly-report.index'), form, {
    preserveState: true,
    preserveScroll: true,
  })
}

const exportReport = () => {
  const params = new URLSearchParams()

  if (form.month) params.append('month', form.month)
  if (form.company_id) params.append('company_id', form.company_id)

  window.location.href = `${route('monthly-report.export')}?${params.toString()}`
}

const truncateText = (text, length = 140) => {
  if (!text || text === '—') return '—'
  return text.length > length ? text.slice(0, length) + '...' : text
}

const aiStatusLabel = (status) => {
  switch (status) {
    case 'pending':
      return 'В очереди'
    case 'processing':
      return 'Анализируется'
    case 'done':
      return 'Готово'
    case 'failed':
      return 'Ошибка'
    case 'disabled':
      return 'Отключен'
    default:
      return status ?? '—'
  }
}

const aiStatusClass = (status) => {
  switch (status) {
    case 'pending':
      return 'bg-gray-100 text-gray-700'
    case 'processing':
      return 'bg-blue-100 text-blue-700'
    case 'done':
      return 'bg-green-100 text-green-700'
    case 'failed':
      return 'bg-red-100 text-red-700'
    case 'disabled':
      return 'bg-yellow-100 text-yellow-700'
    default:
      return 'bg-gray-100 text-gray-700'
  }
}

const hasCompanyFilter = computed(() => props.userRole === 'admin')
</script>

<template>
  <Head title="Месячный отчет" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Месячный отчет
        </h2>
      </div>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="mb-6 grid gap-4 md:grid-cols-3">
          <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">Всего позиций</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ summary.total }}</div>
          </div>

          <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">Загружено</div>
            <div class="mt-2 text-2xl font-bold text-green-600">{{ summary.uploaded }}</div>
          </div>

          <div class="rounded-lg bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">Не загружено</div>
            <div class="mt-2 text-2xl font-bold text-red-600">{{ summary.missing }}</div>
          </div>
        </div>

        <div class="mb-6 rounded-lg bg-white p-6 shadow-sm">
          <form @submit.prevent="submit" class="flex flex-wrap items-end gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Месяц</label>
              <input
                v-model="form.month"
                type="month"
                class="mt-1 rounded-md border-gray-300"
              />
            </div>

            <div v-if="hasCompanyFilter">
              <label class="block text-sm font-medium text-gray-700">Компания</label>
              <select
                v-model="form.company_id"
                class="mt-1 rounded-md border-gray-300"
              >
                <option value="">Все компании</option>
                <option
                  v-for="company in companies"
                  :key="company.id"
                  :value="company.id"
                >
                  {{ company.name }}
                </option>
              </select>
            </div>

            <div class="flex gap-2">
              <button
                type="submit"
                class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
              >
                Сформировать
              </button>

              <button
                type="button"
                @click="exportReport"
                class="rounded bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700"
              >
                Экспорт в Excel
              </button>
            </div>
          </form>
        </div>

        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div v-if="!rows.length" class="text-gray-500">
              Данных для отчета нет.
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr class="text-left text-sm font-semibold text-gray-900">
                    <th class="px-4 py-3">Компания</th>
                    <th class="px-4 py-3">Участок</th>
                    <th class="px-4 py-3">Зона</th>
                    <th class="px-4 py-3">Месяц</th>
                    <th class="px-4 py-3">Статус</th>
                    <th class="px-4 py-3">Фото</th>
                    <th class="px-4 py-3">Дата загрузки</th>
                    <th class="px-4 py-3">Пользователь</th>
                    <th class="px-4 py-3">AI</th>
                    <th class="px-4 py-3">Замечания</th>
                    <th class="px-4 py-3">Рекомендации</th>
                  </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                  <tr
                    v-for="(row, index) in rows"
                    :key="`${row.company_code}-${row.site_code}-${row.zone_code}-${index}`"
                    class="align-top"
                    :class="!row.uploaded ? 'bg-red-50' : ''"
                  >
                    <td class="px-4 py-4">{{ row.company }}</td>
                    <td class="px-4 py-4">{{ row.site }}</td>
                    <td class="px-4 py-4">{{ row.zone }}</td>
                    <td class="px-4 py-4">{{ row.month }}</td>

                    <td class="px-4 py-4">
                      <span
                        class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                        :class="row.uploaded ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                      >
                        {{ row.status_label }}
                      </span>
                    </td>

                    <td class="px-4 py-4">{{ row.photos_count }}</td>
                    <td class="px-4 py-4 whitespace-nowrap">{{ row.created_at }}</td>
                    <td class="px-4 py-4">{{ row.created_by }}</td>

                    <td class="px-4 py-4">
                      <span
                        class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                        :class="aiStatusClass(row.ai_status)"
                      >
                        {{ aiStatusLabel(row.ai_status) }}
                      </span>
                    </td>

                    <td class="max-w-xs px-4 py-4">
                      <div class="whitespace-pre-line break-words">
                        {{ truncateText(row.issues, 140) }}
                      </div>
                    </td>

                    <td class="max-w-xs px-4 py-4">
                      <div class="whitespace-pre-line break-words">
                        {{ truncateText(row.recommendations, 140) }}
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>