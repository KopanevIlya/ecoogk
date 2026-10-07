<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
        default: false,
    },
    canRegister: {
        type: Boolean,
        default: false,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Эко ОГК" />

    <div class="relative min-h-screen overflow-hidden bg-black text-white">
        <div
            class="absolute inset-0 bg-cover bg-center bg-no-repeat scale-105"
            style="background-image: url('/images/welcome-bg.jpg')"
        ></div>

        <div class="absolute inset-0 bg-gradient-to-br from-black/80 via-black/65 to-black/75"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(249,115,22,0.18),transparent_30%)]"></div>

        <div class="relative z-10 flex min-h-screen flex-col">
            <header class="flex items-center justify-between px-6 py-6 md:px-10">
                <div class="flex flex-col">
                    <span class="text-xs font-medium uppercase tracking-[0.28em] text-white/50">
                        Экологический учет
                    </span>
                  
                </div>

                <nav v-if="canLogin" class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/20"
                    >
                        В кабинет
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600"
                        >
                            Войти
                        </Link>
                    </template>
                </nav>
            </header>

            <main class="flex flex-1 items-center px-6 py-10 md:px-10">
                <div class="max-w-4xl">
                    <div
                        class="inline-flex items-center rounded-full border border-white/10 bg-white/10 px-4 py-2 text-sm text-white/80 backdrop-blur-sm"
                    >
                        Единая система учета и отчетности
                    </div>

                    <h1 class="mt-6 max-w-4xl text-4xl font-bold leading-tight md:text-6xl md:leading-[1.05]">
                        Экологические данные и отчетность в одной системе
                    </h1>

                    <p class="mt-6 max-w-2xl text-base leading-7 text-white/80 md:text-xl md:leading-9">
                        Сервис помогает вести экологический учет, работать с
                        данными по объектам и формировать отчеты в едином
                        рабочем пространстве.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-4">
                        <Link
                            v-if="canLogin && !$page.props.auth.user"
                            :href="route('login')"
                            class="rounded-2xl bg-orange-500 px-6 py-3 text-sm font-semibold text-white transition hover:bg-orange-600 md:text-base"
                        >
                            Войти в систему
                        </Link>

                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="rounded-2xl bg-orange-500 px-6 py-3 text-sm font-semibold text-white transition hover:bg-orange-600 md:text-base"
                        >
                            Открыть кабинет
                        </Link>

                        <a
                            href="#about"
                            class="rounded-2xl border border-white/15 bg-white/10 px-6 py-3 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/15 md:text-base"
                        >
                            О сервисе
                        </a>
                    </div>

                    <div class="mt-12 grid max-w-3xl gap-4 md:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur-md">
                            <div class="text-sm font-semibold text-white">
                                Учет данных
                            </div>
                            <p class="mt-2 text-sm leading-6 text-white/70">
                                Ведение и актуализация экологической информации в одном месте.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur-md">
                            <div class="text-sm font-semibold text-white">
                                Отчетность
                            </div>
                            <p class="mt-2 text-sm leading-6 text-white/70">
                                Быстрое формирование отчетов по нужному периоду и данным.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur-md">
                            <div class="text-sm font-semibold text-white">
                                Единая среда
                            </div>
                            <p class="mt-2 text-sm leading-6 text-white/70">
                                Удобная работа с данными, документами и результатами.
                            </p>
                        </div>
                    </div>
                </div>
            </main>

            <section id="about" class="relative z-10 px-6 pb-10 md:px-10">
                <div class="max-w-4xl rounded-3xl border border-white/10 bg-white/10 p-6 backdrop-blur-md md:p-8">
                    <h2 class="text-xl font-semibold text-white md:text-2xl">
                        О сервисе
                    </h2>
                    <p class="mt-4 max-w-3xl text-sm leading-7 text-white/75 md:text-base">
                        Эко ОГК — это рабочее пространство для экологического учета,
                        контроля информации и подготовки отчетности. Система объединяет
                        основные процессы в одном интерфейсе и помогает быстрее работать
                        с данными без лишней сложности.
                    </p>
                </div>
            </section>
        </div>
    </div>
</template>