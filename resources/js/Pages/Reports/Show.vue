<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
  report: Object,
  canSeeAiResults: Boolean,
  userRole: String,
})

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
  <Head :title="`Отчет #${report.id}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Отчет #{{ report.id }}
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            Подробная информация и фотографии
          </p>
        </div>

        <Link
          :href="route('reports.index')"
          class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-300"
        >
          Назад к списку
        </Link>
      </div>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-3">
          <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg lg:col-span-1">
            <div class="p-6">
              <h3 class="mb-4 text-lg font-semibold text-gray-900">Информация</h3>

              <dl class="space-y-4 text-sm">
                <div>
                  <dt class="text-gray-500">Компания</dt>
                  <dd class="mt-1 font-medium text-gray-900">{{ report.company?.name ?? '—' }}</dd>
                </div>

                <div>
                  <dt class="text-gray-500">Участок</dt>
                  <dd class="mt-1 font-medium text-gray-900">{{ report.site?.name ?? '—' }}</dd>
                </div>

                <div>
                  <dt class="text-gray-500">Зона</dt>
                  <dd class="mt-1 font-medium text-gray-900">{{ report.zone?.name ?? '—' }}</dd>
                </div>

                <div>
                  <dt class="text-gray-500">Месяц</dt>
                  <dd class="mt-1 font-medium text-gray-900">{{ report.report_month ?? '—' }}</dd>
                </div>

                <div>
                  <dt class="text-gray-500">Статус отчета</dt>
                  <dd class="mt-1 font-medium text-gray-900">{{ report.status ?? '—' }}</dd>
                </div>

                <div>
                  <dt class="text-gray-500">AI статус</dt>
                  <dd class="mt-2">
                    <span
                      class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                      :class="aiStatusClass(report.ai_status)"
                    >
                      {{ aiStatusLabel(report.ai_status) }}
                    </span>
                  </dd>
                </div>

                <div v-if="userRole !== 'responsible'">
                  <dt class="text-gray-500">Пользователь</dt>
                  <dd class="mt-1 font-medium text-gray-900">{{ report.user?.name ?? '—' }}</dd>
                </div>

                <div>
                  <dt class="text-gray-500">Создан</dt>
                  <dd class="mt-1 font-medium text-gray-900">{{ report.created_at ?? '—' }}</dd>
                </div>

                <div>
                  <dt class="text-gray-500">Комментарий</dt>
                  <dd class="mt-1 whitespace-pre-line font-medium text-gray-900">
                    {{ report.comment || '—' }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>

          <div
            v-if="canSeeAiResults"
            class="overflow-hidden bg-white shadow-sm sm:rounded-lg lg:col-span-2"
          >
            <div class="p-6">
              <h3 class="mb-4 text-lg font-semibold text-gray-900">AI-анализ</h3>

              <div
                v-if="['pending', 'processing'].includes(report.ai_status)"
                class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700"
              >
                Анализ еще выполняется. Обнови страницу чуть позже.
              </div>

              <div
                v-else-if="report.ai_result"
                class="whitespace-pre-line rounded-lg bg-gray-50 p-4 text-sm leading-7 text-gray-800"
              >
                {{ report.ai_result }}
              </div>

              <div
                v-else
                class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-500"
              >
                Результат AI пока отсутствует.
              </div>
            </div>
          </div>

          <div
            v-else
            class="overflow-hidden bg-white shadow-sm sm:rounded-lg lg:col-span-2"
          >
            <div class="p-6">
              <h3 class="mb-4 text-lg font-semibold text-gray-900">Результат проверки</h3>

              <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-500">
                Результаты AI-анализа недоступны для вашей роли.
              </div>
            </div>
          </div>
        </div>

        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">Фотографии</h3>

            <div v-if="!report.photos?.length" class="text-sm text-gray-500">
              Фотографии отсутствуют.
            </div>

            <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <a
                v-for="photo in report.photos"
                :key="photo.id"
                :href="photo.url"
                target="_blank"
                class="group block overflow-hidden rounded-lg border border-gray-200 bg-white"
              >
                <img
                  :src="photo.url"
                  :alt="photo.original_name || `Фото #${photo.id}`"
                  class="h-56 w-full object-cover transition duration-200 group-hover:scale-[1.02]"
                />

                <div class="border-t border-gray-100 p-3">
                  <p class="truncate text-sm text-gray-700">
                    {{ photo.original_name || `Фото #${photo.id}` }}
                  </p>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>