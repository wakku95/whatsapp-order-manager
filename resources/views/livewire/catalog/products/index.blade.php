<div>
    {{-- ================================================================
         FLASH MESSAGES
    ================================================================ --}}
    @if($successMessage)
        <div x-data="{ show: true }" x-show="show"
             class="flex items-center justify-between gap-3 bg-green-50 border border-green-200 text-green-800
                    text-sm rounded-lg px-4 py-3 mb-5">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ $successMessage }}</span>
            </div>
            <button @click="show=false; $wire.set('successMessage', null)"
                    class="text-green-600 hover:text-green-800 shrink-0" aria-label="Dismiss">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    @if($errorMessage)
        <div x-data="{ show: true }" x-show="show"
             class="flex items-center justify-between gap-3 bg-red-50 border border-red-200 text-red-800
                    text-sm rounded-lg px-4 py-3 mb-5">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $errorMessage }}</span>
            </div>
            <button @click="show=false; $wire.set('errorMessage', null)"
                    class="text-red-600 hover:text-red-800 shrink-0" aria-label="Dismiss">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- ================================================================
         PAGE HEADER
    ================================================================ --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-[#0F172A]">Products</h2>
            <p class="text-sm text-[#64748B] mt-0.5">Manage your catalog — prices, stock, and images.</p>
        </div>
        <button wire:click="openCreate"
                class="inline-flex items-center gap-2 bg-[#16A34A] hover:bg-[#15803D] text-white text-sm
                       font-medium px-4 py-2.5 rounded-lg transition-colors shrink-0 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Product
        </button>
    </div>

    {{-- ================================================================
         FILTERS
    ================================================================ --}}
    <div class="bg-white border border-[#E2E8F0] rounded-xl p-4 mb-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row">
            {{-- Search --}}
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#94A3B8]"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by name or SKU…"
                    class="w-full pl-9 pr-4 py-2.5 border border-[#E2E8F0] rounded-lg text-sm
                           focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]"
                >
            </div>

            {{-- Category filter --}}
            <select wire:model.live="filterCategory"
                    class="py-2.5 pl-3 pr-8 border border-[#E2E8F0] rounded-lg text-sm bg-white
                           focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]
                           text-[#0F172A] sm:w-44">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            {{-- Status filter --}}
            <select wire:model.live="filterStatus"
                    class="py-2.5 pl-3 pr-8 border border-[#E2E8F0] rounded-lg text-sm bg-white
                           focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]
                           text-[#0F172A] sm:w-36">
                <option value="">All statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    {{-- ================================================================
         TABLE
    ================================================================ --}}
    <div class="bg-white border border-[#E2E8F0] rounded-xl shadow-sm overflow-hidden">
        @if($products->isEmpty())
            <x-ui.empty-state
                icon="product"
                title="No products yet"
                description="Add your first product to start building your catalog."
            />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-[#E2E8F0] bg-[#F8FAFC]">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wider w-12"></th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Product</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wider hidden lg:table-cell">Category</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wider hidden md:table-cell">Price</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wider hidden md:table-cell">Stock</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @foreach($products as $product)
                            <tr class="hover:bg-[#F8FAFC] transition-colors">

                                {{-- Thumbnail --}}
                                <td class="px-4 py-3">
                                    @if($product->image_path)
                                        <img
                                            src="{{ Storage::url($product->image_path) }}"
                                            alt="{{ $product->name }}"
                                            class="w-10 h-10 object-cover rounded-lg border border-[#E2E8F0]"
                                        >
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-[#F1F5F9] border border-[#E2E8F0] flex items-center justify-center">
                                            <svg class="w-5 h-5 text-[#CBD5E1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </td>

                                {{-- Name + SKU --}}
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-[#0F172A]">{{ $product->name }}</div>
                                    @if($product->sku)
                                        <div class="text-xs text-[#94A3B8] font-mono mt-0.5">{{ $product->sku }}</div>
                                    @endif
                                    {{-- Show price/stock on mobile below name --}}
                                    <div class="mt-1 flex gap-3 md:hidden text-xs text-[#64748B]">
                                        <span>{{ \App\Services\TenantContext::getTenant()?->currency }} {{ number_format((float)$product->price, 2) }}</span>
                                        <span>Stock: {{ $product->stock }}</span>
                                    </div>
                                </td>

                                {{-- Category --}}
                                <td class="px-4 py-3 text-[#64748B] hidden lg:table-cell">
                                    {{ $product->category?->name ?? '—' }}
                                </td>

                                {{-- Price --}}
                                <td class="px-4 py-3 text-right font-mono text-[#0F172A] hidden md:table-cell">
                                    {{ number_format((float)$product->price, 2) }}
                                </td>

                                {{-- Stock --}}
                                <td class="px-4 py-3 text-right hidden md:table-cell">
                                    @if($product->stock === 0)
                                        <span class="text-[#DC2626] font-semibold">0</span>
                                    @elseif($product->stock <= 5)
                                        <span class="text-amber-600 font-semibold">{{ $product->stock }}</span>
                                    @else
                                        <span class="text-[#0F172A]">{{ $product->stock }}</span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="px-4 py-3 text-center">
                                    <x-ui.badge :active="$product->is_active" />
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button wire:click="openEdit({{ $product->id }})"
                                                title="Edit"
                                                class="p-1.5 rounded-md text-[#64748B] hover:text-[#0F172A] hover:bg-[#F1F5F9] transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>

                                        <button wire:click="toggleActive({{ $product->id }})"
                                                title="{{ $product->is_active ? 'Deactivate' : 'Activate' }}"
                                                class="p-1.5 rounded-md transition-colors
                                                       {{ $product->is_active
                                                           ? 'text-[#64748B] hover:text-amber-600 hover:bg-amber-50'
                                                           : 'text-[#64748B] hover:text-green-600 hover:bg-green-50' }}">
                                            @if($product->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </button>

                                        <button wire:click="confirmDelete({{ $product->id }})"
                                                title="Delete"
                                                class="p-1.5 rounded-md text-[#64748B] hover:text-[#DC2626] hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="px-5 py-4 border-t border-[#E2E8F0]">
                    {{ $products->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- ================================================================
         DELETE CONFIRMATION MODAL
    ================================================================ --}}
    @if($confirmDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
            <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-red-100">
                        <svg class="w-5 h-5 text-[#DC2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-[#0F172A]">Delete Product?</h3>
                        <p class="text-sm text-[#64748B] mt-1">
                            Are you sure you want to delete
                            <span class="font-semibold text-[#0F172A]">{{ $confirmDeleteName }}</span>?
                        </p>
                        <p class="text-xs text-[#64748B] bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg px-3 py-2 mt-3">
                            Products with order history will be <strong>deactivated</strong> instead of deleted to preserve your records.
                        </p>
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button wire:click="delete"
                            class="flex-1 bg-[#DC2626] hover:bg-red-700 text-white text-sm font-medium py-2.5 px-4 rounded-lg transition-colors">
                        Confirm
                    </button>
                    <button wire:click="cancelDelete"
                            class="flex-1 bg-white border border-[#E2E8F0] hover:bg-[#F8FAFC] text-[#0F172A] text-sm font-medium py-2.5 px-4 rounded-lg transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================
         FORM SLIDE-OVER PANEL
    ================================================================ --}}
    {{-- Backdrop --}}
    <div
        x-show="$wire.showForm"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="$wire.closeForm()"
        class="fixed inset-0 z-40 bg-black/30"
        style="display:none"
    ></div>

    {{-- Panel --}}
    <div
        x-show="$wire.showForm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-x-full"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-full"
        class="fixed inset-y-0 right-0 z-50 w-full sm:max-w-lg bg-white shadow-2xl flex flex-col"
        style="display:none"
    >
        {{-- Panel header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E2E8F0] shrink-0">
            <h2 class="text-base font-bold text-[#0F172A]">
                {{ $editingId ? 'Edit Product' : 'Add Product' }}
            </h2>
            <button wire:click="closeForm"
                    class="p-1.5 rounded-md text-[#64748B] hover:text-[#0F172A] hover:bg-[#F1F5F9]"
                    aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Panel body (scrollable) --}}
        <div class="flex-1 overflow-y-auto px-6 py-5">
            <form wire:submit="save" class="space-y-5">

                {{-- Name --}}
                <div>
                    <label for="prod-name" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">
                        Name <span class="text-[#DC2626]">*</span>
                    </label>
                    <input type="text" id="prod-name" wire:model="formName"
                           placeholder="e.g. Wireless Headphones"
                           autocomplete="off"
                           class="w-full px-3 py-3 border border-[#E2E8F0] rounded-lg text-sm
                                  focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]
                                  @error('formName') border-[#DC2626] @enderror">
                    @error('formName') <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Category --}}
                <div>
                    <label for="prod-category" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">Category</label>
                    <select id="prod-category" wire:model="formCategoryId"
                            class="w-full px-3 py-3 border border-[#E2E8F0] rounded-lg text-sm bg-white
                                   focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]
                                   @error('formCategoryId') border-[#DC2626] @enderror">
                        <option value="">— No category —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('formCategoryId') <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- SKU --}}
                <div>
                    <label for="prod-sku" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">SKU</label>
                    <input type="text" id="prod-sku" wire:model="formSku"
                           placeholder="e.g. WH-1000XM5"
                           autocomplete="off"
                           class="w-full px-3 py-3 border border-[#E2E8F0] rounded-lg text-sm font-mono
                                  focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]
                                  @error('formSku') border-[#DC2626] @enderror">
                    <p class="text-xs text-[#94A3B8] mt-1">Optional. Must be unique within your catalog if provided.</p>
                    @error('formSku') <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="prod-desc" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">Description</label>
                    <textarea id="prod-desc" wire:model="formDescription" rows="3"
                              placeholder="Optional product description…"
                              class="w-full px-3 py-3 border border-[#E2E8F0] rounded-lg text-sm resize-y
                                     focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]
                                     @error('formDescription') border-[#DC2626] @enderror"></textarea>
                    @error('formDescription') <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Price + Stock (side by side) --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="prod-price" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">
                            Price <span class="text-[#DC2626]">*</span>
                        </label>
                        <input type="number" id="prod-price" wire:model="formPrice"
                               placeholder="0.00" step="0.01" min="0" max="999999.99"
                               class="w-full px-3 py-3 border border-[#E2E8F0] rounded-lg text-sm
                                      focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]
                                      @error('formPrice') border-[#DC2626] @enderror">
                        @error('formPrice') <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="prod-stock" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">
                            Stock <span class="text-[#DC2626]">*</span>
                        </label>
                        <input type="number" id="prod-stock" wire:model="formStock"
                               placeholder="0" step="1" min="0"
                               class="w-full px-3 py-3 border border-[#E2E8F0] rounded-lg text-sm
                                      focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]
                                      @error('formStock') border-[#DC2626] @enderror">
                        @error('formStock') <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Image upload --}}
                <div>
                    <label class="block text-xs font-semibold text-[#0F172A] uppercase mb-2">Product Image</label>

                    {{-- Preview area --}}
                    <div class="flex items-start gap-4 mb-3">
                        @if($formImage)
                            {{-- Livewire temporary preview --}}
                            <img src="{{ $formImage->temporaryUrl() }}"
                                 alt="New image preview"
                                 class="w-20 h-20 object-cover rounded-lg border border-[#E2E8F0] shrink-0">
                            <div class="text-xs text-[#64748B] mt-1">
                                <p class="font-medium text-[#0F172A]">New image ready</p>
                                <p>{{ $formImage->getClientOriginalName() }}</p>
                                <button type="button" wire:click="$set('formImage', null)"
                                        class="text-[#DC2626] hover:underline mt-1">Remove</button>
                            </div>
                        @elseif($formCurrentImagePath && !$formRemoveImage)
                            {{-- Existing image (edit mode) --}}
                            <img src="{{ Storage::url($formCurrentImagePath) }}"
                                 alt="Current product image"
                                 class="w-20 h-20 object-cover rounded-lg border border-[#E2E8F0] shrink-0">
                            <div class="text-xs text-[#64748B] mt-1">
                                <p class="font-medium text-[#0F172A]">Current image</p>
                                <p>Upload a new file below to replace it.</p>
                                <button type="button" wire:click="removeCurrentImage"
                                        class="text-[#DC2626] hover:underline mt-1">Remove image</button>
                            </div>
                        @elseif($formRemoveImage)
                            <div class="w-20 h-20 rounded-lg bg-[#F1F5F9] border border-dashed border-[#CBD5E1]
                                        flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="text-xs text-[#64748B] mt-1">
                                <p class="text-amber-700">Image will be removed on save.</p>
                                <button type="button" wire:click="$set('formRemoveImage', false)"
                                        class="text-[#16A34A] hover:underline mt-1">Undo</button>
                            </div>
                        @else
                            <div class="w-20 h-20 rounded-lg bg-[#F1F5F9] border border-dashed border-[#CBD5E1]
                                        flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-xs text-[#64748B] mt-1">No image. Upload one below (optional).</p>
                        @endif
                    </div>

                    {{-- File input --}}
                    <input type="file"
                           wire:model="formImage"
                           accept="image/jpeg,image/jpg,image/png,image/webp"
                           class="block w-full text-sm text-[#64748B] border border-[#E2E8F0] rounded-lg cursor-pointer
                                  file:mr-3 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:border-r
                                  file:border-[#E2E8F0] file:text-sm file:font-medium file:bg-[#F8FAFC]
                                  file:text-[#0F172A] hover:file:bg-[#F1F5F9]">
                    <p class="text-xs text-[#94A3B8] mt-1">JPEG, PNG, or WebP. Max 2 MB.</p>
                    @error('formImage') <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span> @enderror

                    {{-- Livewire upload progress --}}
                    <div wire:loading wire:target="formImage" class="mt-2">
                        <div class="h-1.5 bg-[#E2E8F0] rounded-full overflow-hidden">
                            <div class="h-full bg-[#16A34A] rounded-full animate-pulse w-3/4"></div>
                        </div>
                        <p class="text-xs text-[#64748B] mt-1">Uploading…</p>
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-semibold text-[#0F172A] uppercase mb-2">Status</label>
                    <label class="flex items-center gap-3 cursor-pointer w-fit">
                        <input type="checkbox" wire:model="formIsActive"
                               class="w-4 h-4 rounded border-[#E2E8F0] text-[#16A34A] focus:ring-[#16A34A]">
                        <span class="text-sm text-[#0F172A]">Active (show in catalog)</span>
                    </label>
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                    <button type="submit"
                            class="w-full bg-[#16A34A] hover:bg-[#15803D] text-white font-medium py-3 px-4
                                   rounded-lg text-sm transition-colors"
                            wire:loading.attr="disabled" wire:loading.class="opacity-75 cursor-not-allowed">
                        <span wire:loading.remove wire:target="save">
                            {{ $editingId ? 'Update Product' : 'Create Product' }}
                        </span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                    <button type="button" wire:click="closeForm"
                            class="w-full mt-2 border border-[#E2E8F0] bg-white hover:bg-[#F8FAFC] text-[#0F172A]
                                   font-medium py-3 px-4 rounded-lg text-sm transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
