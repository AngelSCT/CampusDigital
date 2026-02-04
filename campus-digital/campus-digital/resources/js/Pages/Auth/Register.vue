<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Campus Digital
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Crear una cuenta nueva
                </p>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <InputLabel for="nombre" value="Nombre" />
                        <TextInput
                            id="nombre"
                            v-model="form.nombre"
                            type="text"
                            required
                            autofocus
                            :error="form.errors.nombre"
                        />
                        <InputError :message="form.errors.nombre" />
                    </div>

                    <div>
                        <InputLabel for="apellido" value="Apellido" />
                        <TextInput
                            id="apellido"
                            v-model="form.apellido"
                            type="text"
                            required
                            :error="form.errors.apellido"
                        />
                        <InputError :message="form.errors.apellido" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Correo Electrónico" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            :error="form.errors.email"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Contraseña" />
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            :error="form.errors.password"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            :error="form.errors.password_confirmation"
                        />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>
                </div>

                <div>
                    <PrimaryButton
                        :disabled="form.processing"
                        class="group relative w-full flex justify-center"
                    >
                        Registrarse
                    </PrimaryButton>
                </div>

                <div class="text-center">
                    <Link
                        :href="route('login')"
                        class="font-medium text-sm text-primary hover:text-blue-700"
                    >
                        ¿Ya tienes cuenta? Inicia sesión
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

const form = useForm({
    nombre: '',
    apellido: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>