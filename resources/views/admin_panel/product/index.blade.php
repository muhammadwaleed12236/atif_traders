@extends('admin_panel.layout.app')
@section('content')
    <style>
        div.dataTables_wrapper div.dataTables_length select {
            width: 75px !important
        }
    </style>



    <div class="card shadow-sm border-0">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">📦 Product List</h5>
                <small class="text-muted">Manage all products here</small>
            </div>
            <div class="d-flex justify-content-between align-items-end gap-2 flex-wrap">

                {{-- Import / Export Buttons --}}
                <a href="{{ route('products.template') }}"
                   class="btn btn-outline-secondary btn-sm"
                   title="Download blank CSV template">
                    <i class="las la-file-csv"></i> Template
                </a>

                <a href="{{ route('products.export') }}"
                   class="btn btn-success btn-sm"
                   title="Export all products to CSV">
                    <i class="las la-file-download"></i> Export CSV
                </a>

                @if (auth()->user()->can('products.create') || auth()->user()->email === 'admin@admin.com')
                    <button type="button" class="btn btn-warning btn-sm" id="openImportModalBtn"
                            title="Import products from CSV">
                        <i class="las la-file-upload"></i> Import CSV
                    </button>
                    <a href="create_prodcut" class="btn btn-primary btn-sm">
                        <i class="las la-plus"></i> Add Product
                    </a>
                @endif

            </div>
        </div>

    {{-- ══════════════════════════════════════════════════════════
         IMPORT MODAL  (Bootstrap 4 compatible)
    ══════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius:16px; overflow:hidden;">

                {{-- Header --}}
                <div class="modal-header" style="background: linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff;">
                    <div>
                        <h5 class="modal-title fw-bold" id="importModalLabel">
                            <i class="las la-file-upload me-2"></i>Import Products from CSV
                        </h5>
                        <small class="opacity-75">New products will be created. Existing ones (matched by Barcode or Item Code) will be updated.</small>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity:1; font-size:1.5rem; background:none; border:none;">&times;</button>
                </div>

                {{-- Body --}}
                <div class="modal-body p-4">

                    {{-- Instructions --}}
                    <div class="alert alert-info d-flex gap-2 align-items-start mb-4" style="font-size:0.85rem;">
                        <i class="las la-info-circle fs-5 mt-1 flex-shrink-0"></i>
                        <div>
                            <strong>How to use:</strong><br>
                            1. Download the <a href="{{ route('products.template') }}" class="alert-link">CSV Template</a> first.<br>
                            2. Fill in your data in Excel and save as <strong>CSV</strong>.<br>
                            3. Upload here — existing products (matched by Barcode or Item Code) will be <strong>updated</strong>; new rows will be <strong>created</strong>.<br>
                            4. Stock differences will be automatically adjusted and logged.
                        </div>
                    </div>

                    {{-- File Drop Zone --}}
                    <div id="dropZone" class="border border-2 border-dashed rounded-3 text-center p-5 mb-3"
                         style="border-color:#c7d2fe !important; background:#f5f3ff; cursor:pointer; transition: all 0.2s;"
                         onclick="document.getElementById('csvFileInput').click()"
                         ondragover="event.preventDefault(); this.style.background='#ede9fe';"
                         ondragleave="this.style.background='#f5f3ff';"
                         ondrop="handleDrop(event)">
                        <i class="las la-cloud-upload-alt" style="font-size:3rem; color:#7c3aed;"></i>
                        <div class="fw-bold mt-2 text-primary" style="font-size:1rem;">Click to browse or drag & drop CSV file here</div>
                        <div class="text-muted mt-1" id="fileNameDisplay" style="font-size:0.82rem;">No file selected — Max 5 MB</div>
                    </div>
                    <input type="file" id="csvFileInput" class="d-none" accept=".csv,.txt">

                    {{-- Progress --}}
                    <div id="importProgress" class="d-none mb-3">
                        <div class="d-flex align-items-center gap-2 text-primary fw-bold mb-1">
                            <div class="spinner-border spinner-border-sm"></div>
                            Processing your file, please wait...
                        </div>
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width:100%"></div>
                        </div>
                    </div>

                    {{-- Results --}}
                    <div id="importResults" class="d-none">
                        <hr>
                        <h6 class="fw-bold mb-3">📊 Import Results</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-4">
                                <div class="text-center p-3 rounded-3" style="background:#d1fae5;">
                                    <div style="font-size:2rem; font-weight:800; color:#065f46;" id="resultCreated">0</div>
                                    <div class="text-success fw-bold" style="font-size:0.8rem;">CREATED</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-3 rounded-3" style="background:#dbeafe;">
                                    <div style="font-size:2rem; font-weight:800; color:#1e40af;" id="resultUpdated">0</div>
                                    <div class="text-primary fw-bold" style="font-size:0.8rem;">UPDATED</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="text-center p-3 rounded-3" style="background:#fef3c7;">
                                    <div style="font-size:2rem; font-weight:800; color:#92400e;" id="resultSkipped">0</div>
                                    <div class="text-warning fw-bold" style="font-size:0.8rem;">SKIPPED</div>
                                </div>
                            </div>
                        </div>
                        <div id="importErrorsList" class="d-none">
                            <div class="alert alert-warning p-2" style="font-size:0.82rem; max-height:150px; overflow-y:auto;">
                                <strong>⚠ Skipped Rows:</strong>
                                <ul class="mb-0 mt-1" id="errorItemsList"></ul>
                            </div>
                        </div>
                        <div id="importSuccessMsg" class="alert alert-success d-none" style="font-size:0.85rem;">
                            ✅ Import completed successfully! <a href="{{ route('product') }}" class="alert-link">Refresh product list</a>
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="modal-footer bg-light gap-2">
                    <a href="{{ route('products.template') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="las la-download me-1"></i>Download Template
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning fw-bold px-4" id="startImportBtn" disabled
                            style="min-width:130px;">
                        <i class="las la-file-upload me-1"></i> Start Import
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Import JS --}}
    <script>
    $(document).ready(function () {

        var selectedFile = null;

        var fileInput   = document.getElementById('csvFileInput');
        var dropZone    = document.getElementById('dropZone');
        var fileDisplay = document.getElementById('fileNameDisplay');
        var startBtn    = document.getElementById('startImportBtn');
        var progressEl  = document.getElementById('importProgress');
        var resultsEl   = document.getElementById('importResults');

        // ── Open modal via jQuery (Bootstrap 4) ──
        $('#openImportModalBtn').on('click', function () {
            $('#importModal').modal('show');
        });

        // ── File selection ──
        function setFile(file) {
            if (!file || !file.name.match(/\.(csv|txt)$/i)) {
                Swal.fire('Invalid File', 'Please select a valid CSV file.', 'warning');
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire('File Too Large', 'File size exceeds 5 MB limit.', 'warning');
                return;
            }
            selectedFile = file;
            fileDisplay.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            fileDisplay.style.color = '#4f46e5';
            startBtn.disabled = false;
            resultsEl.classList.add('d-none');
        }

        $(fileInput).on('change', function () {
            if (this.files[0]) setFile(this.files[0]);
        });

        window.handleDrop = function (e) {
            e.preventDefault();
            dropZone.style.background = '#f5f3ff';
            if (e.dataTransfer.files[0]) setFile(e.dataTransfer.files[0]);
        };

        // ── Start Import ──
        $('#startImportBtn').on('click', function () {
            if (!selectedFile) return;

            var formData = new FormData();
            formData.append('csv_file', selectedFile);
            formData.append('_token', '{{ csrf_token() }}');

            startBtn.disabled = true;
            progressEl.classList.remove('d-none');
            resultsEl.classList.add('d-none');

            fetch('{{ route("products.import") }}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                progressEl.classList.add('d-none');
                resultsEl.classList.remove('d-none');

                if (!data.success && !data.created && !data.updated) {
                    Swal.fire('Error', data.message || 'Import failed.', 'error');
                    startBtn.disabled = false;
                    return;
                }

                document.getElementById('resultCreated').textContent = data.created  || 0;
                document.getElementById('resultUpdated').textContent = data.updated  || 0;
                document.getElementById('resultSkipped').textContent = data.skipped  || 0;

                var errList  = document.getElementById('errorItemsList');
                var errBlock = document.getElementById('importErrorsList');
                var sucMsg   = document.getElementById('importSuccessMsg');

                errList.innerHTML = '';
                if (data.errors && data.errors.length > 0) {
                    data.errors.forEach(function(msg) {
                        var li = document.createElement('li');
                        li.textContent = msg;
                        errList.appendChild(li);
                    });
                    errBlock.classList.remove('d-none');
                } else {
                    errBlock.classList.add('d-none');
                }

                sucMsg.classList.remove('d-none');
                startBtn.disabled = false;
            })
            .catch(function () {
                progressEl.classList.add('d-none');
                Swal.fire('Server Error', 'Something went wrong. Please try again.', 'error');
                startBtn.disabled = false;
            });
        });

        // ── Reset on modal close ──
        $('#importModal').on('hidden.bs.modal', function () {
            selectedFile = null;
            fileDisplay.textContent = 'No file selected — Max 5 MB';
            fileDisplay.style.color = '';
            startBtn.disabled = true;
            progressEl.classList.add('d-none');
            resultsEl.classList.add('d-none');
            fileInput.value = '';
        });

    });
    </script>

        <div class="card-body">
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    ✅ {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- ── FILTER BAR ── --}}
            <form method="GET" action="{{ route('product') }}" id="filterForm" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.78rem;">🔍 Search</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control form-control-sm"
                            placeholder="Item name or code...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.78rem;">📂 Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.78rem;">🏷 Brand</label>
                        <select name="brand_id" class="form-select form-select-sm">
                            <option value="">All Brands</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold mb-1" style="font-size:0.78rem;">⚡ Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <a href="{{ route('product') }}" class="btn btn-outline-secondary btn-sm w-100">
                            ✕ Clear
                        </a>
                    </div>
                </div>
                @if(request()->hasAny(['search','category_id','brand_id','status']))
                    <div class="mt-2">
                        <small class="text-muted">
                            Showing <strong>{{ $products->total() }}</strong> result(s)
                            @if(request('search')) for "<strong>{{ request('search') }}</strong>" @endif
                        </small>
                    </div>
                @endif
            </form>

            <div class="table-responsive">
                <table id="productTable" class="table table-striped table-bordered align-middle nowrap" style="width:100%">

                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>#</th>
                            <th>Code</th>
                            <th>Image</th>
                            <th>Category</th>
                            <th>Item Name</th>
                            <th>Variants</th>
                            <th>Stock</th>
                            <th>Trade Price</th>
                            <th>Retail Price</th>
                            <th>Brand</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $key => $product)
                            <tr id="product-row-{{ $product->id }}" class="{{ $product->is_active ? '' : 'table-secondary opacity-75' }}">
                                <td><input type="checkbox" class="selectProduct" value="{{ $product->id }}"></td>
                                <td>{{ $key + 1 }}</td>
                                <td class="fw-bold">{{ $product->item_code }}</td>
                                <td>
                                    @if ($product->image)
                                        <img src="{{ asset('uploads/products/' . $product->image) }}" alt="Product"
                                            width="50" height="50" class="rounded border">
                                    @else
                                        <span class="badge bg-secondary">No Img</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $product->category_relation->name ?? '-' }}</strong><br>
                                    <small class="text-muted">{{ $product->sub_category_relation->name ?? '-' }}</small>
                                </td>
                                <td>{{ $product->item_name }}</td>
                                <td>
                                    @if ($product->variants->count() > 0)
                                        @foreach ($product->variants as $v)
                                            <span class="badge bg-light text-dark border me-1" style="font-size:0.75rem;">
                                                {{ $v->name }} ({{ $v->weight_per_piece }}{{ $v->weight_unit }})
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                @php
                                    $stockPieces = (float) ($product->warehouse_stocks_sum_total_pieces ?? 0);
                                    $ppb = $product->pieces_per_box > 0 ? $product->pieces_per_box : 1;
                                    
                                    if (($product->size_mode === 'by_cartons' || $product->size_mode === 'by_size') && $ppb > 1) {
                                        $boxes = floor($stockPieces / $ppb);
                                        $loose = $stockPieces % $ppb;
                                        $stockDisplay = $loose > 0 ? "{$boxes}.{$loose} <small class='text-muted'>(Box.Loose)</small>" : "{$boxes} <small class='text-muted'>Boxes</small>";
                                    } else {
                                        $stockDisplay = "{$stockPieces} <small class='text-muted'>Pcs</small>";
                                    }

                                    // Prices based on mode
                                    $tradePrice = 0;
                                    $retailPrice = 0;
                                    if ($product->size_mode === 'by_size') {
                                        $m2PerPiece = ($product->height * $product->width) / 10000;
                                        $tradePrice = $m2PerPiece * (float)$product->purchase_price_per_m2;
                                        $retailPrice = $m2PerPiece * (float)$product->price_per_m2;
                                    } else {
                                        $tradePrice = (float)$product->purchase_price_per_piece;
                                        $retailPrice = (float)$product->sale_price_per_piece ?: (float)$product->sale_price_per_box; 
                                    }
                                @endphp
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.85rem;">{!! $stockDisplay !!}</span>
                                </td>
                                <td>Rs. {{ number_format($tradePrice, 2) }} <small class="text-muted">/pc</small></td>
                                <td>Rs. {{ number_format($retailPrice, 2) }} <small class="text-muted">/pc</small></td>
                                <td>{{ $product->brand->name ?? '-' }}</td>
                                <td class="text-center">
                                    @if($product->is_active)
                                        <span class="badge bg-success" id="status-badge-{{ $product->id }}">Active</span>
                                    @else
                                        <span class="badge bg-danger" id="status-badge-{{ $product->id }}">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-warning viewProductBtn"
                                        data-id="{{ $product->id }}">
                                        View
                                    </button>

                                    @if (auth()->user()->can('products.edit') || auth()->user()->email === 'admin@admin.com')
                                        <a href="{{ route('products.edit', $product->id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            ✏ Edit
                                        </a>
                                    @endif

                                    <a href="{{ route('generate-barcode-image', $product->id) }}"
                                        class="btn btn-sm btn-outline-success">
                                        🏷 Barcode
                                    </a>

                                    @if (auth()->user()->can('products.edit') || auth()->user()->email === 'admin@admin.com')
                                        <button type="button"
                                            class="btn btn-sm {{ $product->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} toggle-active-btn"
                                            data-id="{{ $product->id }}"
                                            data-active="{{ $product->is_active ? '1' : '0' }}"
                                            data-name="{{ $product->item_name }}"
                                            title="{{ $product->is_active ? 'Deactivate Product' : 'Activate Product' }}">
                                            {{ $product->is_active ? '🔴 Deactivate' : '🟢 Activate' }}
                                        </button>
                                    @endif

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <small class="text-muted">Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }} products</small>
                {{ $products->links() }}
            </div>
        </div>
    </div>

    {{-- add product modal --}}

    <div class="modal fade bd-example-modal-lg" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger">Please use the main "Add Product" page for the new per-m² flow.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Detail View Modal (BS4 Simple 3-Panel) -->
    <div class="modal fade" id="productViewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-sm">

                <!-- Header -->
                <div class="modal-header bg-white border-bottom-0 pb-0">
                    <div>
                        <h5 class="modal-title font-weight-bold text-dark" id="view_item_name">Product Name</h5>
                        <p class="text-muted small mb-0"><i class="las la-barcode"></i> <span
                                id="view_item_code">CODE</span></p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body bg-light p-3">

                    <!-- Loading Spinner -->
                    <div id="modalLoadingSpinner" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="row" id="modalContentRow">

                        <!-- Panel 1: Information -->
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="card h-100 border-0 shadow-sm rounded">
                                <div class="card-body p-3">
                                    <h6 class="text-uppercase text-primary font-weight-bold small mb-3 border-bottom pb-2">
                                        1. Information</h6>

                                    <div class="text-center mb-3">
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto"
                                            style="width: 100px; height: 100px; overflow: hidden; border: 1px solid #eee;">
                                            <img id="view_image_preview" src="" class="img-fluid d-none">
                                            <div id="view_image_placeholder" class="text-center">
                                                <i class="las la-image text-muted" style="font-size: 2rem;"></i>
                                                <small class="d-block text-muted" style="font-size: 10px;">No
                                                    Image</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted d-block">Category</small>
                                        <span class="font-weight-bold text-dark" id="view_cat_sub">-</span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Brand / Model</small>
                                        <span class="font-weight-bold text-dark" id="view_brand_model">-</span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Colors</small>
                                        <span class="text-dark" id="view_color" style="font-size: 0.9rem;">-</span>
                                    </div>

                                    <div class="mb-0 border-top pt-2 mt-2">
                                        <small class="text-muted d-block">Created On</small>
                                        <span class="text-dark small" id="view_created_at">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel 2: Measurement & Stock -->
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="card h-100 border-0 shadow-sm rounded">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                        <h6 class="text-uppercase text-info font-weight-bold small mb-0">2. Measurement
                                        </h6>
                                        <span class="badge badge-secondary" id="view_size_mode_badge">Mode</span>
                                    </div>

                                    <!-- By Size -->
                                    <div id="sec_by_size" class="d-none">
                                        <div class="row no-gutters mb-2">
                                            <div class="col-6 pr-1">
                                                <small class="text-muted d-block">Dim (HxW)</small>
                                                <span class="font-weight-bold text-dark" id="view_dimensions">-</span>
                                            </div>
                                            <div class="col-6 pl-1">
                                                <small class="text-muted d-block">m²/Pc</small>
                                                <span class="font-weight-bold text-dark" id="view_m2_piece">-</span>
                                            </div>
                                        </div>
                                        <div class="bg-light p-2 rounded mb-2 border">
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">Box Qty</small>
                                                <strong class="text-dark" id="view_boxes_qty_size">-</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">Pcs/Box</small>
                                                <strong class="text-dark" id="view_pcs_box_size">-</strong>
                                            </div>
                                        </div>
                                        <div class="text-center mt-2">
                                            <small class="text-muted d-block Uppercase">Total Area (m²)</small>
                                            <div class="h5 font-weight-bold text-info" id="view_total_m2">-</div>
                                        </div>
                                    </div>

                                    <!-- By Box/Carton -->
                                    <div id="sec_packing" class="d-none">
                                        <div class="row text-center mb-2 mx-0">
                                            <div class="col-4 px-1">
                                                <div class="bg-light p-1 rounded border">
                                                    <small class="d-block" style="font-size: 0.6rem;">PCS/BOX</small>
                                                    <strong class="text-dark" id="view_pcs_box">-</strong>
                                                </div>
                                            </div>
                                            <div class="col-4 px-1">
                                                <div class="bg-light p-1 rounded border">
                                                    <small class="d-block" style="font-size: 0.6rem;">BOXES</small>
                                                    <strong class="text-primary" id="view_boxes_qty">-</strong>
                                                </div>
                                            </div>
                                            <div class="col-4 px-1">
                                                <div class="bg-light p-1 rounded border">
                                                    <small class="d-block" style="font-size: 0.6rem;">LOOSE</small>
                                                    <strong class="text-warning" id="view_loose_pcs">-</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- By Piece -->
                                    <div id="sec_by_piece" class="d-none text-center mb-3">
                                        <div class="alert alert-light border">
                                            <i class="las la-layer-group text-primary" style="font-size: 1.5rem;"></i>
                                            <br>
                                            <span class="text-muted small">Unit Tracking Only</span>
                                        </div>
                                    </div>

                                    <!-- Total Stock -->
                                    <div class="mt-auto pt-3 border-top">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted font-weight-bold">TOTAL PCS</small>
                                            <span class="h4 mb-0 font-weight-bold text-success"
                                                id="view_total_stock_qty">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-danger font-weight-bold">ALERT QUANTITY</small>
                                            <span class="h6 mb-0 font-weight-bold text-danger"
                                                id="view_alert_quantity">-</span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Panel 3: Financial -->
                        <div class="col-md-4">
                            <div class="card h-100 border-0 shadow-sm rounded">
                                <div class="card-body p-3">
                                    <h6 class="text-uppercase text-success font-weight-bold small mb-3 border-bottom pb-2">
                                        3. Financials</h6>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="text-muted font-weight-bold" id="lbl_price_unit">Sale
                                                Price</small>
                                            <span class="font-weight-bold text-dark" id="view_price_unit">-</span>
                                        </div>
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-success" style="width: 100%"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="text-muted font-weight-bold" id="lbl_purch_unit">Purch
                                                Price</small>
                                            <span class="text-secondary" id="view_purch_unit">-</span>
                                        </div>
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar bg-secondary" style="width: 60%"></div>
                                        </div>
                                    </div>

                                    <div class="alert alert-success p-2 mb-0 mt-4 mx-0 text-center"
                                        style="background-color: #d1e7dd; border-color: #badbcc;">
                                        <small class="d-block text-success font-weight-bold text-uppercase"
                                            style="font-size: 0.7rem;">Est. Sale Value</small>
                                        <div class="font-weight-bold text-dark h4 mb-0" id="view_sale_total">-</div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">Total Purch: <span id="view_purch_total"
                                                class="text-danger">-</span></small>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Simple Footer -->
                <div class="modal-footer border-top-0 py-2 bg-white rounded-bottom">
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4"
                        data-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>




    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Toggle Active JS --}}
    <script>
        $(document).on('click', '.toggle-active-btn', function () {
            const btn = $(this);
            const productId = btn.data('id');
            const isActive = btn.data('active') == '1';
            const productName = btn.data('name');
            const actionText = isActive ? 'Deactivate' : 'Activate';
            const actionIcon = isActive ? 'warning' : 'success';

            Swal.fire({
                title: actionText + ' Product?',
                html: `<b>${productName}</b><br><small class="text-muted">${isActive ? 'Product will be hidden from Sale/Purchase forms.' : 'Product will be visible in Sale/Purchase forms.'}</small>`,
                icon: actionIcon,
                showCancelButton: true,
                confirmButtonText: 'Yes, ' + actionText,
                confirmButtonColor: isActive ? '#dc3545' : '#28a745',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/product/${productId}/toggle-active`,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function (res) {
                            if (res.success) {
                                const row = $(`#product-row-${productId}`);
                                const badge = $(`#status-badge-${productId}`);

                                if (res.is_active) {
                                    // Activated
                                    row.removeClass('table-secondary opacity-75');
                                    badge.removeClass('bg-danger').addClass('bg-success').text('Active');
                                    btn.removeClass('btn-outline-success').addClass('btn-outline-danger')
                                       .text('🔴 Deactivate').data('active', '1')
                                       .attr('title', 'Deactivate Product');
                                } else {
                                    // Deactivated
                                    row.addClass('table-secondary opacity-75');
                                    badge.removeClass('bg-success').addClass('bg-danger').text('Inactive');
                                    btn.removeClass('btn-outline-danger').addClass('btn-outline-success')
                                       .text('🟢 Activate').data('active', '0')
                                       .attr('title', 'Activate Product');
                                }

                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: res.message,
                                    showConfirmButton: false,
                                    timer: 2500,
                                    timerProgressBar: true,
                                });
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'Could not update product status.', 'error');
                        }
                    });
                }
            });
        });
    </script>

    {{-- product model --}}
    <script>
        $(document).on('click', '.viewProductBtn', function() {
            let productId = $(this).data('id');

            // 1. Reset & Loading State
            $('#modalContentRow').addClass('d-none');
            $('#modalLoadingSpinner').removeClass('d-none');
            $('#productViewModal').modal('show');

            $.ajax({
                url: "/productview/" + productId,
                type: "GET",
                success: function(product) {

                    // 2. Hide Spinner, Show Content
                    $('#modalLoadingSpinner').addClass('d-none');
                    $('#modalContentRow').removeClass('d-none');

                    // --- Basic ---
                    $('#view_item_name').text(product.item_name ?? 'Unknown Product');
                    $('#view_item_code').text(product.item_code ?? 'N/A');
                    $('#view_cat_sub').text((product.category_relation?.name ?? '') + (product
                        .sub_category_relation ? ' • ' + product.sub_category_relation.name : ''
                    ));
                    $('#view_brand_model').text((product.brand?.name ?? '-') + (product.model ? ' / ' +
                        product.model : ''));

                    $('#view_created_at').text(product.created_at ? new Date(product.created_at)
                        .toLocaleDateString() : '-');

                    // --- Image ---
                    if (product.image) {
                        $('#view_image_preview').attr('src', '/uploads/products/' + product.image)
                            .removeClass('d-none');
                        $('#view_image_placeholder').addClass('d-none');
                    } else {
                        $('#view_image_preview').addClass('d-none');
                        $('#view_image_placeholder').removeClass('d-none');
                    }

                    // --- Colors ---
                    if (product.color) {
                        try {
                            let colors = JSON.parse(product.color);
                            $('#view_color').text(Array.isArray(colors) ? colors.join(', ') : colors);
                        } catch (e) {
                            $('#view_color').text(product.color);
                        }
                    } else {
                        $('#view_color').text('-');
                    }

                    // --- Mode & Layout Switching ---
                    let mode = product.size_mode ?? 'by_size';

                    // Defaults
                    $('#sec_by_size, #sec_packing, #sec_by_piece').addClass('d-none');

                    let calcBoxes = product.calculated_boxes_quantity ?? 0;
                    let calcLoose = product.calculated_loose_pieces ?? 0;
                    let calcTotal = product.calculated_total_stock_qty ?? 0;

                    let salePrice = 0;
                    let purchPrice = 0;
                    let estSaleVal = 0;
                    let estPurchVal = 0;

                    if (mode === 'by_size') {
                        $('#view_size_mode_badge').text('By Size').removeClass('bg-info bg-warning')
                            .addClass('bg-light text-primary border-primary');
                        $('#sec_by_size').removeClass('d-none');

                        // Fill Size Data
                        $('#view_dimensions').text((product.height ?? 0) + ' x ' + (product.width ??
                            0));
                        let m2Piece = ((product.height * product.width) / 10000).toFixed(4);
                        $('#view_m2_piece').text(m2Piece);
                        $('#view_boxes_qty_size').text(calcBoxes); // Box count for Size mode
                        $('#view_pcs_box_size').text(product.pieces_per_box ?? 0);
                        $('#view_total_m2').text(parseFloat(product.total_m2 ?? 0).toFixed(2));

                        // Stock
                        $('#view_total_stock_qty').text(calcTotal);

                        // Price Labels
                        $('#lbl_price_unit').text('Price per m²');
                        $('#lbl_purch_unit').text('Cost per m²');
                        salePrice = product.price_per_m2;
                        purchPrice = product.purchase_price_per_m2;

                        estSaleVal = (product.total_m2 ?? 0) * calcBoxes * salePrice;
                        estPurchVal = (product.total_m2 ?? 0) * calcBoxes * purchPrice;

                    } else if (mode === 'by_cartons') {
                        $('#view_size_mode_badge').text('By Box').removeClass(
                            'bg-light text-primary border-primary bg-warning').addClass(
                            'bg-info text-white border-0');
                        $('#sec_packing').removeClass('d-none');

                        $('#view_boxes_qty').text(calcBoxes);
                        $('#view_loose_pcs').text(calcLoose);
                        $('#view_pcs_box').text(product.pieces_per_box ?? '-');

                        // Stock
                        $('#view_total_stock_qty').text(calcTotal);

                        // Price Labels
                        $('#lbl_price_unit').text('Price per Box');
                        $('#lbl_purch_unit').text('Cost per Piece');
                        salePrice = product.sale_price_per_box;
                        purchPrice = product.purchase_price_per_piece;

                        // Calc Value
                        // Sale Value: Boxes * SalePricePerBox + Loose * (SalePricePerBox/PcsPerBox)
                        let ppb = product.pieces_per_box > 0 ? product.pieces_per_box : 1;
                        let pricePerPieceScale = salePrice / ppb;
                        estSaleVal = calcTotal * pricePerPieceScale;
                        estPurchVal = calcTotal * purchPrice;

                    } else { // by_pieces
                        $('#view_size_mode_badge').text('By Piece').removeClass(
                            'bg-light text-primary border-primary bg-info text-white').addClass(
                            'bg-warning text-dark border-0');
                        $('#sec_by_piece').removeClass('d-none');

                        // Stock
                        $('#view_total_stock_qty').text(calcTotal);

                        // Price Labels
                        $('#lbl_price_unit').text('Price per Piece');
                        $('#lbl_purch_unit').text('Cost per Piece');
                        salePrice = product.sale_price_per_box;
                        purchPrice = product.purchase_price_per_piece;

                        estSaleVal = calcTotal * salePrice;
                        estPurchVal = calcTotal * purchPrice;
                    }

                    // Format Financials
                    $('#view_price_unit').text('Rs. ' + parseFloat(salePrice || 0).toFixed(2));
                    $('#view_purch_unit').text('Rs. ' + parseFloat(purchPrice || 0).toFixed(2));
                    $('#view_sale_total').text('Rs. ' + parseFloat(estSaleVal || 0).toLocaleString(
                        'en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                    $('#view_purch_total').text('Rs. ' + parseFloat(estPurchVal || 0).toLocaleString(
                        'en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));

                    $('#view_alert_quantity').text(product.alert_quantity !== null && product.alert_quantity !== undefined ? product.alert_quantity + ' Pcs' : 'Not Set');

                    $('#productViewModal').modal('show');
                },
                error: function() {
                    $('#modalLoadingSpinner').addClass('d-none');
                    Swal.fire('Error', 'Could not fetch details', 'error');
                }
            });
        });
    </script>


    <script>
        $(document).ready(function() {

            // Select/Deselect all checkboxes
            $('#selectAll').click(function() {
                $('.selectProduct').prop('checked', this.checked);
            });

            // On "Create Discount" click
            $('#createDiscountBtn').click(function() {
                var selected = [];
                $('.selectProduct:checked').each(function() {
                    selected.push($(this).val());
                });

                if (selected.length === 0) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Please select at least one product!",

                    });
                    return;
                }

                // Redirect with product IDs as query param
                window.location.href = "{{ route('discount.create') }}" + "?products=" + selected.join(
                    ',');
            });
        });
    </script>

    <script>
        $(document).ready(function() {

            function debounce(func, delay) {
                let timer;
                return function(...args) {
                    clearTimeout(timer);
                    timer = setTimeout(() => func.apply(this, args), delay);
                }
            }

            // DataTable: no built-in search (we use server-side filter bar)
            let table = $('#productTable').DataTable({
                responsive: true,
                paging: false,
                ordering: true,
                info: false,
                order: [[1, 'asc']],
                dom: 'rt',  // only table rows, no search/filter UI
                columnDefs: [{
                    targets: [0, 12],
                    orderable: false,
                    searchable: false
                }]
            });

        });
    </script>

    <!-- DataTables CSS -->
