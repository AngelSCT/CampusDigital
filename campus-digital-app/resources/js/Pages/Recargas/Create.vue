<script setup>
import { ref } from 'vue'
import axios from 'axios'

const monto = ref('')
const metodo = ref('tarjeta')
const mensaje = ref('')

const recargar = async () => {
    try {
        
        if (monto.value <= 0) {
            mensaje.value = "Monto inválido ❌"
            return
        }
        
        const response = await axios.post('/recargas', {
            monto: monto.value,
            metodo: metodo.value
        })

        mensaje.value = "Recarga exitosa ✅"
    } catch (error) {
        mensaje.value = "Error en la recarga ❌"
    }
}
</script>

<template>
    <div class="max-w-md mx-auto mt-10 p-6 bg-white shadow rounded">
        <h2 class="text-xl font-bold mb-4">Recargar saldo</h2>

        <!-- Monto -->
        <div class="mb-4">
            <label class="block text-sm font-medium">Monto</label>
            <input 
                v-model="monto"
                type="number"
                class="w-full border rounded px-3 py-2"
                placeholder="Ingresa el monto"
            />
        </div>

        <!-- Método -->
        <div class="mb-4">
            <label class="block text-sm font-medium">Método de pago</label>
            <select 
                v-model="metodo"
                class="w-full border rounded px-3 py-2"
            >
                <option value="tarjeta">Tarjeta</option>
                <option value="efectivo">Efectivo</option>
            </select>
        </div>

        <!-- Botón -->
        <button 
            @click="recargar"
            class="w-full bg-blue-500 text-white py-2 rounded"
        >
            Recargar
        </button>

        <!-- Mensaje -->
        <p class="mt-4 text-center">
            {{ mensaje }}
        </p>
    </div>
</template>