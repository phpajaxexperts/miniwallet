<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import TransferForm from '@/components/TransferForm.vue';
import TransactionHistory from '@/components/TransactionHistory.vue';
import { Wallet } from 'lucide-vue-next';

defineProps<{
    commissionRate: number;
}>();

const page = usePage();
const user = page.props.auth.user;
const balance = ref(0);
const transactions = ref([]);
const loading = ref(true);

const fetchData = async () => {
    try {
        const response = await window.axios.get('/api/transactions');
        balance.value = response.data.balance;
        transactions.value = response.data.transactions.data;
    } catch (error) {
        console.error('Error fetching data:', error);
    } finally {
        loading.value = false;
    }
};

const handleTransferSuccess = () => {
    // Optimistic update or just fetch fresh data
    fetchData();
};

onMounted(() => {
    fetchData();

    // Listen for real-time updates
    if (window.Echo) {
        window.Echo.private(`user.${user.id}`)
            .listen('.TransactionCompleted', (e: any) => {
                console.log('Transaction event received:', e);
                // Update balance based on who we are
                if (e.transaction.sender_id === user.id) {
                    balance.value = e.sender_balance;
                } else if (e.transaction.receiver_id === user.id) {
                    balance.value = e.receiver_balance;
                }
                
                // Add new transaction to the top of the list
                transactions.value.unshift(e.transaction);
            });
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave(`user.${user.id}`);
    }
});

const breadcrumbs = [
    {
        title: 'Wallet',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="My Wallet" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Balance Card -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center gap-3 mb-2">
                    <Wallet class="w-6 h-6 text-blue-200" />
                    <h2 class="text-lg font-medium text-blue-100">Current Balance</h2>
                </div>
                <div class="text-4xl font-bold">
                    {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(balance) }}
                </div>
                <div class="mt-4 text-blue-200 text-sm">
                    Account ID: #{{ user.id }}
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Transfer Form -->
                <div class="lg:col-span-1">
                    <TransferForm :commission-rate="commissionRate" @transfer-success="handleTransferSuccess" />
                </div>

                <!-- Transaction History -->
                <div class="lg:col-span-2">
                    <TransactionHistory :transactions="transactions" :user-id="user.id" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
