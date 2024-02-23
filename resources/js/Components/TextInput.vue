<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: String,
    step: {
        type: String,
        default: 'any',
        required: false,
    },
    buttonGroup: {
        type: Boolean,
        default: false,
        required: false,
    },
    buttonGroupLabel: {
        type: String,
        default: '',
        required: false,
    },
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        class="relative border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :step="step"
    />
</template>
