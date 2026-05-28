@extends('admin_panel.layout.app')

@section('content')
    {{-- 
        SUCCESS: Horizontal Compact Layout Redesign for Edit Page
        Matches Create Product UI
    --}}
    
    {{-- External Resources --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-light: #eef2ff;
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius-md: 10px;
            --radius-lg: 16px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
        }

        .page-container {
            max-width: 1350px;
            margin: 0 auto;
            padding: 15px;
        }

        .section-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.06);
            border: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .card-header-pro {
            padding: 12px 20px;
            border-bottom: 1px solid var(--border-color);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title-pro {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .card-body-pro {
            padding: 20px;
        }

        /* --- Form Styling --- */
        .form-label-pro {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 4px;
            letter-spacing: 0.02em;
        }

        .form-control-pro {
            display: block;
            width: 100%;
            padding: 8px 12px;
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--text-main);
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control-pro:focus {
            border-color: var(--primary);
            outline: 0;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-select-pro {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

        /* --- Image Uploader --- */
        .img-uploader {
            width: 100%;
            border: 2px dashed #cbd5e1;
            border-radius: var(--radius-md);
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.2s;
        }

        .img-uploader:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .img-uploader img {
            max-width: 100%;
            object-fit: contain;
            padding: 5px;
        }

        /* Select2 Style tweaks */
        .select2-container--default .select2-selection--multiple {
            border-radius: var(--radius-md) !important;
            border-color: var(--border-color) !important;
            padding: 2px 6px !important;
        }
    </style>

    <div class="page-container">
        
        {{-- Page Title --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('product') }}" class="btn btn-white border shadow-sm rounded-circle p-0" style="width: 36px; height: 36px; display: grid; place-items: center;">
                    <i class="las la-arrow-left"></i>
                </a>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Edit Product</h5>
                    <small class="text-muted" style="font-size:0.8rem;">Update item settings and stock values</small>
                </div>
            </div>
        </div>

        <form id="productForm" action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                
                {{-- MAIN COLUMN: Full Width --}}
                <div class="col-12">
                    
                    {{-- CARD 1: Identity & Categorization --}}
                    <div class="section-card">
                        <div class="card-header-pro">
                            <h5 class="card-title-pro"><i class="las la-tag text-primary"></i> Product Identity</h5>
                        </div>
                        <div class="card-body-pro">
                            <div class="row g-3">
                                
                                {{-- Sub-grid for left content, image on the right --}}
                                <div class="col-md-9">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label-pro">Product Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control-pro fs-6 fw-bold" name="product_name" required value="{{ $product->item_name }}" placeholder="e.g. Ceramic Floor Tile 60x60">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label-pro">Barcode Auto-Gen</label>
                                            <div class="d-flex">
                                                <input type="text" class="form-control-pro" id="barcodeInput" name="barcode_path" value="{{ $product->barcode_path }}" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                                <button type="button" class="btn btn-light border" id="generateBarcodeBtn" style="border-left: 0; border-top-left-radius: 0; border-bottom-left-radius: 0; border-top-right-radius: var(--radius-md); border-bottom-right-radius: var(--radius-md);"><i class="las la-magic"></i></button>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label class="form-label-pro">Category <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-1">
                                                <select class="form-select form-control-pro form-select-pro" id="category-dropdown" name="category_id" required>
                                                    <option value="">Select...</option>
                                                    @foreach ($categories as $cat)
                                                        <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-light border px-2 shadow-sm" data-toggle="modal" data-target="#categoryModal">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label-pro">Sub Category</label>
                                            <div class="d-flex gap-1">
                                                <select class="form-select form-control-pro form-select-pro" id="subcategory-dropdown" name="sub_category_id">
                                                    <option value="">Select...</option>
                                                    @foreach ($subcategories as $subCat)
                                                        @if ($subCat->category_id == $product->category_id)
                                                            <option value="{{ $subCat->id }}" {{ $product->sub_category_id == $subCat->id ? 'selected' : '' }}>{{ $subCat->name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-light border px-2 shadow-sm" data-toggle="modal" data-target="#subcategoryModal">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label-pro">Brand</label>
                                            <div class="d-flex gap-1">
                                                <select class="form-select form-control-pro form-select-pro" name="brand_id" required>
                                                    <option value="">Select...</option>
                                                    @foreach ($brands as $brand)
                                                        <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-light border px-2 shadow-sm" data-toggle="modal" data-target="#brandModal">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label-pro">Unit (UOM) <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-1">
                                                <select class="form-select form-control-pro form-select-pro" name="unit" id="unitDropdown" required>
                                                    <option value="">Select...</option>
                                                    @foreach ($units as $unit)
                                                        <option value="{{ $unit->id }}" {{ $product->unit_id == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-light border px-2 shadow-sm" data-toggle="modal" data-target="#unitModal">+</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Image Uploader side-by-side (Right) --}}
                                <div class="col-md-3">
                                    <label class="form-label-pro">Product Image</label>
                                    <input type="file" id="imageInput" name="image" class="d-none" accept="image/*">
                                    <div class="img-uploader" style="height: 110px;" onclick="document.getElementById('imageInput').click()">
                                        <button type="button" id="clearImageBtn" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 {{ $product->image ? '' : 'd-none' }} rounded-circle" style="width:20px;height:20px;padding:0;z-index:10; font-size:12px; line-height:1;">&times;</button>
                                        @if($product->image)
                                            <img id="preview" src="{{ asset('uploads/products/' . $product->image) }}" style="max-height: 100px;">
                                            <div id="uploadPlaceholder" class="text-center p-2 d-none">
                                                <i class="las la-camera fs-3 text-primary"></i>
                                                <div class="fw-bold" style="font-size: 11px;">Upload</div>
                                            </div>
                                        @else
                                            <img id="preview" class="d-none" style="max-height: 100px;">
                                            <div id="uploadPlaceholder" class="text-center p-2">
                                                <i class="las la-camera fs-3 text-primary"></i>
                                                <div class="fw-bold" style="font-size: 11px;">Upload</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label-pro">Model / Series</label>
                                    <input type="text" class="form-control-pro" name="model" value="{{ $product->model }}" placeholder="Optional">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-pro">Colors</label>
                                    <select class="form-control-pro" name="color[]" id="color-select" multiple="multiple" style="width: 100%">
                                        @php
                                            $colors = is_string($product->color) ? json_decode($product->color, true) : $product->color ?? [];
                                            if (!is_array($colors)) {
                                                $colors = [];
                                            }
                                        @endphp
                                        <option value="Black" {{ in_array('Black', $colors) ? 'selected' : '' }}>Black</option>
                                        <option value="White" {{ in_array('White', $colors) ? 'selected' : '' }}>White</option>
                                        <option value="Red" {{ in_array('Red', $colors) ? 'selected' : '' }}>Red</option>
                                        <option value="Blue" {{ in_array('Blue', $colors) ? 'selected' : '' }}>Blue</option>
                                        <option value="Beige" {{ in_array('Beige', $colors) ? 'selected' : '' }}>Beige</option>
                                        @foreach ($colors as $c)
                                            @if (!in_array($c, ['Black', 'White', 'Red', 'Blue', 'Beige']))
                                                <option value="{{ $c }}" selected>{{ $c }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- CARD 2: Stock Specifications & Pricing --}}
                    <div class="section-card">
                        <div class="card-header-pro d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <h5 class="card-title-pro"><i class="las la-box-open text-info"></i> Stock & Pricing</h5>
                            
                            <div class="d-flex align-items-center flex-wrap gap-3">
                                <div class="bg-light px-3 py-1 rounded border d-flex gap-3 text-nowrap align-items-center" style="font-size: 0.82rem;">
                                    <div>
                                        <span class="text-muted fw-bold">Total Stock:</span>
                                        <strong class="text-primary fs-6" id="total_stock_display">0</strong> <span class="text-muted small">pcs</span>
                                    </div>
                                    <div class="border-start ps-3">
                                        <span class="text-muted fw-bold">Est. Value:</span>
                                        <span class="text-success fw-bold">PKR <span id="sale_total_display">0.00</span></span>
                                    </div>
                                </div>

                                {{-- Switcher --}}
                                <div class="btn-group btn-group-sm" role="group">
                                    <input type="radio" class="btn-check" name="size_mode" id="mode_carton" value="by_cartons" {{ $product->size_mode == 'by_cartons' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary px-3 fw-bold" for="mode_carton"><i class="las la-box me-1"></i> Carton</label>

                                    <input type="radio" class="btn-check" name="size_mode" id="mode_piece" value="by_pieces" {{ $product->size_mode == 'by_pieces' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary px-3 fw-bold" for="mode_piece"><i class="las la-puzzle-piece me-1"></i> Piece</label>
                                    
                                    {{-- BY SIZE SWITCHER HIDE / COMMENTED --}}
                                    <input type="radio" class="d-none" name="size_mode" id="mode_size" value="by_size" {{ $product->size_mode == 'by_size' || !$product->size_mode ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>

                        <div class="card-body-pro">
                            <div class="row g-3">
                                
                                {{-- Dynamic Stock Fields --}}
                                <div class="col-md-12">
                                    <div class="row g-3">
                                        {{-- Carton Mode Fields --}}
                                        <div class="col-md-3 group-by-size">
                                            <label class="form-label-pro">Pcs / Carton</label>
                                            <input type="number" class="form-control-pro" name="pieces_per_box" id="pieces_per_box" value="{{ $product->pieces_per_box }}" placeholder="0">
                                        </div>
                                        <div class="col-md-3 group-by-size">
                                            <label class="form-label-pro">In-Stock Cartons</label>
                                            <input type="number" class="form-control-pro border-primary text-primary fw-bold" name="boxes_quantity" id="boxes_quantity" value="{{ $product->boxes_quantity }}" placeholder="0">
                                        </div>
                                        <div class="col-md-3 group-loose">
                                            <label class="form-label-pro text-warning">Loose Pieces (Extra)</label>
                                            <input type="number" class="form-control-pro border-warning" name="loose_pieces" id="loose_pieces" value="{{ $product->loose_pieces }}" placeholder="0">
                                        </div>

                                        {{-- Piece Mode Fields --}}
                                        <div class="col-md-6 group-piece-only d-none">
                                            <label class="form-label-pro text-primary">Total In-Stock Quantity (Pieces)</label>
                                            <input type="number" class="form-control-pro border-primary text-primary fw-bold" name="piece_quantity" id="piece_quantity" value="{{ $product->piece_quantity }}" placeholder="0">
                                        </div>

                                        {{-- Common Alert Quantity --}}
                                        <div class="col-md-3">
                                            <label class="form-label-pro text-danger">Alert Quantity (Min Stock Pcs)</label>
                                            <input type="number" class="form-control-pro border-danger" name="alert_quantity" id="alert_quantity" min="0" value="{{ $product->alert_quantity }}" placeholder="e.g. 10">
                                        </div>
                                    </div>
                                </div>

                                {{-- Pricing Group --}}
                                <div class="col-md-12 pt-3 border-top">
                                    <h6 class="form-label-pro text-primary mb-2">Unit Price Settings (Rs.)</h6>
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label-pro text-success">Sale Price <span class="unit-label text-muted fw-normal">(pc)</span></label>
                                            <input type="number" class="form-control-pro fw-bold text-success" name="sale_price_per_box" id="sale_price_per_box" step="0.01" value="{{ $product->sale_price_per_piece }}" placeholder="0.00">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label-pro text-secondary">Purchase Price <span class="unit-label text-muted fw-normal">(pc)</span></label>
                                            <input type="number" class="form-control-pro text-muted" name="purchase_price_per_piece" id="purchase_price_per_piece" step="0.01" value="{{ $product->purchase_price_per_piece }}" placeholder="0.00">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label-pro">Sale Disc (%)</label>
                                            <input type="number" class="form-control-pro" name="sale_discount_percent" step="0.01" value="{{ $product->sale_discount_percent ?? 0 }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label-pro">Purch Disc (%)</label>
                                            <input type="number" class="form-control-pro" name="purchase_discount_percent" step="0.01" value="{{ $product->purchase_discount_percent ?? 0 }}">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- CARD 3: Variants (Auto-generated based on UOM) --}}
                    <div class="section-card">
                        <div class="card-header-pro">
                            <h5 class="card-title-pro"><i class="las la-layer-group text-success"></i> Variants <small class="text-muted fw-normal" style="font-size:0.7rem;">(Auto-generated based on selected Unit — adjust as needed)</small></h5>
                        </div>
                        <div class="card-body-pro">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-3" id="variantsTable" style="font-size:0.85rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:20%;">Variant Name</th>
                                            <th style="width:12%;">Wt / Piece</th>
                                            <th style="width:8%;">Unit</th>
                                            <th style="width:14%;">Sale Price <span class="text-danger">*</span></th>
                                            <th style="width:14%;">Purchase Price <span class="text-danger">*</span></th>
                                            <th style="width:10%;">Alert Qty</th>
                                            <th style="width:10%;">Barcode</th>
                                            <th style="width:4%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="variantsBody">
                                        @forelse ($product->variants as $variant)
                                            <tr class="variant-row">
                                                <td>
                                                    <input type="text" class="form-control form-control-sm variant-name-input" name="variant_name[]" value="{{ $variant->name }}">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm variant-weight" name="variant_weight[]" step="0.01" min="0" value="{{ $variant->weight_per_piece }}" placeholder="1">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm variant-unit-display" name="variant_weight_unit[]" value="{{ $variant->weight_unit }}" readonly style="background:#f8f9fa;">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm variant-sale-price" name="variant_sale_price[]" step="0.01" min="0" value="{{ $variant->sale_price }}" placeholder="0.00">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm variant-purchase-price" name="variant_purchase_price[]" step="0.01" min="0" value="{{ $variant->purchase_price }}" placeholder="0.00">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" name="variant_alert_quantity[]" min="0" value="{{ $variant->alert_quantity }}" placeholder="0">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="variant_barcode[]" value="{{ $variant->barcode }}" placeholder="Auto">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-variant"><i class="las la-trash"></i></button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="variant-row">
                                                <td>
                                                    <input type="text" class="form-control form-control-sm variant-name-input" name="variant_name[]" placeholder="Auto from product + unit">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm variant-weight" name="variant_weight[]" step="0.01" min="0" placeholder="1">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm variant-unit-display" name="variant_weight_unit[]" value="Pcs" readonly style="background:#f8f9fa;width:60px;">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm variant-sale-price" name="variant_sale_price[]" step="0.01" min="0" placeholder="0.00">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm variant-purchase-price" name="variant_purchase_price[]" step="0.01" min="0" placeholder="0.00">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" name="variant_alert_quantity[]" min="0" placeholder="0">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="variant_barcode[]" placeholder="Auto">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-variant" disabled><i class="las la-trash"></i></button>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-outline-success btn-sm" id="addVariantBtn">
                                <i class="las la-plus"></i> Add Variant
                            </button>
                            <small class="text-muted ms-2">Variants auto-generate when you select a Unit. You can edit weights, prices, and add/remove rows.</small>
                        </div>
                    </div>

                    {{-- INLINE ACTIONS ROW --}}
                    <div class="d-flex justify-content-end align-items-center bg-white p-3 rounded shadow-sm border mb-4 gap-2">
                        <a href="{{ route('product') }}" class="btn btn-outline-secondary px-4 py-2" style="border-radius: var(--radius-md); font-size: 0.9rem;">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold" style="background: var(--primary); border: none; border-radius: var(--radius-md); font-size: 0.9rem;">
                            <i class="las la-check-circle me-1"></i> UPDATE PRODUCT
                        </button>
                    </div>

                </div>

            </div>

            {{-- HIDDEN BY-SIZE FORM CONTROLS FOR MIGRATION BACKWARD COMPATIBILITY --}}
            <div style="display:none !important;">
                <input type="number" name="height" id="height" step="0.01" value="{{ $product->height }}">
                <input type="number" name="width" id="width" step="0.01" value="{{ $product->width }}">
                <input type="number" name="price_per_m2" id="price_per_m2" step="0.01" value="{{ $product->price_per_m2 }}">
                <input type="number" name="purchase_price_per_m2" id="purchase_price_per_m2" step="0.01" value="{{ $product->purchase_price_per_m2 }}">
            </div>

        </form>

        {{-- Modals --}}
        <div id="categoryModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-md);">
                    <form action="{{ route('store.category') }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h6 class="modal-title fw-bold">New Category</h6>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="mb-3">
                                <label class="form-label-pro">Category Name</label>
                                <input type="text" name="name" class="form-control-pro" required placeholder="e.g. Ceramics">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill">Create Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="subcategoryModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-md);">
                    <form action="{{ route('store.subcategory') }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h6 class="modal-title fw-bold">New Subcategory</h6>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="mb-3">
                                <label class="form-label-pro">Parent Category</label>
                                <select name="category_id" class="form-select form-control-pro">
                                    @foreach ($categories as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label-pro">Name</label>
                                <input type="text" name="name" class="form-control-pro" required placeholder="e.g. Floor Tiles">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill">Create Subcategory</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Unit Modal --}}
        <div id="unitModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-md);">
                    <form id="unitForm" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h6 class="modal-title fw-bold">New Unit of Measurement</h6>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label-pro">Unit Name</label>
                                <input type="text" name="name" class="form-control-pro" required placeholder="e.g. Pieces, Carton, Kg">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill" id="saveUnitBtn">Create Unit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Brand Modal --}}
        <div id="brandModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-md);">
                    <form action="{{ route('store.Brand') }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h6 class="modal-title fw-bold">New Brand</h6>
                            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="page" value="product_page">
                            <div class="mb-3">
                                <label class="form-label-pro">Brand Name</label>
                                <input type="text" name="name" class="form-control-pro" required placeholder="e.g. Johnson">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill">Create Brand</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- UI Elements ---
            const form = document.getElementById('productForm');
            const modeRadios = document.querySelectorAll('input[name="size_mode"]');

            // Containers
            const grpBySize = document.querySelectorAll('.group-by-size');
            const grpLoose = document.querySelectorAll('.group-loose');
            const grpPieceOnly = document.querySelectorAll('.group-piece-only');

            function toggleGroup(els, hide) {
                els.forEach(el => hide ? el.classList.add('d-none') : el.classList.remove('d-none'));
            }

            // Labels
            const unitLabels = document.querySelectorAll('.unit-label');

            // --- Logic Update Mode ---
            function updateMode() {
                const modeEl = document.querySelector('input[name="size_mode"]:checked');
                if(!modeEl) return;
                const mode = modeEl.value;

                // Sync UI Switcher labels highlight
                modeRadios.forEach(r => {
                    const lbl = document.querySelector(`label[for="${r.id}"]`);
                    if(lbl) {
                        if(r.checked) {
                            lbl.classList.remove('btn-outline-primary');
                            lbl.classList.add('btn-primary');
                        } else {
                            lbl.classList.remove('btn-primary');
                            lbl.classList.add('btn-outline-primary');
                        }
                    }
                });

                // Hide ALL dynamic wrappers
                toggleGroup(grpBySize, true);
                toggleGroup(grpLoose, true);
                toggleGroup(grpPieceOnly, true);

                if (mode === 'by_cartons') {
                    toggleGroup(grpBySize, false);
                    toggleGroup(grpLoose, false);

                    setRequired(['pieces_per_box', 'boxes_quantity', 'sale_price_per_box', 'purchase_price_per_piece'], true);
                    setRequired(['piece_quantity'], false);

                } else if (mode === 'by_pieces') {
                    toggleGroup(grpPieceOnly, false);

                    setRequired(['piece_quantity', 'sale_price_per_box', 'purchase_price_per_piece'], true);
                    setRequired(['pieces_per_box', 'boxes_quantity'], false);
                }

                calculate();
            }

            function setRequired(ids, isReq) {
                ids.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) isReq ? el.setAttribute('required', 'required') : el.removeAttribute('required');
                });
            }

            function calculate() {
                const modeEl = document.querySelector('input[name="size_mode"]:checked');
                if(!modeEl) return;
                const mode = modeEl.value;

                const v = (id) => parseFloat(document.getElementById(id)?.value) || 0;
                let stock = 0;
                let saleVal = 0;

                if (mode === 'by_cartons') {
                    stock = (v('pieces_per_box') * v('boxes_quantity')) + v('loose_pieces');
                    saleVal = stock * v('sale_price_per_box');

                } else if (mode === 'by_pieces') {
                    stock = v('piece_quantity');
                    saleVal = stock * v('sale_price_per_box');
                }

                setText('total_stock_display', stock);
                setText('sale_total_display', saleVal.toLocaleString(undefined, { minimumFractionDigits: 2 }));
            }

            function setText(id, val) {
                const el = document.getElementById(id);
                if (el) el.innerText = val;
            }

            // Events
            modeRadios.forEach(r => r.addEventListener('change', function() {
                // Do NOT resetInputs() on edit page - keep pre-saved details intact unless modified!
                updateMode();
            }));
            form.querySelectorAll('input').forEach(i => i.addEventListener('input', calculate));

            updateMode();

            // Image Handler
            const imgInput = document.getElementById('imageInput');
            const preview = document.getElementById('preview');
            const ph = document.getElementById('uploadPlaceholder');
            const clr = document.getElementById('clearImageBtn');

            imgInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const r = new FileReader();
                    r.onload = (e) => {
                        preview.src = e.target.result;
                        preview.classList.remove('d-none');
                        if(ph) ph.classList.add('d-none');
                        clr.classList.remove('d-none');
                    };
                    r.readAsDataURL(this.files[0]);
                }
            });

            clr.addEventListener('click', (e) => {
                e.stopPropagation();
                imgInput.value = '';
                
                // For Edit: Revert to original if exists
                @if($product->image)
                    preview.src = "{{ asset('uploads/products/' . $product->image) }}";
                    preview.classList.remove('d-none');
                    if(ph) ph.classList.add('d-none');
                @else
                    preview.classList.add('d-none');
                    if(ph) ph.classList.remove('d-none');
                    clr.classList.add('d-none');
                @endif
            });

            // AJAX Submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.querySelector('button[type="submit"]');
                const originalContent = btn.innerHTML;
                btn.innerHTML = '<i class="las la-spinner la-spin"></i> Updating...';
                btn.disabled = true;

                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST', // Method POST because we use _method=PUT in formData
                    headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
                    body: formData
                })
                .then(r => r.json().then(data => ({status: r.status, body: data})))
                .then(({status, body}) => {
                    if (status === 200 || body.status === 'success') {
                         Swal.fire({
                            icon: 'success', title: 'Updated!',
                            text: 'Product updated successfully', timer: 1500, showConfirmButton: false
                        }).then(() => window.location.href = "{{ route('product') }}");
                    } else {
                        const msg = body.errors ? Object.values(body.errors).flat().join('<br>') : (body.message || 'Error');
                        Swal.fire({icon: 'error', title: 'Error', html: msg});
                    }
                })
                .catch(err => Swal.fire({icon: 'error', title: 'Error', text: 'Server Error'}))
                .finally(() => {
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                });
            });

            // Barcode
            const barIn = document.getElementById('barcodeInput');
            const barBtn = document.getElementById('generateBarcodeBtn');
            const barcodeUrl = '{{ route('generate-barcode-image') }}';
            
            barBtn.addEventListener('click', () => fetch(barcodeUrl).then(r => r.json()).then(d => barIn.value = d.barcode_number));

            // ===== Unit Modal: Create Unit via AJAX =====
            $('#unitForm').on('submit', function(e) {
                e.preventDefault();
                location.reload();
            });

            // ===== Auto-generate variants based on Unit & Product Name =====
            const unitDropdown = document.getElementById('unitDropdown');
            const productNameInput = document.querySelector('input[name="product_name"]');
            const variantsBody = document.getElementById('variantsBody');

            const unitNames = {};
            @foreach ($units as $unit)
                unitNames[{{ $unit->id }}] = '{{ $unit->name }}';
            @endforeach

            function getUnitName(id) {
                return unitNames[id] || '';
            }

            function autoGenerateVariants() {
                const unitId = unitDropdown.value;
                const unitName = getUnitName(unitId);
                const prodName = productNameInput.value.trim() || 'Product';

                if (!unitId || !unitName) return;

                const unitLower = unitName.toLowerCase();

                // Auto-set size_mode based on unit
                const sizeModeInputs = document.querySelectorAll('input[name="size_mode"]');
                let targetMode = 'by_pieces';
                if (unitLower === 'carton' || unitLower === 'cartons' || unitLower === 'ctn' || unitLower === 'box' || unitLower === 'boxes') {
                    targetMode = 'by_cartons';
                } else if (unitLower === 'pieces' || unitLower === 'pcs' || unitLower === 'piece') {
                    targetMode = 'by_pieces';
                } else {
                    targetMode = 'by_pieces';
                }
                sizeModeInputs.forEach(input => {
                    input.checked = (input.value === targetMode);
                    const evt = new Event('change');
                    input.dispatchEvent(evt);
                });

                // Only auto-generate if no variants exist (first time or unit changed)
                const existingRows = variantsBody.querySelectorAll('.variant-row');
                if (existingRows.length > 0) {
                    return;
                }

                variantsBody.innerHTML = '';

                if (unitLower === 'pieces' || unitLower === 'pcs' || unitLower === 'piece') {
                    addVariantRow(prodName, 1, 'Pcs');
                }
                else if (unitLower === 'carton' || unitLower === 'cartons' || unitLower === 'ctn' || unitLower === 'box' || unitLower === 'boxes') {
                    addVariantRow(prodName + ' CTN', 12, 'Pcs');
                    addVariantRow(prodName + ' PCS', 1, 'Pcs');
                }
                else if (unitLower === 'kg' || unitLower === 'kilogram' || unitLower === 'kgs') {
                    addVariantRow(prodName, 1, 'Kg');
                }
                else if (unitLower === 'gram' || unitLower === 'g' || unitLower === 'grams') {
                    addVariantRow(prodName, 1, 'g');
                }
                else if (unitLower === 'meter' || unitLower === 'm' || unitLower === 'meters') {
                    addVariantRow(prodName, 1, 'm');
                }
                else if (unitLower === 'liter' || unitLower === 'l' || unitLower === 'litre') {
                    addVariantRow(prodName, 1, 'L');
                }
                else {
                    addVariantRow(prodName, 1, unitName);
                }

                const rows = variantsBody.querySelectorAll('.variant-row');
                rows.forEach((row, i) => {
                    const rm = row.querySelector('.remove-variant');
                    rm.disabled = (i === 0);
                });
            }

            function addVariantRow(name, weight, unit) {
                const row = document.createElement('tr');
                row.className = 'variant-row';
                row.innerHTML = `
                    <td>
                        <input type="text" class="form-control form-control-sm variant-name-input" name="variant_name[]" value="${name}" placeholder="Variant name">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm variant-weight" name="variant_weight[]" step="0.01" min="0" value="${weight}" placeholder="1">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm variant-unit-display" name="variant_weight_unit[]" value="${unit}" readonly style="background:#f8f9fa;width:60px;">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm variant-sale-price" name="variant_sale_price[]" step="0.01" min="0" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm variant-purchase-price" name="variant_purchase_price[]" step="0.01" min="0" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm" name="variant_alert_quantity[]" min="0" placeholder="0">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="variant_barcode[]" placeholder="Auto">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-variant"><i class="las la-trash"></i></button>
                    </td>
                `;
                const rmBtn = row.querySelector('.remove-variant');
                rmBtn.addEventListener('click', function() {
                    row.remove();
                });
                variantsBody.appendChild(row);
            }

            // ===== Auto-calculate variant prices from base rate × weight =====
            function calculateVariantPrices() {
                const baseSale = parseFloat(document.getElementById('sale_price_per_box').value) || 0;
                const basePurch = parseFloat(document.getElementById('purchase_price_per_piece').value) || 0;
                const unitName = getUnitName(unitDropdown.value).toLowerCase();
                const rows = variantsBody.querySelectorAll('.variant-row');
                if (!rows.length) return;

                const isCarton = unitName === 'carton' || unitName === 'cartons' || unitName === 'ctn' || unitName === 'box' || unitName === 'boxes';

                rows.forEach((row, i) => {
                    const weight = parseFloat(row.querySelector('.variant-weight').value) || 1;
                    const saleInput = row.querySelector('.variant-sale-price');
                    const purchInput = row.querySelector('.variant-purchase-price');

                    if (isCarton) {
                        if (i === 0) {
                            saleInput.value = baseSale.toFixed(2);
                            purchInput.value = basePurch.toFixed(2);
                        } else {
                            const firstWt = parseFloat(rows[0].querySelector('.variant-weight').value) || 1;
                            saleInput.value = (baseSale / firstWt).toFixed(2);
                            purchInput.value = (basePurch / firstWt).toFixed(2);
                        }
                    } else {
                        saleInput.value = (baseSale * weight).toFixed(2);
                        purchInput.value = (basePurch * weight).toFixed(2);
                    }
                });
            }

            function updateUnitLabel() {
                const unitName = getUnitName(unitDropdown.value).toLowerCase();
                const labels = document.querySelectorAll('.unit-label');
                let txt = '(pc)';
                if (unitName === 'kg' || unitName === 'kilogram' || unitName === 'kgs') txt = '(per kg)';
                else if (unitName === 'carton' || unitName === 'cartons' || unitName === 'ctn' || unitName === 'box' || unitName === 'boxes') txt = '(per ctn)';
                else if (unitName === 'pieces' || unitName === 'pcs' || unitName === 'piece') txt = '(per pc)';
                labels.forEach(l => l.textContent = txt);
            }

            document.getElementById('sale_price_per_box').addEventListener('input', calculateVariantPrices);
            document.getElementById('purchase_price_per_piece').addEventListener('input', calculateVariantPrices);
            document.getElementById('variantsBody').addEventListener('input', function(e) {
                if (e.target.classList.contains('variant-weight')) {
                    calculateVariantPrices();
                }
            });

            const origAdd = addVariantRow;
            addVariantRow = function(name, weight, unit) {
                origAdd(name, weight, unit);
                setTimeout(calculateVariantPrices, 0);
            };

            // Wire remove for existing rows
            document.querySelectorAll('.remove-variant').forEach(btn => {
                if (!btn.disabled) {
                    btn.addEventListener('click', function() {
                        this.closest('tr').remove();
                    });
                }
            });

            productNameInput.addEventListener('input', function() {
                autoGenerateVariants();
                updateUnitLabel();
            });
            unitDropdown.addEventListener('change', function() {
                autoGenerateVariants();
                updateUnitLabel();
                setTimeout(calculateVariantPrices, 50);
            });

            if (unitDropdown.value) {
                updateUnitLabel();
                setTimeout(calculateVariantPrices, 100);
            }

            document.getElementById('addVariantBtn').addEventListener('click', function() {
                const unitId = unitDropdown.value;
                const unitName = getUnitName(unitId);
                const prodName = productNameInput.value.trim() || 'Variant';
                addVariantRow(prodName + ' VAR', 1, unitName ? (unitName === 'Pieces' || unitName === 'Carton' ? 'Pcs' : unitName) : 'Pcs');
                const rows = variantsBody.querySelectorAll('.variant-row');
                if (rows.length > 1) {
                    rows[0].querySelector('.remove-variant').disabled = false;
                }
            });

            // Select2
            $('#color-select').select2({ placeholder: "Select Colors", tags: true });
            
            $('#category-dropdown').on('change', function() {
                var cid = $(this).val();
                if (cid) {
                    $.get('/get-subcategories/' + cid, function(d) {
                        $('#subcategory-dropdown').empty().append('<option value="">Select...</option>');
                        $.each(d, function(_, v) {
                            $('#subcategory-dropdown').append('<option value="' + v.id + '">' + v.name + '</option>');
                        });
                    });
                }
            });
        });
    </script>
@endsection
