<script setup>
    import { ref } from 'vue';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import DataTable from "@/Components/DataTable.vue";
    import Modal from "@/Components/Modal.vue";
    import { useForm } from '@inertiajs/vue3';
    import ActionMessage from '@/Components/ActionMessage.vue';
    import ModalFormSection from '@/Components/ModalFormSection.vue';
    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue';
    import TextInput from '@/Components/TextInput.vue';
    import ConfirmationModal from '@/Components/ConfirmationModal.vue';
    import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
    import { faTimes, faPlus } from "@fortawesome/free-solid-svg-icons";
    import DangerButton from "@/Components/DangerButton.vue";

    const props = defineProps({
        data: Array,
        inventory: Array,
    });

    const showModal = ref('');
    const showDeleteModal = ref(false);
    const showDeliveredModal = ref(false);

    const deliveryId = ref(null);

    const form = useForm({
        date: '',
        status: '',
        type: '',
        notes: '',
        inventory: []
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
            label: 'Mark as Delivered',
            show: (item) => item.status === 'pending',
            behaviour: (id) => openDeliveredModal(id),
        },
        {
            label: 'Edit',
            show: (item) => item.status === 'pending',
            behaviour: (id) => getDetail(id),
        },
        {
            label: 'Delete',
            show: (item) => item.status === 'pending',
            behaviour: (id) => deleteData(id),
        },
    ];

    const formatDateToIndonesian = (date) => {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(date.date).toLocaleDateString('id-ID', options);
    };

    const addInventory = () => {
        form.inventory.push(JSON.parse(JSON.stringify({
            inventory_id: 0,
            quantity: null
        })));
    };

    const submit = () => {
        form.stock = parseInt(form.stock);
        form.price = parseInt(form.price);
        form.weight = parseInt(form.weight);
        form.minimum_stock = parseInt(form.minimum_stock);

        if (deliveryId.value) {
            form.put(route('delivery.update', deliveryId.value), {
                errorBag: 'updateDeliveryInformation',
                preserveScroll: true,
                onSuccess: () => {
                    closeModal();
                }
            });
        } else {
            form.post(route('delivery.store'), {
                errorBag: 'createDeliveryInformation',
                preserveScroll: true,
                onSuccess: () => {
                    closeModal();
                }
            });
        }
    };

    const closeModal = () => {
        showModal.value = '';
        form.reset();
        deliveryId.value = null;
    };

    const addModal = () => {
        showModal.value = 'add';
    };

    const getDetail = (id) => {
        showModal.value = 'edit';
        deliveryId.value = id;

        props.data.forEach((item) => {
            if (item.id === id) {
                form.date = item.date;
                form.status = item.status;
                form.type = item.type;
                form.notes = item.notes;
                form.inventory = item.inventory;
            }
        });
    };

    const deleteDeliveryForm = useForm({});

    const deleteData = (id) => {
        deliveryId.value = id;
        showDeleteModal.value = true;
    };

    const closeDeleteModal = () => {
        showDeleteModal.value = false;
        deliveryId.value = null;
    };

    const deleteDelivery = () => {
        deleteDeliveryForm.delete(route('delivery.destroy', deliveryId.value), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                deliveryId.value = null
                showDeleteModal.value = false;
            },
        });
    };

    const decrementQuantity = (index) => {
        if (form.inventory[index].quantity > 0) {
            form.inventory[index].quantity--;
        }
    };

    const incrementQuantity = (index) => {
        form.inventory[index].quantity++;
    };

    const deliveredForm = useForm({
        date: '',
        status: '',
        type: '',
        notes: '',
        inventory: []
    });

    const openDeliveredModal = (id) => {
        showDeliveredModal.value = true;

        props.data.forEach((item) => {
            if (item.id === id) {
                deliveredForm.date = item.date;
                deliveredForm.status = item.status;
                deliveredForm.type = item.type;
                deliveredForm.notes = item.notes;
                deliveredForm.inventory = item.inventory;
            }
        });
    };

    const closeDeliveredModal = () => {
        showDeliveredModal.value = false;
        deliveryId.value = null;
    };

    const submitDelivered = () => {
        deliveredForm.put(route('delivery.update', deliveredForm.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                deliveryId.value = null
                deliveredForm.reset();
                showDeliveredModal.value = false;
            },
        });
    };
</script>

