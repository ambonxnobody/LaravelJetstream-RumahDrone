<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from "@/Components/DataTable.vue";
import Modal from "@/Components/Modal.vue";
import { router, useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import ModalFormSection from '@/Components/ModalFormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faTimes } from "@fortawesome/free-solid-svg-icons";
import DangerButton from "@/Components/DangerButton.vue";

const props = defineProps({
    data: Object,
});

const showModal = ref('');
const showDeleteModal = ref(false);

const photoPreview = ref(null);
const photoInput = ref(null);
const inventoryId = ref(null);

const form = useForm({
    name: '',
    stock: null,
    price: null,
    weight: null,
    minimum_stock: null,
    // image: null,
});

const columns = [
    { label: 'Name', key: 'name', className: 'text-left', sortable: true },
    { label: 'Stock', key: 'stock', className: 'text-left', sortable: true, format: (row) => row.stock + ' pcs' },
    { label: 'Inbound', key: 'stock_in', className: 'text-left', sortable: true, format: (row) => row.stock_in + ' pcs' },
    { label: 'Outbound', key: 'stock_out', className: 'text-left', sortable: true, format: (row) => row.stock_out + ' pcs' },
    { label: 'Minimum Stock', key: 'minimum_stock', className: 'text-left', sortable: true, format: (row) => row.minimum_stock + ' pcs' },
];

const actions = [
    {
        label: 'Edit',
        behaviour: (id) => getDetail(id),
    },
    {
        label: 'Delete',
        behaviour: (id) => deleteData(id),
    },
];

const submit = () => {
    // if (photoInput.value) {
    //     form.image = photoInput.value.files[0];
    // }

    form.stock = parseInt(form.stock);
    form.price = parseInt(form.price);
    form.weight = parseInt(form.weight);
    form.minimum_stock = parseInt(form.minimum_stock);

    if (inventoryId.value) {
        form.put(route('inventory.update', inventoryId.value), {
            errorBag: 'updateInventoryInformation',
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            }
        });
    } else {
        form.post(route('inventory.store'), {
            errorBag: 'createInventoryInformation',
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            }
        });
    }
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (! photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    router.delete(route('inventory.removeImage', inventoryId.value), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = "https://ui-avatars.com/api/?name=X&color=7F9CF5&background=EBF4FF"
            clearPhotoFileInput();
            form.image = null;
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};

const closeModal = () => {
    showModal.value = '';
    form.reset();
    photoPreview.value = null;
    clearPhotoFileInput();
    inventoryId.value = null;
};

const addModal = () => {
    showModal.value = 'add';
    photoPreview.value = "https://ui-avatars.com/api/?name=X&color=7F9CF5&background=EBF4FF"
};

const getDetail = (id) => {
    showModal.value = 'edit';
    inventoryId.value = id;

    props.data.forEach((item) => {
        if (item.id === id) {
            form.name = item.name;
            form.stock = item.stock.toString();
            form.price = item.price.toString();
            form.weight = item.weight.toString();
            form.minimum_stock = item.minimum_stock.toString();
            if (item.image) {
                form.image = item.image;
            } else {
                photoPreview.value = "https://ui-avatars.com/api/?name=X&color=7F9CF5&background=EBF4FF";
            }
        }
    });
};

const deleteInventoryForm = useForm({});

const deleteData = (id) => {
    inventoryId.value = id;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    inventoryId.value = null;
};

const deleteInventory = () => {
    deleteInventoryForm.delete(route('inventory.destroy', inventoryId.value), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            inventoryId.value = null
            showDeleteModal.value = false;
        },
    });
};
</script>

