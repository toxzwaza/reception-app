<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('admin.dashboard')" class="text-xl font-bold text-gray-800">
                                    受付管理システム
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <Link 
                                    :href="route('admin.dashboard')" 
                                    :class="[
                                        'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5',
                                        route().current('admin.dashboard') 
                                            ? 'border-indigo-400 text-gray-900 focus:outline-none focus:border-indigo-700' 
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300'
                                    ]"
                                >
                                    ダッシュボード
                                </Link>
                                <Link 
                                    :href="route('admin.appointments.index')" 
                                    :class="[
                                        'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5',
                                        route().current('admin.appointments.*') 
                                            ? 'border-indigo-400 text-gray-900 focus:outline-none focus:border-indigo-700' 
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300'
                                    ]"
                                >
                                    アポイント
                                </Link>
                                <Link 
                                    :href="route('admin.notification-settings.index')" 
                                    :class="[
                                        'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5',
                                        route().current('admin.notification-settings.*') 
                                            ? 'border-indigo-400 text-gray-900 focus:outline-none focus:border-indigo-700' 
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300'
                                    ]"
                                >
                                    通知設定
                                </Link>
                                <Link 
                                    :href="route('admin.announcements.index')" 
                                    :class="[
                                        'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5',
                                        route().current('admin.announcements.*') 
                                            ? 'border-indigo-400 text-gray-900 focus:outline-none focus:border-indigo-700' 
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300'
                                    ]"
                                >
                                    お知らせ
                                </Link>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <!-- Logout Button -->
                            <div class="ml-3 relative">
                                <Link 
                                    :href="route('logout')" 
                                    method="post" 
                                    as="button"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150"
                                >
                                    ログアウト
                                </Link>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <Link 
                            :href="route('admin.dashboard')" 
                            :class="[
                                'block pl-3 pr-4 py-2 border-l-4 text-base font-medium',
                                route().current('admin.dashboard')
                                    ? 'bg-indigo-50 border-indigo-400 text-indigo-700'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300'
                            ]"
                        >
                            ダッシュボード
                        </Link>
                        <Link 
                            :href="route('admin.appointments.index')" 
                            :class="[
                                'block pl-3 pr-4 py-2 border-l-4 text-base font-medium',
                                route().current('admin.appointments.*')
                                    ? 'bg-indigo-50 border-indigo-400 text-indigo-700'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300'
                            ]"
                        >
                            アポイント
                        </Link>
                        <Link 
                            :href="route('admin.notification-settings.index')" 
                            :class="[
                                'block pl-3 pr-4 py-2 border-l-4 text-base font-medium',
                                route().current('admin.notification-settings.*')
                                    ? 'bg-indigo-50 border-indigo-400 text-indigo-700'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300'
                            ]"
                        >
                            通知設定
                        </Link>
                        <Link 
                            :href="route('admin.announcements.index')" 
                            :class="[
                                'block pl-3 pr-4 py-2 border-l-4 text-base font-medium',
                                route().current('admin.announcements.*')
                                    ? 'bg-indigo-50 border-indigo-400 text-indigo-700'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 hover:border-gray-300'
                            ]"
                        >
                            お知らせ
                        </Link>
                    </div>

                    <!-- Responsive Logout Button -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <Link 
                                :href="route('logout')" 
                                method="post" 
                                as="button"
                                class="block w-full text-left px-3 py-2 border border-transparent text-base font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            >
                                ログアウト
                            </Link>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
