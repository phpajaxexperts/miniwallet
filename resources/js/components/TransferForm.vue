<script setup lang="ts">
import { ref } from 'vue';
import { Loader2, Send } from 'lucide-vue-next';

const emit = defineEmits(['transfer-success']);

import { computed, watch } from 'vue';

const props = defineProps<{
    commissionRate: number;
}>();

const form = ref({
    receiver_id: '',
    amount: '',
});

const loading = ref(false);
const error = ref<string | null>(null);
const success = ref<string | null>(null);

// Computed properties for real-time calculation
const commissionFee = computed(() => {
    const amt = parseFloat(form.value.amount);
    return isNaN(amt) ? 0 : amt * props.commissionRate;
});

const totalDeduction = computed(() => {
    const amt = parseFloat(form.value.amount);
    return isNaN(amt) ? 0 : amt + commissionFee.value;
});

const isValid = computed(() => {
    const amt = parseFloat(form.value.amount);
    return form.value.receiver_id && !isNaN(amt) && amt > 0;
});

// Auto-hide messages
watch([error, success], ([newError, newSuccess]) => {
    if (newError || newSuccess) {
        setTimeout(() => {
            error.value = null;
            success.value = null;
        }, 3000);
    }
});

const submit = async () => {
    if (!isValid.value) return;

    loading.value = true;
    error.value = null;
    success.value = null;

    try {
        await window.axios.post('/api/transactions', {
            receiver_id: form.value.receiver_id,
            amount: form.value.amount,
        });
        success.value = 'Transfer successful!';
        form.value.receiver_id = '';
        form.value.amount = '';
        emit('transfer-success');
    } catch (e: any) {
        error.value = e.response?.data?.message || e.response?.data?.error || 'An error occurred.';
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-100">
        <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
            <Send class="w-5 h-5 text-blue-600" />
            Send Money
        </h2>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Recipient ID</label>
                <input 
                    v-model="form.receiver_id" 
                    type="number" 
                    placeholder="Enter User ID"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                    required
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                    <input 
                        v-model="form.amount" 
                        type="number" 
                        step="0.01" 
                        min="0.01"
                        placeholder="0.00"
                        class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                        required
                    />
                </div>
                <!-- Fee Breakdown -->
                <div v-if="form.amount && parseFloat(form.amount) > 0" class="mt-2 text-xs text-gray-500 space-y-1 bg-gray-50 p-2 rounded">
                    <div class="flex justify-between">
                        <span>Transfer Amount:</span>
                        <span>${{ parseFloat(form.amount).toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Commission ({{ (props.commissionRate * 100).toFixed(1) }}%):</span>
                        <span>${{ commissionFee.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-gray-700 border-t border-gray-200 pt-1 mt-1">
                        <span>Total Deduction:</span>
                        <span>${{ totalDeduction.toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <div v-if="error" class="text-red-500 text-sm p-2 bg-red-50 rounded transition-all duration-300">
                {{ error }}
            </div>

            <div v-if="success" class="text-green-600 text-sm p-2 bg-green-50 rounded transition-all duration-300">
                {{ success }}
            </div>

            <button 
                type="submit" 
                :disabled="loading || !isValid"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <Loader2 v-if="loading" class="w-4 h-4 animate-spin" />
                <span v-else>Transfer Funds</span>
            </button>
        </form>
    </div>
</template>
