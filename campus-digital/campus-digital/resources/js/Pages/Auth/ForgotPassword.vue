<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Recuperar Contraseña
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
                </p>
            </div>

            <div v-if="status" class="mb-4 font-medium text-sm text-success">
                {{ status }}
            </div>

            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div>
                    <InputLabel for="email" value="Correo Electrónico" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        :error="form.errors.email"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div>
                    <PrimaryButton
                        :disabled="form.processing"
                        class="group relative w-full flex justify-center"
                    >
                        Enviar Enlace
                    </PrimaryButton>
                </div>

                <div class="text-center">
                    <Link
                        :href="route('login')"
                        class="font-medium text-sm text-primary hover:text-blue-700"
                    >
                        Volver al inicio de sesión
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>