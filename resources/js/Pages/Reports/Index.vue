<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, watch } from 'vue'

const props = defineProps({
  reports: Array,
})

const hasPollingReports = computed(() =>
  props.reports.some((report) =>
    ['pending', 'processing'].includes(report.ai_status)
  )
)

const hasVisibleProcessingReports = computed(() =>
  props.reports.some((report) => report.ai_status === 'processing')
)

let intervalId = null

const reloadReports = () => {
  router.reload({
    only: ['reports'],
    preserveScroll: true,
    preserveState: true,
  })
}

const startPolling = () => {
  if (intervalId) return

  intervalId = setInterval(() => {
    reloadReports()
  }, 4000)
}

const stopPolling = () => {
  if (intervalId) {
    clearInterval(intervalId)
    intervalId = null
  }
}

watch(
  hasPollingReports,
  (value) => {
    if (value) {
      startPolling()
    } else {
      stopPolling()
    }
  },
  { immediate: true }
)

onBeforeUnmount(() => {
  stopPolling()
})

const truncateText = (text, length = 140) => {
  if (!text) return '—'
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
</script>

<template>
  <Head title="Отчеты" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Отчеты</h2>

        <Link
          :href="route('reports.create')"
          class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500"
        >
          Загрузить отчет
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div
          v-if="hasVisibleProcessingReports"
          class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700"
        >
          AI-анализ выполняется. Таблица обновляется автоматически.
        </div>

        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div v-if="!reports.length" class="text-gray-500">
              Отчетов пока нет.
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr class="text-left text-sm font-semibold text-gray-900">
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Участок</th>
                    <th class="px-4 py-3">Зона</th>
                    <th class="px-4 py-3">Месяц</th>
                    <th class="px-4 py-3">Фото</th>
                    <th class="px-4 py-3">Статус</th>
                    <th class="px-4 py-3">AI</th>
                    <th class="px-4 py-3">Пользователь</th>
                    <th class="px-4 py-3">Создан</th>
                    <th class="px-4 py-3">AI результат</th>
                    <th class="px-4 py-3">Действия</th>
                  </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
  <tr v-for="report in reports" :key="report.id" class="align-top">
    <td class="px-4 py-4">{{ report.id }}</td>
    <td class="px-4 py-4">{{ report.site ?? '—' }}</td>
    <td class="px-4 py-4">{{ report.zone ?? '—' }}</td>
    <td class="px-4 py-4">{{ report.report_month ?? '—' }}</td>
    <td class="px-4 py-4">{{ report.photos_count ?? 0 }}</td>
    <td class="px-4 py-4">{{ report.status ?? '—' }}</td>
    <td class="px-4 py-4">
      <span
        class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
        :class="aiStatusClass(report.ai_status)"
      >
        {{ aiStatusLabel(report.ai_status) }}
      </span>
    </td>
    <td class="px-4 py-4">{{ report.created_by ?? '—' }}</td>
    <td class="px-4 py-4 whitespace-nowrap">{{ report.created_at ?? '—' }}</td>
    <td class="px-4 py-4 max-w-sm">
      <div class="whitespace-pre-line break-words">
        {{ truncateText(report.ai_result, 140) }}
      </div>
    </td>
    <td class="px-4 py-4">
      <Link
        :href="route('reports.show', report.id)"
        class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-700"
      >
        Открыть
      </Link>
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