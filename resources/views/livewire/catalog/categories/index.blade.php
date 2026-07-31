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
            <h2 class="text-xl font-bold text-[#0F172A]">Categories</h2>
            <p class="text-sm text-[#64748B] mt-0.5">Organise your product catalog into categories.</p>
        </div>
        <button wire:click="openCreate"
                class="inline-flex items-center gap-2 bg-[#16A34A] hover:bg-[#15803D] text-white text-sm
                       font-medium px-4 py-2.5 rounded-lg transition-colors shrink-0 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Category
        </button>
    </div>

    {{-- ================================================================
         SEARCH BAR
    ================================================================ --}}
    <div class="bg-white border border-[#E2E8F0] rounded-xl p-4 mb-4 shadow-sm">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#94A3B8]"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Search by name or slug…"
                class="w-full pl-9 pr-4 py-2.5 border border-[#E2E8F0] rounded-lg text-sm
                       focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]"
            >
        </div>
    </div>

    {{-- ================================================================
         TABLE
    ================================================================ --}}
    <div class="bg-white border border-[#E2E8F0] rounded-xl shadow-sm overflow-hidden">
        @if($categories->isEmpty())
            <x-ui.empty-state
                icon="category"
                title="No categories yet"
                description="Create your first category to start organising your product catalog."
            />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-[#E2E8F0] bg-[#F8FAFC]">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Name</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wider hidden sm:table-cell">Slug</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wider hidden md:table-cell">Products</th>
                            <th class="text-center px-5 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-[#64748B] uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @foreach($categories as $category)
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                {{-- Name --}}
                                <td class="px-5 py-3.5">
                                    <span class="font-semibold text-[#0F172A]">{{ $category->name }}</span>
                                    {{-- Show slug below name on mobile --}}
                                    <div class="text-xs text-[#94A3B8] mt-0.5 sm:hidden font-mono">{{ $category->slug }}</div>
                                </td>

                                {{-- Slug (hidden on mobile — shown inline above) --}}
                                <td class="px-5 py-3.5 font-mono text-xs text-[#64748B] hidden sm:table-cell">
                                    {{ $category->slug }}
                                </td>

                                {{-- Product count --}}
                                <td class="px-5 py-3.5 text-center hidden md:table-cell">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[#F1F5F9] text-[#475569] text-xs font-medium">
                                        {{ $category->products_count }}
                                    </span>
                                </td>

                                {{-- Status badge --}}
                                <td class="px-5 py-3.5 text-center">
                                    <x-ui.badge :active="$category->is_active" />
                                </td>

                                {{-- Actions --}}
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-end gap-1">
                                        {{-- Edit --}}
                                        <button wire:click="openEdit({{ $category->id }})"
                                                title="Edit"
                                                class="p-1.5 rounded-md text-[#64748B] hover:text-[#0F172A] hover:bg-[#F1F5F9] transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>

                                        {{-- Toggle active --}}
                                        <button wire:click="toggleActive({{ $category->id }})"
                                                title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}"
                                                class="p-1.5 rounded-md transition-colors
                                                       {{ $category->is_active
                                                           ? 'text-[#64748B] hover:text-amber-600 hover:bg-amber-50'
                                                           : 'text-[#64748B] hover:text-green-600 hover:bg-green-50' }}">
                                            @if($category->is_active)
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

                                        {{-- Delete --}}
                                        <button wire:click="confirmDelete({{ $category->id }})"
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

            {{-- Pagination --}}
            @if($categories->hasPages())
                <div class="px-5 py-4 border-t border-[#E2E8F0]">
                    {{ $categories->links() }}
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
                        <h3 class="text-base font-bold text-[#0F172A]">Delete Category?</h3>
                        <p class="text-sm text-[#64748B] mt-1">
                            Are you sure you want to delete
                            <span class="font-semibold text-[#0F172A]">{{ $confirmDeleteName }}</span>?
                            This action cannot be undone.
                        </p>
                        <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mt-3">
                            Categories with products cannot be deleted. You will be informed if this applies.
                        </p>
                    </div>
                </div>
                <div class="flex gap-3 mt-5">
                    <button wire:click="delete"
                            class="flex-1 bg-[#DC2626] hover:bg-red-700 text-white text-sm font-medium py-2.5 px-4 rounded-lg transition-colors">
                        Delete
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
         Backdrop + panel use Alpine $wire to react to Livewire's showForm.
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
        class="fixed inset-y-0 right-0 z-50 w-full sm:max-w-md bg-white shadow-2xl flex flex-col"
        style="display:none"
    >
        {{-- Panel header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E2E8F0] shrink-0">
            <h2 class="text-base font-bold text-[#0F172A]">
                {{ $editingId ? 'Edit Category' : 'Add Category' }}
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
                    <label for="cat-name" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">
                        Name <span class="text-[#DC2626]">*</span>
                    </label>
                    <input
                        type="text"
                        id="cat-name"
                        wire:model.live.debounce.200ms="formName"
                        placeholder="e.g. Electronics"
                        autocomplete="off"
                        class="w-full px-3 py-3 border border-[#E2E8F0] rounded-lg text-sm
                               focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]
                               @error('formName') border-[#DC2626] @enderror"
                    >
                    @error('formName')
                        <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Slug --}}
                <div>
                    <label for="cat-slug" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">
                        Slug <span class="text-[#DC2626]">*</span>
                    </label>
                    <input
                        type="text"
                        id="cat-slug"
                        wire:model="formSlug"
                        placeholder="e.g. electronics"
                        autocomplete="off"
                        class="w-full px-3 py-3 border border-[#E2E8F0] rounded-lg text-sm font-mono
                               focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]
                               @error('formSlug') border-[#DC2626] @enderror"
                    >
                    <p class="text-xs text-[#94A3B8] mt-1">
                        Lowercase letters, numbers, and hyphens only. Must be unique within your business.
                    </p>
                    @error('formSlug')
                        <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Status toggle --}}
                <div>
                    <label class="block text-xs font-semibold text-[#0F172A] uppercase mb-2">Status</label>
                    <label class="flex items-center gap-3 cursor-pointer w-fit">
                        <input type="checkbox" wire:model="formIsActive"
                               class="w-4 h-4 rounded border-[#E2E8F0] text-[#16A34A] focus:ring-[#16A34A]">
                        <span class="text-sm text-[#0F172A]">Active (visible to customers)</span>
                    </label>
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                    <button type="submit"
                            class="w-full bg-[#16A34A] hover:bg-[#15803D] text-white font-medium py-3 px-4
                                   rounded-lg text-sm transition-colors">
                        {{ $editingId ? 'Update Category' : 'Create Category' }}
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
