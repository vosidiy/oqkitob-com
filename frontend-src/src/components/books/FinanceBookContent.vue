<template>
  <div>
    <h3 class="h6 mb-3">Transactions</h3>

    <div v-if="items.length === 0" class="text-secondary">No transactions yet.</div>

    <div v-else class="table-responsive">
      <table class="table table-sm align-middle mb-0">
        <thead>
          <tr>
            <th scope="col">Date</th>
            <th scope="col">Type</th>
            <th scope="col">Amount</th>
            <th scope="col">Note</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td>{{ item.transaction_date }}</td>
            <td class="text-capitalize">{{ item.type }}</td>
            <td>{{ item.amount }} {{ item.currency_code }}</td>
            <td>{{ item.note || '-' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({
  items: {
    type: Array,
    default: () => [],
  },
})
</script>