<template>
    <AppLayout title="Inventory">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Inventory
                </h2>
                <button v-if="$page.props.auth.user.role === 'admin'" class="flex items-center px-4 py-0.5 text-sm font-medium text-white bg-blue-600 dark:bg-blue-500 rounded-md hover:bg-blue-500 dark:hover:bg-blue-400" @click="addModal">
                    Add New
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <DataTable :data="data" :columns="columns" :actions="$page.props.auth.user.role === 'admin' ? actions : null" />
                </div>
            </div>
        </div>

        <Modal
            :show="showModal !== ''"
            @close="closeModal"
        >
            <ModalFormSection @submitted="submit">
                <template #title>
                    {{ showModal === 'add' ? "Add New " : "Edit " }}Inventory
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
                    <!-- Name -->
                    <div class="col-span-6">
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autocomplete="name"
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <!-- Stock -->
                    <div class="col-span-6">
                        <InputLabel for="stock" value="Stock" />
                        <TextInput
                            id="stock"
                            v-model="form.stock"
                            type="number"
                            class="mt-1 block w-full"
                            required
                            autocomplete="stock"
                        />
                        <InputError :message="form.errors.stock" class="mt-2" />
                    </div>

                    <!-- Price -->
                    <div class="col-span-6">
                        <InputLabel for="price" value="Price" />
                        <TextInput
                            id="price"
                            v-model="form.price"
                            type="number"
                            class="mt-1 block w-full"
                            required
                            autocomplete="price"
                        />
                        <InputError :message="form.errors.price" class="mt-2" />
                    </div>

                    <!-- Weight -->
                    <div class="col-span-6">
                        <InputLabel for="weight" value="Weight" />
                        <TextInput
                            id="weight"
                            v-model="form.weight"
                            type="number"
                            class="mt-1 block w-full"
                            required
                            autocomplete="weight"
                        />
                        <InputError :message="form.errors.weight" class="mt-2" />
                    </div>

                    <!-- Minimum Stock -->
                    <div class="col-span-6">
                        <InputLabel for="minimum_stock" value="Minimum Stock" />
                        <TextInput
                            id="minimum_stock"
                            v-model="form.minimum_stock"
                            type="number"
                            class="mt-1 block w-full"
                            required
                            autocomplete="minimum_stock"
                        />
                        <InputError :message="form.errors.minimum_stock" class="mt-2" />
                    </div>

<!--                    <div class="col-span-6">-->
<!--                        <input-->
<!--                            id="photo"-->
<!--                            ref="photoInput"-->
<!--                            type="file"-->
<!--                            class="hidden"-->
<!--                            @change="updatePhotoPreview"-->
<!--                            accept="image/*"-->
<!--                        >-->

<!--                        <InputLabel for="photo" value="Photo" />-->

<!--                        <div v-show="! photoPreview" class="mt-2 flex justify-center">-->
<!--                            <img :src="form.image" :alt="form.name" class="rounded-lg h-20 w-auto object-cover">-->
<!--                        </div>-->

<!--                        <div v-show="photoPreview" class="mt-2 flex justify-center">-->
<!--                            <span-->
<!--                                class="block rounded-lg w-20 h-20 bg-cover bg-no-repeat bg-center"-->
<!--                                :style="'background-image: url(\'' + photoPreview + '\');'"-->
<!--                            />-->
<!--                        </div>-->

<!--                        <SecondaryButton class="mt-2 me-2" type="button" @click.prevent="selectNewPhoto">-->
<!--                            Select A New Photo-->
<!--                        </SecondaryButton>-->

<!--                        <SecondaryButton-->
<!--                            v-if="form.image"-->
<!--                            type="button"-->
<!--                            class="mt-2"-->
<!--                            @click.prevent="deletePhoto"-->
<!--                        >-->
<!--                            Remove Photo-->
<!--                        </SecondaryButton>-->

<!--                        <InputError :message="form.errors.photo" class="mt-2" />-->
<!--                    </div>-->
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
                Delete Inventory
            </template>

            <template #content>
                Are you sure you would like to delete this inventory? This action is permanent and cannot be undone.
            </template>

            <template #footer>
                <SecondaryButton @click="closeDeleteModal">
                    Cancel
                </SecondaryButton>

                <DangerButton
                    class="ms-3"
                    :class="{ 'opacity-25': deleteInventoryForm.processing }"
                    :disabled="deleteInventoryForm.processing"
                    @click="deleteInventory"
                >
                    Delete
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
