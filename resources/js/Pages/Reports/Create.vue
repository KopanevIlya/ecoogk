<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
  sites: Array,
  zones: Array,
})

const form = useForm({
  site_id: '',
  zone_id: '',
  report_month: '',
  comment: '',
  photos: [],
})

const submit = () => {
  form.post(route('reports.store'), {
    forceFormData: true,
  })
}

const handleFiles = (event) => {
  form.photos = Array.from(event.target.files)
}
</script>

<template>
  <Head title="Загрузка отчета" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Загрузка отчета
      </h2>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow sm:rounded-lg">
          <form @submit.prevent="submit" class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700">Участок</label>
              <select v-model="form.site_id" class="mt-1 block w-full rounded-md border-gray-300">
                <option value="">Выберите участок</option>
                <option v-for="site in sites" :key="site.id" :value="site.id">
                  {{ site.name }}
                </option>
              </select>
              <div v-if="form.errors.site_id" class="mt-1 text-sm text-red-600">{{ form.errors.site_id }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Зона</label>
              <select v-model="form.zone_id" class="mt-1 block w-full rounded-md border-gray-300">
                <option value="">Выберите зону</option>
                <option v-for="zone in zones" :key="zone.id" :value="zone.id">
                  {{ zone.name }}
                </option>
              </select>
              <div v-if="form.errors.zone_id" class="mt-1 text-sm text-red-600">{{ form.errors.zone_id }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Месяц отчета</label>
              <input v-model="form.report_month" type="date" class="mt-1 block w-full rounded-md border-gray-300" />
              <div v-if="form.errors.report_month" class="mt-1 text-sm text-red-600">{{ form.errors.report_month }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Комментарий</label>
              <textarea v-model="form.comment" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
              <div v-if="form.errors.comment" class="mt-1 text-sm text-red-600">{{ form.errors.comment }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Фотографии</label>
              <input type="file" multiple @change="handleFiles" class="mt-1 block w-full" />
              <div v-if="form.errors.photos" class="mt-1 text-sm text-red-600">{{ form.errors.photos }}</div>
              <div v-if="form.errors['photos.*']" class="mt-1 text-sm text-red-600">{{ form.errors['photos.*'] }}</div>
            </div>

            <div class="flex gap-3">
              <button
                type="submit"
                class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                :disabled="form.processing"
              >
                Загрузить
              </button>

              <a :href="route('reports.index')" class="rounded bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300">
                Назад
              </a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>