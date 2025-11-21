<script setup lang="ts">
import { ArrowDownLeft, ArrowUpRight, Clock } from 'lucide-vue-next';

defineProps<{
    transactions: any[];
    userId: number;
}>();

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString();
};
</script>

<template>
    <div class="bg-white rounded-lg shadow-md border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-xl font-semibold flex items-center gap-2">
                <Clock class="w-5 h-5 text-gray-600" />
                Transaction History
            </h2>
        </div>

        <div class="divide-y divide-gray-100">
            <div v-if="transactions.length === 0" class="p-8 text-center text-gray-500">
                No transactions yet.
            </div>

            <div 
                v-for="transaction in transactions" 
                :key="transaction.id" 
                class="p-4 hover:bg-gray-50 transition-colors flex items-center justify-between"
            >
                <div class="flex items-center gap-3">
                    <div 
                        class="w-10 h-10 rounded-full flex items-center justify-center"
                        :class="transaction.sender_id === userId ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'"
                    >
                        <ArrowUpRight v-if="transaction.sender_id === userId" class="w-5 h-5" />
                        <ArrowDownLeft v-else class="w-5 h-5" />
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">
                            {{ transaction.sender_id === userId ? 'Sent to' : 'Received from' }}
                            <span class="font-bold">
                                {{ transaction.sender_id === userId ? transaction.receiver?.name : transaction.sender?.name }}
                            </span>
                        </p>
                        <p class="text-xs text-gray-500">{{ formatDate(transaction.created_at) }}</p>
                    </div>
                </div>

                <div class="text-right">
                    <p 
                        class="font-bold text-lg"
                        :class="transaction.sender_id === userId ? 'text-red-600' : 'text-green-600'"
                    >
                        {{ transaction.sender_id === userId ? '-' : '+' }}${{ Number(transaction.amount).toFixed(2) }}
                    </p>
                    <p v-if="transaction.sender_id === userId" class="text-xs text-gray-400">
                        Fee: ${{ transaction.commission_fee }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
