<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

defineProps({
  reports: Array,
})
</script>

<template>
  <Head title="Отчеты" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
          Отчеты
        </h2>

        <Link
          :href="route('reports.create')"
          class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
        >
          Загрузить отчет
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
          <div class="p-6">
            <div v-if="reports.length === 0" class="text-gray-500">
              Пока нет загруженных отчетов.
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full border-collapse">
                <thead>
                  <tr class="border-b">
                    <th class="px-4 py-3 text-left">ID</th>
                    <th class="px-4 py-3 text-left">Участок</th>
                    <th class="px-4 py-3 text-left">Зона</th>
                    <th class="px-4 py-3 text-left">Месяц</th>
                    <th class="px-4 py-3 text-left">Фото</th>
                    <th class="px-4 py-3 text-left">Статус</th>
                    <th class="px-4 py-3 text-left">AI</th>
                    <th class="px-4 py-3 text-left">Пользователь</th>
                    <th class="px-4 py-3 text-left">Создан</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="report in reports" :key="report.id" class="border-b">
                    <td class="px-4 py-3">{{ report.id }}</td>
                    <td class="px-4 py-3">{{ report.site }}</td>
                    <td class="px-4 py-3">{{ report.zone }}</td>
                    <td class="px-4 py-3">{{ report.report_month }}</td>
                    <td class="px-4 py-3">{{ report.photos_count }}</td>
                    <td class="px-4 py-3">{{ report.status }}</td>
                    <td class="px-4 py-3">{{ report.ai_status }}</td>
                    <td class="px-4 py-3">{{ report.created_by }}</td>
                    <td class="px-4 py-3">{{ report.created_at }}</td>
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