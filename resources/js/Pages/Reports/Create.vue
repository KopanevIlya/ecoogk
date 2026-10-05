<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  sites: Array,
  zones: Array,
  userRole: String,
})

const form = useForm({
  site_id: '',
  zone_id: '',
  comment: '',
  photos: [],
})

const isResponsible = computed(() => props.userRole === 'responsible')

const submit = () => {
  form.post(route('reports.store'), {
    forceFormData: true,
  })
}

const handleFiles = (event) => {
  form.photos = Array.from(event.target.files || [])
}
</script>

<template>
  <Head title="Загрузка отчета" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Загрузка фото
      </h2>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow sm:rounded-lg">
          <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
            <p class="font-medium">Как это работает</p>
            <p class="mt-1">
              Выберите участок, зону и загрузите фотографии. Дата и отчетный месяц будут установлены автоматически по серверному времени.
            </p>
          </div>

          <form @submit.prevent="submit" class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700">Участок</label>
              <select
                v-model="form.site_id"
                class="mt-1 block w-full rounded-md border-gray-300"
              >
                <option value="">Выберите участок</option>
                <option v-for="site in sites" :key="site.id" :value="site.id">
                  {{ site.name }}
                </option>
              </select>
              <div v-if="form.errors.site_id" class="mt-1 text-sm text-red-600">
                {{ form.errors.site_id }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Зона</label>
              <select
                v-model="form.zone_id"
                class="mt-1 block w-full rounded-md border-gray-300"
              >
                <option value="">Выберите зону</option>
                <option v-for="zone in zones" :key="zone.id" :value="zone.id">
                  {{ zone.name }}
                </option>
              </select>
              <div v-if="form.errors.zone_id" class="mt-1 text-sm text-red-600">
                {{ form.errors.zone_id }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Комментарий</label>
              <textarea
                v-model="form.comment"
                rows="4"
                class="mt-1 block w-full rounded-md border-gray-300"
                placeholder="При необходимости добавьте комментарий"
              />
              <div v-if="form.errors.comment" class="mt-1 text-sm text-red-600">
                {{ form.errors.comment }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Фотографии</label>
              <input
                type="file"
                multiple
                accept="image/*"
                capture="environment"
                @change="handleFiles"
                class="mt-1 block w-full"
              />
              <p class="mt-2 text-xs text-gray-500">
                Можно выбрать одно или несколько фото. На телефоне откроется камера или галерея.
              </p>
              <div v-if="form.errors.photos" class="mt-1 text-sm text-red-600">
                {{ form.errors.photos }}
              </div>
              <div v-if="form.errors['photos.*']" class="mt-1 text-sm text-red-600">
                {{ form.errors['photos.*'] }}
              </div>
            </div>

            <div v-if="form.photos.length" class="rounded-lg bg-gray-50 p-4">
              <p class="mb-2 text-sm font-medium text-gray-700">Выбрано файлов: {{ form.photos.length }}</p>
              <ul class="space-y-1 text-sm text-gray-600">
                <li v-for="(file, index) in form.photos" :key="index">
                  {{ file.name }}
                </li>
              </ul>
            </div>

            <div
              v-if="isResponsible"
              class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
            >
              После загрузки фотографий они будут доступны для проверки экологу и администратору. Результаты AI-анализа вам не отображаются.
            </div>

            <div class="flex flex-wrap gap-3">
              <button
                type="submit"
                class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="form.processing"
              >
                {{ form.processing ? 'Загрузка...' : 'Загрузить' }}
              </button>

              <Link
                :href="route('reports.index')"
                class="rounded bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300"
              >
                Назад
              </Link>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>