<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Панель" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Панель управления
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-2xl">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-2xl font-semibold text-gray-900">
                            Добро пожаловать
                        </h3>

                        <p class="mt-3 max-w-3xl text-sm leading-6 text-gray-600">
                            Здесь вы можете вносить экологические данные,
                            просматривать информацию и формировать отчеты
                            за нужный период.
                        </p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-2xl">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold text-gray-900">
                                Как работать в системе
                            </h4>

                            <ol class="mt-4 space-y-4 text-sm leading-6 text-gray-700">
                                <li>
                                    <span class="font-semibold">1. Откройте нужный раздел.</span>
                                    Перейдите в раздел, где ведется учет данных или создаются отчеты.
                                </li>
                                <li>
                                    <span class="font-semibold">2. Внесите информацию.</span>
                                    Заполните необходимые поля и сохраните данные в системе.
                                </li>
                                <li>
                                    <span class="font-semibold">3. Проверьте введенные данные.</span>
                                    Убедитесь, что информация указана без ошибок и в полном объеме.
                                </li>
                                <li>
                                    <span class="font-semibold">4. Сформируйте отчет.</span>
                                    Выберите нужный период и создайте отчет по сохраненным данным.
                                </li>
                                <li>
                                    <span class="font-semibold">5. Просмотрите результат.</span>
                                    Проверьте сформированный отчет перед дальнейшим использованием или выгрузкой.
                                </li>
                            </ol>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-2xl">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold text-gray-900">
                                Основные возможности
                            </h4>

                            <div class="mt-4 grid gap-4">
                                <div class="rounded-xl border border-gray-200 p-4">
                                    <div class="font-semibold text-gray-900">
                                        Внесение данных
                                    </div>
                                    <div class="mt-1 text-sm leading-6 text-gray-600">
                                        Добавление и обновление информации для дальнейшей работы и отчетности.
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 p-4">
                                    <div class="font-semibold text-gray-900">
                                        Формирование отчетов
                                    </div>
                                    <div class="mt-1 text-sm leading-6 text-gray-600">
                                        Подготовка отчетов по выбранным данным и нужному периоду.
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-200 p-4">
                                    <div class="font-semibold text-gray-900">
                                        Просмотр информации
                                    </div>
                                    <div class="mt-1 text-sm leading-6 text-gray-600">
                                        Работа с уже внесенными данными и проверка результатов.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-2xl">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-900">
                            Рекомендация
                        </h4>

                        <p class="mt-3 text-sm leading-6 text-gray-700">
                            Перед формированием отчета всегда проверяйте,
                            что все данные за выбранный период внесены и сохранены.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>