@endsection
<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let cartonQuantityInput = document.getElementById("carton_quantity");
        let piecesPerCartonInput = document.getElementById("pieces_per_carton");
        let initialStockInput = document.getElementById("initial_stock");

        if (cartonQuantityInput && piecesPerCartonInput && initialStockInput) {
            function updateInitialStock() {
                let cartonQuantity = parseInt(cartonQuantityInput.value) || 0;
                let piecesPerCarton = parseInt(piecesPerCartonInput.value) || 0;
                initialStockInput.value = cartonQuantity * piecesPerCarton;
            }

            cartonQuantityInput.addEventListener("input", updateInitialStock);
            piecesPerCartonInput.addEventListener("input", updateInitialStock);
        }
    });


    $(document).ready(function() {
        // Add Product Modal: Fetch Subcategories on Category Change
        $('#categorySelect').change(function() {
            var categoryId = $(this).val();

            $('#subCategorySelect').html('<option value="">Loading...</option>');

            if (categoryId) {
                $.ajax({
                    url: "/get-subcategories/" + categoryId,

                    type: "GET",
                    data: {
                        category_id: categoryId
                    },
                    success: function(data) {
                        $('#subCategorySelect').html(
                            '<option value="">Select Sub-Category</option>');
                        $.each(data, function(key, subCategory) {
                            $('#subCategorySelect').append('<option value="' +
                                subCategory.id + '">' +
                                subCategory.name + '</option>');
                        });
                    },
                    error: function() {
                        alert('Error fetching subcategories.');
                    }
                });
            } else {
                $('#subCategorySelect').html('<option value="">Select Sub-Category</option>');
            }
        });

        // Edit Product Modal: Fetch Subcategories when Category is Changed
        $('#edit_category').change(function() {
            var categoryId = $(this).val();
            $('#edit_sub_category').html('<option value="">Loading...</option>');

            if (categoryId) {
                $.ajax({
                    url: "/get-subcategories/" + categoryId,

                    type: "GET",
                    data: {
                        category_id: categoryId
                    },
                    success: function(data) {
                        $('#edit_sub_category').html(
                            '<option value="">Select Sub-Category</option>');
                        $.each(data, function(key, subCategory) {
                            $('#edit_sub_category').append('<option value="' +
                                subCategory.sub_category_name + '">' +
                                subCategory.sub_category_name + '</option>');
                        });
                    },
                    error: function() {
                        alert('Error fetching subcategories.');
                    }
                });
            } else {
                $('#edit_sub_category').html('<option value="">Select Sub-Category</option>');
            }
        });
    });
</script>
