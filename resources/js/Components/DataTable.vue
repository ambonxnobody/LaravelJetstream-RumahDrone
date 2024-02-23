<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    columns: {
        type: Array,
        required: true,
    },
    actions: {
        type: Array,
        required: false,
    }
})

const filteredData = ref([]);
const page = ref(1);
const perPage = ref(5);

watch(
    [page, perPage],
    () => {
        if (props.data.length > 0) {
            const start = (page.value - 1) * perPage.value;
            const end = start + perPage.value;
            filteredData.value = props.data.slice(start, end);
        }
    },
    { immediate: true }
)

watch(() => props.data, () => {
    page.value = 1;
    if (props.data.length > 0) {
        const start = (page.value - 1) * perPage.value;
        const end = start + perPage.value;
        filteredData.value = props.data.slice(start, end);
    }
})

// TODO: Implement sorting
const sortBy = (key) => {
    console.log(key);
}

const addPage = () => {
    page.value--;
}

const subtractPage = () => {
    page.value++;
}
</script>

<template>


    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 capitalize bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="p-4 text-center">No</th>
                    <th scope="col" v-for="(column, index) in columns" :key="index" :class="column.className && column.sortable ? (column.className + ' cursor-pointer') : column.className" @click="sortBy(column.key)" class="px-6 py-3">
                        {{ column.label }}
                    </th>
                    <th scope="col" class="px-6 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="filteredData.length > 0" v-for="(row, index) in filteredData" :key="index" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <td class="w-4 p-4 text-center">{{ index + 1 }}</td>
                    <td v-for="(column, index) in columns" :key="index" class="px-6 py-4" :class="column.className">
                        <template v-if='!column.type'>
                            <span v-if="column.format">{{ column.format(row) }}</span>
                            <span v-else>{{ row[column.key] }}</span>
                        </template>

                        <template v-else-if="column.type === 'status'">
                            <span
                                :class="{
                                    'bg-green-100 text-green-800 dark:bg-green-600 dark:text-green-100': row[column.key] === column.success,
                                    'bg-red-100 text-red-800 dark:bg-red-600 dark:text-red-100': row[column.key] === column.danger,
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-600 dark:text-yellow-100': row[column.key] === column.warning,
                                }"
                                class="px-2 py-1 rounded-full text-xs capitalize font-bold"
                            >
                                <span v-if="column.format">{{ column.format(row) }}</span>
                                <span v-else>{{ row[column.key] }}</span>
                            </span>
                        </template>
                    </td>
                    <td class="px-6 py-4 text-end">
                        <template v-if="actions">
                            <template v-for="(action, index) in actions" :key="index">
                                <template v-if="action.show">
                                    <template v-if="action.show(row)">
                                        <button
                                            class="mr-3 font-medium text-blue-600 dark:text-blue-500 hover:underline"
                                            @click="action.behaviour(row.id)"
                                        >
                                            {{ action.label }}
                                        </button>
                                    </template>
                                </template>

                                <template v-else-if="!action.show">
                                    <button
                                        class="mr-3 font-medium text-blue-600 dark:text-blue-500 hover:underline"
                                        @click="action.behaviour(row.id)"
                                    >
                                        {{ action.label }}
                                    </button>
                                </template>
                            </template>
                        </template>
                    </td>
                </tr>

                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600" v-else>
                    <td class="p-4 text-center" :colspan="columns.length + 2">No data found</td>
                </tr>
            </tbody>
        </table>
        <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between px-4 py-2" aria-label="Table navigation">
            <span class="text-sm font-normal text-gray-500 dark:text-gray-400 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                Showing{{ ' ' }}
                <span class="font-semibold text-gray-900 dark:text-white">
                    {{ filteredData.length > 0 ? ((page - 1) * perPage + 1) : 0 }} - {{ (page - 1) * perPage + filteredData.length }}
                </span>
                {{ ' ' }}of{{ ' ' }}
                <span class="font-semibold text-gray-900 dark:text-white">
                    {{ props.data.length }}
                </span>
            </span>

            <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                <li>
                    <button
                        class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white disabled:opacity-50 disabled:cursor-not-allowed dark:disabled:opacity-50 dark:disabled:cursor-not-allowed"
                        :disabled="page === 1"
                        @click="addPage"
                    >
                        Previous
                    </button>
                </li>

                <li>
                    <input
                        name="page"
                        class="no-arrows flex items-center justify-center w-12 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-none dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 focus:outline-none focus:ring-0 focus:border-gray-500 text-center"
                        type="number"
                        min="1"
                        :max="Math.ceil(props.data.length / perPage)"
                        v-model.number="page"
                    />
                </li>

                <li>
                    <button
                        class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white disabled:opacity-50 disabled:cursor-not-allowed dark:disabled:opacity-50 dark:disabled:cursor-not-allowed"
                        :disabled="page === Math.ceil(props.data.length / perPage)"
                        @click="subtractPage"
                    >
                        Next
                    </button>
                </li>
            </ul>
        </nav>
    </div>
</template>
