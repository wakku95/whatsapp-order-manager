<?php

namespace App\Livewire\Catalog\Categories;

use App\Models\Category;
use App\Services\CategoryService;
use App\Services\TenantContext;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryIndex extends Component
{
    use WithPagination;

    // -------------------------------------------------------------------------
    // Filters & pagination
    // -------------------------------------------------------------------------
    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    // -------------------------------------------------------------------------
    // Form state (shared for create + edit)
    // -------------------------------------------------------------------------
    public bool    $showForm    = false;
    public ?int    $editingId   = null;
    public string  $formName    = '';
    public string  $formSlug    = '';
    public bool    $formIsActive = true;

    // -------------------------------------------------------------------------
    // Delete confirmation
    // -------------------------------------------------------------------------
    public ?int   $confirmDeleteId   = null;
    public string $confirmDeleteName = '';

    // -------------------------------------------------------------------------
    // Flash messages
    // -------------------------------------------------------------------------
    public ?string $successMessage = null;
    public ?string $errorMessage   = null;

    // =========================================================================
    // Lifecycle
    // =========================================================================

    public function mount(): void
    {
        $this->authorize('viewAny', Category::class);
    }

    // =========================================================================
    // Reactive updates
    // =========================================================================

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Auto-generate slug from name when creating a new category.
     * Does not touch the slug when editing (slug is shown pre-filled and user-editable).
     */
    public function updatedFormName(string $value): void
    {
        if ($this->editingId === null) {
            $this->formSlug = Str::slug($value);
        }
    }

    // =========================================================================
    // Form actions
    // =========================================================================

    public function openCreate(): void
    {
        $this->authorize('create', Category::class);
        $this->resetFormFields();
        $this->editingId      = null;
        $this->showForm       = true;
        $this->successMessage = null;
        $this->errorMessage   = null;
    }

    public function openEdit(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->authorize('update', $category);

        $this->editingId      = $id;
        $this->formName       = $category->name;
        $this->formSlug       = $category->slug;
        $this->formIsActive   = (bool) $category->is_active;
        $this->showForm       = true;
        $this->successMessage = null;
        $this->errorMessage   = null;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetFormFields();
    }

    public function save(CategoryService $service): void
    {
        $tenantId = TenantContext::getTenantId();

        // Slug uniqueness is scoped to this business, ignoring the record being edited.
        $uniqueSlugRule = Rule::unique('categories', 'slug')
            ->where('business_id', $tenantId)
            ->ignore($this->editingId);

        $this->validate(
            [
                'formName'    => ['required', 'string', 'max:255'],
                'formSlug'    => [
                    'required', 'string', 'max:255',
                    'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                    $uniqueSlugRule,
                ],
                'formIsActive' => ['boolean'],
            ],
            [
                'formSlug.regex' => 'The slug may only contain lowercase letters, numbers, and hyphens.',
            ],
            [
                'formName'    => 'name',
                'formSlug'    => 'slug',
                'formIsActive' => 'status',
            ]
        );

        $data = [
            'name'      => $this->formName,
            'slug'      => $this->formSlug,
            'is_active' => $this->formIsActive,
        ];

        if ($this->editingId) {
            $category = Category::findOrFail($this->editingId);
            $this->authorize('update', $category);       // Re-checked on save (not just on open)
            $service->update($category, $data);
            $this->successMessage = 'Category updated successfully.';
        } else {
            $this->authorize('create', Category::class); // Re-checked on save
            $service->create($data);
            $this->successMessage = 'Category created successfully.';
        }

        $this->showForm     = false;
        $this->errorMessage = null;
        $this->resetFormFields();
        $this->resetPage();
    }

    // =========================================================================
    // Toggle active
    // =========================================================================

    public function toggleActive(int $id, CategoryService $service): void
    {
        $category = Category::findOrFail($id);
        $this->authorize('update', $category);

        $category             = $service->toggleActive($category);
        $this->successMessage = 'Category ' . ($category->is_active ? 'activated' : 'deactivated') . '.';
        $this->errorMessage   = null;
    }

    // =========================================================================
    // Delete
    // =========================================================================

    public function confirmDelete(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->authorize('delete', $category);

        $this->confirmDeleteId   = $id;
        $this->confirmDeleteName = $category->name;
        $this->errorMessage      = null;
    }

    public function delete(CategoryService $service): void
    {
        if (! $this->confirmDeleteId) {
            return;
        }

        $category = Category::findOrFail($this->confirmDeleteId);
        $this->authorize('delete', $category);           // Re-checked on delete

        try {
            $service->delete($category);
            $this->successMessage = "Category \"{$this->confirmDeleteName}\" deleted.";
            $this->errorMessage   = null;
        } catch (\RuntimeException $e) {
            $this->errorMessage   = $e->getMessage();
            $this->successMessage = null;
        } finally {
            $this->confirmDeleteId   = null;
            $this->confirmDeleteName = '';
        }
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId   = null;
        $this->confirmDeleteName = '';
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    protected function resetFormFields(): void
    {
        $this->formName    = '';
        $this->formSlug    = '';
        $this->formIsActive = true;
        $this->resetValidation();
    }

    // =========================================================================
    // Render
    // =========================================================================

    public function render()
    {
        $categories = Category::query()
            ->withCount('products')
            ->when(
                $this->search,
                fn ($q) => $q->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('slug', 'like', '%' . $this->search . '%');
                })
            )
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.catalog.categories.index', compact('categories'))
            ->layout('layouts.app', ['title' => 'Categories']);
    }
}
