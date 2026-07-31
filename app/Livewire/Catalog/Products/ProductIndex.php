<?php

namespace App\Livewire\Catalog\Products;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ProductIndex extends Component
{
    use WithPagination, WithFileUploads;

    // -------------------------------------------------------------------------
    // Filters & pagination
    // -------------------------------------------------------------------------
    public string $search         = '';
    public string $filterCategory = '';
    public string $filterStatus   = '';

    protected $queryString = [
        'search'         => ['except' => ''],
        'filterCategory' => ['except' => ''],
        'filterStatus'   => ['except' => ''],
    ];

    // -------------------------------------------------------------------------
    // Form state
    // -------------------------------------------------------------------------
    public bool   $showForm  = false;
    public ?int   $editingId = null;

    // Form fields
    public string $formName        = '';
    public string $formSku         = '';
    public string $formDescription = '';
    public string $formPrice       = '';
    public int    $formStock       = 0;
    public bool   $formIsActive    = true;
    public string $formCategoryId  = '';

    // Image handling
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $formImage           = null;
    public ?string $formCurrentImagePath = null;
    public bool    $formRemoveImage      = false;

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
        $this->authorize('viewAny', Product::class);
    }

    // =========================================================================
    // Reactive updates
    // =========================================================================

    public function updatedSearch(): void        { $this->resetPage(); }
    public function updatedFilterCategory(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void   { $this->resetPage(); }

    // =========================================================================
    // Form actions
    // =========================================================================

    public function openCreate(): void
    {
        $this->authorize('create', Product::class);
        $this->resetFormFields();
        $this->editingId      = null;
        $this->showForm       = true;
        $this->successMessage = null;
        $this->errorMessage   = null;
    }

    public function openEdit(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->authorize('update', $product);

        $this->editingId             = $id;
        $this->formName              = $product->name;
        $this->formSku               = $product->sku ?? '';
        $this->formDescription       = $product->description ?? '';
        $this->formPrice             = (string) $product->price;
        $this->formStock             = (int) $product->stock;
        $this->formIsActive          = (bool) $product->is_active;
        $this->formCategoryId        = $product->category_id ? (string) $product->category_id : '';
        $this->formCurrentImagePath  = $product->image_path;
        $this->formImage             = null;
        $this->formRemoveImage       = false;
        $this->showForm              = true;
        $this->successMessage        = null;
        $this->errorMessage          = null;
    }

    public function removeCurrentImage(): void
    {
        $this->formRemoveImage = true;
        $this->formImage       = null;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetFormFields();
    }

    public function save(ProductService $service): void
    {
        $tenantId = TenantContext::getTenantId();

        // Determine SKU rules dynamically.
        // Uniqueness is checked only when SKU is provided (empty SKU = no constraint).
        $skuRules = ['nullable', 'string', 'max:100'];

        $this->validate(
            [
                'formName'        => ['required', 'string', 'max:255'],
                'formSku'         => $skuRules,
                'formDescription' => ['nullable', 'string', 'max:5000'],
                'formPrice'       => ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:999999.99'],
                'formStock'       => ['required', 'integer', 'min:0', 'max:999999'],
                'formIsActive'    => ['boolean'],
                'formCategoryId'  => ['nullable', 'string'],
                'formImage'       => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            ],
            [
                'formPrice.decimal' => 'Price must have at most 2 decimal places.',
                'formImage.max'     => 'Image must not exceed 2 MB.',
                'formImage.mimes'   => 'Image must be a JPEG, PNG, or WebP file.',
            ],
            [
                'formName'        => 'name',
                'formSku'         => 'SKU',
                'formDescription' => 'description',
                'formPrice'       => 'price',
                'formStock'       => 'stock',
                'formCategoryId'  => 'category',
                'formImage'       => 'image',
            ]
        );

        // ---- SKU uniqueness (manually, after normalization) ------------------
        $normalizedSku = trim($this->formSku) !== '' ? trim($this->formSku) : null;

        if ($normalizedSku !== null) {
            // TenantScope is active on Product::, so this query is already scoped
            // to the current business — no additional business_id filter needed.
            $duplicate = Product::where('sku', $normalizedSku)
                ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
                ->exists();

            if ($duplicate) {
                $this->addError('formSku', 'This SKU is already used by another product in your catalog.');
                return;
            }
        }

        // ---- Category cross-tenant validation --------------------------------
        // Category::find() is tenant-scoped via TenantScope. If the ID belongs to
        // another business, it returns null, which fails the validation below.
        $categoryId = null;

        if ($this->formCategoryId !== '') {
            $category = Category::find((int) $this->formCategoryId);
            if (! $category) {
                $this->addError('formCategoryId', 'The selected category is invalid or does not belong to your business.');
                return;
            }
            $categoryId = $category->id;
        }

        $data = [
            'category_id' => $categoryId,
            'name'        => $this->formName,
            'sku'         => $normalizedSku,
            'description' => trim($this->formDescription) !== '' ? trim($this->formDescription) : null,
            'price'       => $this->formPrice,
            'stock'       => $this->formStock,
            'is_active'   => $this->formIsActive,
        ];

        if ($this->editingId) {
            $product = Product::findOrFail($this->editingId);
            $this->authorize('update', $product);        // Re-checked on save
            $service->update($product, $data, $this->formImage ?: null, $this->formRemoveImage);
            $this->successMessage = 'Product updated successfully.';
        } else {
            $this->authorize('create', Product::class);  // Re-checked on save
            $service->create($data, $this->formImage ?: null);
            $this->successMessage = 'Product created successfully.';
        }

        $this->showForm     = false;
        $this->errorMessage = null;
        $this->resetFormFields();
        $this->resetPage();
    }

    // =========================================================================
    // Toggle active
    // =========================================================================

    public function toggleActive(int $id, ProductService $service): void
    {
        $product = Product::findOrFail($id);
        $this->authorize('update', $product);

        $product              = $service->toggleActive($product);
        $this->successMessage = 'Product ' . ($product->is_active ? 'activated' : 'deactivated') . '.';
        $this->errorMessage   = null;
    }

    // =========================================================================
    // Delete
    // =========================================================================

    public function confirmDelete(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->authorize('delete', $product);

        $this->confirmDeleteId   = $id;
        $this->confirmDeleteName = $product->name;
        $this->errorMessage      = null;
    }

    public function delete(ProductService $service): void
    {
        if (! $this->confirmDeleteId) {
            return;
        }

        $product = Product::findOrFail($this->confirmDeleteId);
        $this->authorize('delete', $product);            // Re-checked on delete

        $physicallyDeleted = $service->delete($product);

        if ($physicallyDeleted) {
            $this->successMessage = "Product \"{$this->confirmDeleteName}\" has been deleted.";
        } else {
            $this->successMessage = "Product \"{$this->confirmDeleteName}\" has order history and has been deactivated to preserve order data.";
        }

        $this->errorMessage      = null;
        $this->confirmDeleteId   = null;
        $this->confirmDeleteName = '';
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
        $this->formName             = '';
        $this->formSku              = '';
        $this->formDescription      = '';
        $this->formPrice            = '';
        $this->formStock            = 0;
        $this->formIsActive         = true;
        $this->formCategoryId       = '';
        $this->formImage            = null;
        $this->formCurrentImagePath = null;
        $this->formRemoveImage      = false;
        $this->resetValidation();
    }

    // =========================================================================
    // Render
    // =========================================================================

    public function render()
    {
        // All Category/Product queries below are auto-scoped to the current tenant
        // via TenantScope (BelongsToTenant trait). No manual business_id filtering needed.
        $categories = Category::orderBy('name')->get();

        $products = Product::query()
            ->with('category')
            ->when(
                $this->search,
                fn ($q) => $q->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%');
                })
            )
            ->when(
                $this->filterCategory !== '',
                fn ($q) => $q->where('category_id', (int) $this->filterCategory)
            )
            ->when(
                $this->filterStatus !== '',
                fn ($q) => $q->where('is_active', $this->filterStatus === 'active')
            )
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.catalog.products.index', compact('products', 'categories'))
            ->layout('layouts.app', ['title' => 'Products']);
    }
}
