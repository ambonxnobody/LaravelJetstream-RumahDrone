<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import DataTable from "@/Components/DataTable.vue";
    import { router } from "@inertiajs/vue3";

    const props = defineProps({
        data: Array,
        inventory: Array,
    });

    const columns = [
        { label: 'Order Number', key: 'order_number', className: 'text-center', sortable: true },
        { label: 'Date', key: 'date', className: 'text-center', sortable: true, format: (value) => formatDateToIndonesian(value) },
        { label: 'Status', key: 'status', className: 'text-center', type: 'status', success: 'delivered', warning: 'pending', sortable: true },
        { label: 'Type', key: 'type', className: 'text-center', type: 'status', success: 'inbound', danger: 'outbound', sortable: true },
        { label: 'Notes', key: 'notes' },
    ];

    const actions = [
        {
            label: 'View Invoice',
            show: (item) => item.status === 'delivered',
            behaviour: (id) => redirectTo(id),
        },
    ];

    const redirectTo = (id) => {
        router.visit(`/invoice/${id}`);
    }

    const formatDateToIndonesian = (date) => {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(date.date).toLocaleDateString('id-ID', options);
    };
</script>

<template>
    <AppLayout title="Report">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Report
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <DataTable :data="data" :columns="columns" :actions="$page.props.auth.user.role === 'admin' ? actions : null" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
