<template>
  <main class="d-flex flex-1 overflow-hidden mobile:flex-col relative">
    <aside class="col-6 d-flex flex-col overflow-hidden border-right mobile:max-w-full flex-grow mobile:w-full">
      
      <header class="d-flex h-16 gap-2 align-items-center justify-content-between px-4 py-3 border-bottom">
        <div>
          <input type="date" class="form-control">
        </div>
        <div class="flex-grow">
          <div class="relative">
            <input 
              type="search" placeholder="Client name or item name"
              class="form-control"
            >
            <button class="btn btn-neutral top-0 right-0 absolute" title="Clear search"> ✕ </button>
          </div>
        </div>
      </header>
      <section class="flex-1 overflow-y-auto">
        <div class="p-4">
          <a href="#" class="btn btn-primary w-full">Create new order</a>
        </div>

        <ul class="mt-1 mb-10">
          <li class="border-bottom hover:bg-neutral-100">
            <a href="#" role="button" class="px-4 p-3 text-base d-block">
              <div class="d-flex justify-content-between gap-2">
                <div class="mr-1">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt-text-icon lucide-receipt-text"><path d="M13 16H8"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M4 3a1 1 0 0 1 1-1 1.3 1.3 0 0 1 .7.2l.933.6a1.3 1.3 0 0 0 1.4 0l.934-.6a1.3 1.3 0 0 1 1.4 0l.933.6a1.3 1.3 0 0 0 1.4 0l.933-.6a1.3 1.3 0 0 1 1.4 0l.934.6a1.3 1.3 0 0 0 1.4 0l.933-.6A1.3 1.3 0 0 1 19 2a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1 1.3 1.3 0 0 1-.7-.2l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.934.6a1.3 1.3 0 0 1-1.4 0l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-1.4 0l-.934-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-.7.2 1 1 0 0 1-1-1z"/></svg>
                </div>
                <div>
                  <h6>  👤 Name of client • 📞 Phone: +9981234567</h6>
                  <p  class="mt-1 mb-0"> 3 items | <span> 120.00 <small class="currency-code">USD</small></span> </p>
                  <p  class="text-secondary mt-1 mb-0"> Received: Today </p>
                </div>
                <div class="ml-auto text-right">
                  
                  
                  <p class="text-warning badge badge-orange"> Status: ⏳  New received </p>
                </div>
              </div>
            </a>
          </li>

          <li class="border-bottom hover:bg-neutral-100">
            <a href="#" role="button" class="px-4 p-3 text-base d-block">
              <div class="d-flex justify-content-between gap-2">
                <div class="mr-1">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt-text-icon lucide-receipt-text"><path d="M13 16H8"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M4 3a1 1 0 0 1 1-1 1.3 1.3 0 0 1 .7.2l.933.6a1.3 1.3 0 0 0 1.4 0l.934-.6a1.3 1.3 0 0 1 1.4 0l.933.6a1.3 1.3 0 0 0 1.4 0l.933-.6a1.3 1.3 0 0 1 1.4 0l.934.6a1.3 1.3 0 0 0 1.4 0l.933-.6A1.3 1.3 0 0 1 19 2a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1 1.3 1.3 0 0 1-.7-.2l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.934.6a1.3 1.3 0 0 1-1.4 0l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-1.4 0l-.934-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-.7.2 1 1 0 0 1-1-1z"/></svg>
                </div>
                <div>
                  <h6>  👤 Name of client • 📞 Phone: +9981234567</h6>
                  <p  class="mt-1 mb-0"> 3 items | <span> 120.00 <small class="currency-code">USD</small></span> </p>
                  <p  class="text-secondary mt-1 mb-0"> Received: Yesterday </p>
                </div>
                <div class="ml-auto text-right"> 
                  <p class="text-warning badge badge-green"> Status: ✔️ Ready to give  </p>
                </div>
              </div>
            </a>
          </li>

          <li class="border-bottom hover:bg-neutral-100">
            <a href="#" role="button" class="px-4 p-3 text-base d-block">
              <div class="d-flex justify-content-between gap-2">
                <div class="mr-1">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt-text-icon lucide-receipt-text"><path d="M13 16H8"/><path d="M14 8H8"/><path d="M16 12H8"/><path d="M4 3a1 1 0 0 1 1-1 1.3 1.3 0 0 1 .7.2l.933.6a1.3 1.3 0 0 0 1.4 0l.934-.6a1.3 1.3 0 0 1 1.4 0l.933.6a1.3 1.3 0 0 0 1.4 0l.933-.6a1.3 1.3 0 0 1 1.4 0l.934.6a1.3 1.3 0 0 0 1.4 0l.933-.6A1.3 1.3 0 0 1 19 2a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1 1.3 1.3 0 0 1-.7-.2l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.934.6a1.3 1.3 0 0 1-1.4 0l-.933-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-1.4 0l-.934-.6a1.3 1.3 0 0 0-1.4 0l-.933.6a1.3 1.3 0 0 1-.7.2 1 1 0 0 1-1-1z"/></svg>
                </div>
                <div>
                  <h6>  👤 Name of client • 📞 Phone: +9981234567</h6>
                  <p  class="mt-1 mb-0"> 3 items | <span> 120.00 <small class="currency-code">USD</small></span> </p>
                  <p  class="text-secondary mt-1 mb-0"> Received: Yesterday </p>
                </div>
                <div class="ml-auto text-right"> 
                  <p class="text-warning badge badge-orange"> Status: ⏳ Working on </p>
                </div>
              </div>
            </a>
          </li>


        </ul>
      </section>
    </aside>
    <aside class="col-6 mobile:max-w-full flex-grow mobile:w-full">
      <section class="flex-1 overflow-y-auto p-4 bg-lower h-full">
        Content placeholder
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