<template>
    <AppLayout title="Delivery">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Delivery
                </h2>
                <button class="flex items-center px-4 py-0.5 text-sm font-medium text-white bg-blue-600 dark:bg-blue-500 rounded-md hover:bg-blue-500 dark:hover:bg-blue-400" @click="addModal">
                    Add New
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <DataTable :data="data" :columns="columns" :actions="$page.props.auth.user.role === 'staff' ? actions : null" />
                </div>
            </div>
        </div>

        <Modal
            :show="showModal !== ''"
            @close="closeModal"
        >
            <ModalFormSection @submitted="submit">
                <template #title>
                    {{ showModal === 'add' ? "Add New " : "Edit " }}Delivery
                </template>

                <template #toolbar>
                    <button
                        type="button"
                        class="text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-400"
                        @click="closeModal"
                    >
                        <span class="sr-only">Close</span>
                        <FontAwesomeIcon :icon="faTimes" />
                    </button>
                </template>

                <template #form>
                    <!-- Date -->
                    <div class="col-span-6">
                        <InputLabel for="date" value="Date" />
                        <TextInput id="date" type="date" v-model="form.date" class="mt-1 block w-full" />
                        <InputError :message="form.errors.date" class="mt-2" />
                    </div>

                    <!-- Status -->
                    <div class="col-span-6">
                        <InputLabel for="status" value="Status" />

                        <div class="flex items-center my-4">
                            <input id="pending" type="radio" value="pending" name="status" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" v-model="form.status" onchange="this.setAttribute('value', this.value);">
                            <label for="pending" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Pending</label>
                        </div>
                        <div class="flex items-center">
                            <input id="delivered" type="radio" value="delivered" name="status" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" v-model="form.status" onchange="this.setAttribute('value', this.value);">
                            <label for="delivered" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Delivered</label>
                        </div>

                        <InputError :message="form.errors.status" class="mt-2" />
                    </div>

                    <!-- Type -->
                    <div class="col-span-6">
                        <InputLabel for="type" value="Type" />

                        <div class="flex items-center my-4">
                            <input id="inbound" type="radio" value="inbound" name="type" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" v-model="form.type" onchange="this.setAttribute('value', this.value);">
                            <label for="inbound" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Inbound</label>
                        </div>
                        <div class="flex items-center">
                            <input id="outbound" type="radio" value="outbound" name="type" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" v-model="form.type" onchange="this.setAttribute('value', this.value);">
                            <label for="outbound" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Outbound</label>
                        </div>

                        <InputError :message="form.errors.type" class="mt-2" />
                    </div>

                    <!-- Notes -->
                    <div class="col-span-6">
                        <InputLabel for="notes" value="Notes" />
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            class="relative w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            onchange="this.setAttribute('value', this.value);"
                        ></textarea>
                        <InputError :message="form.errors.notes" class="mt-2" />
                    </div>

                    <!-- Inventory -->
                    <div class="col-span-6">
                        <InputLabel for="inventory">
                            <div class="flex justify-between">
                                <span>Inventory</span>
                                <button type="button" class="text-blue-600 dark:text-blue-500" @click="addInventory">
                                    <FontAwesomeIcon :icon="faPlus" />
                                </button>
                            </div>
                        </InputLabel>

                        <div v-if="form.inventory" v-for="(item, index) in form.inventory" :key="index" class="grid grid-cols-6 gap-1 mt-3">
                            <InputLabel class="col-span-6" :for="'inventory' + index" :value="'Inventory ' + (index + 1)" />

                            <div class="col-span-6">
                                <select
                                    v-model="item.inventory_id"
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-center"
                                >
                                    <option value="" disabled selected>Select an inventory</option>
                                    <option v-for="inventory in inventory" :value="inventory.id">{{ inventory.name }}</option>
                                </select>
                                <InputError :message="form.errors.inventory" class="mt-2" />
                            </div>

                            <div class="col-span-6 grid grid-cols-12 gap-1 items-center">
                                <button
                                    class="col-span-1 p-1 text-sm font-medium h-6 w-6 text-gray-500 bg-white border border-gray-300 rounded-full focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700 disabled:opacity-50 disabled:cursor-not-allowed dark:disabled:opacity-50 dark:disabled:cursor-not-allowed flex items-center justify-center"
                                    type="button"
                                    @click="decrementQuantity(index)"
                                    :disabled="item.quantity <= 0"
                                >
                                    <span class="sr-only">Quantity button</span>
                                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h16"/>
                                    </svg>
                                </button>
                                <TextInput v-model="item.quantity" type="number" class="col-span-10 text-center" />
                                <button
                                    class="col-span-1 h-6 w-6 p-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-full focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700 flex items-center justify-center"
                                    type="button"
                                    @click="incrementQuantity(index)"
                                >
                                    <span class="sr-only">Quantity button</span>
                                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <InputError :message="form.errors.inventory" class="mt-2" />
                    </div>

                </template>

                <template #actions>
                    <ActionMessage :on="form.recentlySuccessful" class="me-3">
                        Saved.
                    </ActionMessage>

                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Save
                    </PrimaryButton>
                </template>
            </ModalFormSection>
        </Modal>

        <ConfirmationModal :show="showDeleteModal" @close="!showDeleteModal">
            <template #title>
                Delete Delivery
            </template>

            <template #content>
                Are you sure you would like to delete this delivery? This action is permanent and cannot be undone.
            </template>

            <template #footer>
                <SecondaryButton @click="closeDeleteModal">
                    Cancel
                </SecondaryButton>

                <DangerButton
                    class="ms-3"
                    :class="{ 'opacity-25': deleteDeliveryForm.processing }"
                    :disabled="deleteDeliveryForm.processing"
                    @click="deleteDelivery"
                >
                    Delete
                </DangerButton>
            </template>
        </ConfirmationModal>

        <ConfirmationModal :show="showDeliveredModal" @close="!showDeliveredModal">
            <template #title>
                Mark as Delivered
            </template>

            <template #content>
                Are you sure you would like to mark this delivery as delivered?
            </template>

            <template #footer>
                <SecondaryButton @click="closeDeliveredModal">
                    Cancel
                </SecondaryButton>

                <PrimaryButton
                    class="ms-3"
                    :class="{ 'opacity-25': deliveredForm.processing }"
                    :disabled="deliveredForm.processing"
                    @click="submitDelivered"
                >
                    Mark as Delivered
                </PrimaryButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
