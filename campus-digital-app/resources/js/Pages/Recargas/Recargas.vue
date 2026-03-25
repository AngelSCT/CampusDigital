<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const monto = ref('')

const enviar = () => {

    if (!monto.value) {
        alert('Ingresa un monto')
        return
    }

    router.post('/modulo_8/recargar', {
        monto: monto.value,
        metodo_pago: 'Tarjeta'
    },
    {
        onSuccess: () => {
            alert('recarga exitosa')
        }
    })
}
</script>

<template>
<div>

    <h1>Recargar saldo</h1>

    <input 
        v-model="monto" 
        type="number" 
        placeholder="Monto"
    >

    <button @click="enviar">
        Recargar
    </button>

</div>
</template>