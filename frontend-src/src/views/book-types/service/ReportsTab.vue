<template>
  <main class="d-flex flex-1 overflow-hidden mobile:flex-col relative">
    <aside class="col-6  d-flex flex-col overflow-hidden border-right mobile:max-w-full flex-grow mobile:w-full">
      <header class="d-flex h-16 gap-2 align-items-center justify-content-between px-4 py-3 border-bottom">
        <div class="flex-1">
          <input type="date" class="form-control">
        </div>
        <div class="flex-1">
          <div class="relative">
            <input type="search" placeholder="Chek ID, Mijoz nomi" class="form-control">
            <button class="btn btn-neutral top-0 right-0 absolute" title="Clear search"> ✕ </button>
          </div>
        </div>
      </header>
      <section class="flex-1 overflow-y-auto">
        <ul>
          <li> Order item </li>
          <li> Order item </li>
        </ul>
      </section>
    </aside>
    <aside class="col-6 mobile:max-w-full flex-grow mobile:w-full">

      <section class="flex-1 overflow-y-auto p-3 bg-lower h-full">
        <article>
          <p class="text-lg p-10 text-secondary text-center"> 👈 Select any order from list </p>
          
          <div class="card card-body mb-4">
            <header class="mb-4">
              <select name="" class="form-select">
                <option value="">Today - June 6, 2026</option>
                <option value="">Yesterday - June 5, 2026</option>
                <option value="">Last 10 days - From ... - To ...</option>
                <option value="">Last 30 days - From ... - To ...</option>
              </select>
            </header>
            <ul class="d-grid gap-2 grid-template-cols-2 mobile:grid-template-cols-1 lh-sm">
              <li class="card p-2 bg-neutral-100">
                <p class="mb-2">Todal received: </p>
                <strong class="text-xl">0</strong>
              </li>
              <li class="card p-2 bg-neutral-100">
                <p class="mb-2">Orders total cost: </p>
                <strong class="text-xl">0.00 <small class="currency-code">UZS</small></strong>
              </li>
            </ul>

            <hr>
            
            <h5 class="mb-3">By each service</h5>
            <table class="table">
              <thead>
                <tr>
                  <td>Service</td> <td>Ordered</td>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td> Normal cleaning </td>
                  <td> 120 000 usd</td>
                </tr>
                <tr>
                  <td> Super cleaning </td>
                  <td> 120 000 usd</td>
                </tr>
              </tbody>
            </table>
          </div>
        </article>
      </section>
    </aside>
  </main>
</template>

<script setup>
defineProps({
  book: {
    type: Object,
    required: true,
  },
})
</script>
