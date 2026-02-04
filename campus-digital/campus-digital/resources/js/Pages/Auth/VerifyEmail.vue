<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Verificar Correo Electrónico
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Gracias por registrarte. Antes de continuar, ¿podrías verificar tu dirección de correo electrónico haciendo clic en el enlace que acabamos de enviarte? Si no recibiste el correo, con gusto te enviaremos otro.
                </p>
            </div>

            <div v-if="status === 'verification-link-sent'" class="mb-4 font-medium text-sm text-success">
                Se ha enviado un nuevo enlace de verificación a tu correo electrónico.
            </div>

            <form @submit.prevent="submit">
                <div class="flex items-center justify-between">
                    <PrimaryButton :disabled="form.processing">
                        Reenviar Email de Verificación
                    </PrimaryButton>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900"
                    >
                        Cerrar Sesión
                    </Link>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};
</script>