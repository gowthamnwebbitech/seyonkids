@extends('admin.index')
@section('admin')
    @if (session('success'))
        <div id="successAlert" class="alert alert-success">
            {{ session('success') }}
        </div>

        <script>
            setTimeout(function() {
                $('#successAlert').fadeOut('fast');
            }, 3000); // 3000 milliseconds = 3 seconds
        </script>
    @endif


    <div id="uploadimageModal" class="modal" role="dialog">
        <div class="modal-dialog cropimg">
            <div class="modal-content">
                <div class="modal-body">
                    <a href="Javascript:void" class="btn-cropclose" onclick="modalclose();"><img
                            src="https://icones.pro/wp-content/uploads/2022/05/icone-fermer-et-x-rouge.png"
                            width="25px"></a>

                    <div class="row">
                        <div class="col-md-12 text-center">
                            <div id="image_demo" style="width:100%;"></div>
                        </div>
                        <input type="hidden" id="idval">
                        <div class="col-md-12 text-center mb-1">
                            <button type="button" class="btn btn-success crop_image">Upload</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <div class="profile-tab">
        <div class="custom-tab-1">
            <ul class="nav nav-tabs">
                <li class="nav-item"><a href="#profile-settings" data-bs-toggle="tab" class="nav-link active show">ADD
                        PRODUCT</a>
                </li>
            </ul>

            <div class="tab-content">
                <div id="profile-settings" class="tab-pane fade active show">
                    <div class="pt-3">
                        <div class="settings-form">
                            <form method="POST" action="{{ route('admin.product.store') }}" enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">Select Type</label>
                                        <select class="form-control" id="product_type" name="product_type" required>
                                            <option value="">Please select</option>
                                            <option value="book" {{ old('product_type') == 'book' ? 'selected' : '' }}>
                                                Book</option>
                                            <option value="toys" {{ old('product_type') == 'toys' ? 'selected' : '' }}>
                                                Toys</option>
                                        </select>
                                        @error('product_type')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">Category</label>
                                        <select class="form-control" id="category" name="category_id" required>
                                            <option data-display="Select" value="">Please select</option>
                                            @php $categories = App\Models\ProductCategory::get(); @endphp
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">SubCategory</label>
                                        <select class="form-control" id="subcategory" name="subcategory" required>
                                            <option value="" data-display="Select">Please select</option>
                                            {{-- <option value="{{ $subcategory->id }}" data-display="Select">{{ $subcategory->name }}</option> --}}
                                        </select>
                                        @error('subcategory')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-md-4">
                                        <label class="form-label">SubMenu</label>
                                        <select class="form-control" id="submenu" name="submenu" required>
                                            <option value="" data-display="Select">Please select</option>
                                        </select>
                                        @error('submenu')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label d-block">Select Shop By Age</label>

                                        @php
                                            $shop_by_ages = App\Models\ShopByAge::get();
                                        @endphp

                                        <div class="row">
                                            @foreach ($shop_by_ages as $shop_by_age)
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="shop_by_age_id[]" id="age_{{ $shop_by_age->id }}"
                                                            value="{{ $shop_by_age->id }}">
                                                        <label class="form-check-label" for="age_{{ $shop_by_age->id }}">
                                                            {{ $shop_by_age->title }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        @error('shop_by_age_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Select Shop By Price</label>
                                        @php $shop_by_prices = App\Models\ShopByPrice::get(); @endphp
                                        <select class="form-control" id="shop_by_price" name="shop_by_price" required>
                                            <option data-display="Select" value="">Please select</option>
                                            @foreach ($shop_by_prices as $shop_by_price)
                                                <option value="{{ $shop_by_price->id }}"
                                                    {{ old('shop_by_price') == $shop_by_price->id ? 'selected' : '' }}>
                                                    {{ $shop_by_price->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('shop_by_price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Product Name</label>
                                        <input type="text" name="product_name" class="form-control"
                                            placeholder="Ex: Harmony Ring" value="{{ old('product_name') }}" required>
                                        @error('product_name')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Quantity</label>
                                        <input type="text" name="quantity" class="form-control" placeholder="Ex: 2"
                                            value="{{ old('quantity') }}" required>
                                        @error('quantity')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row @if (old('pages') == 'book') @else d-none @endif" id="pages_row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">No of Pages</label>
                                        <input type="number" name="pages" class="form-control"
                                            placeholder="Ex: 120 Pages" value="{{ old('pages') }}" required>
                                        @error('pages')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">SKU</label>
                                        <input type="text" name="sku" class="form-control"
                                            value="{{ old('sku') }}" required placeholder="Enter SKU">
                                        @error('sku')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Actual Price</label>
                                        <input type="number" name="actual_price" class="form-control"
                                            placeholder="Ex: 1000" value="{{ old('actual_price') }}" required>
                                        @error('actual_price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Offer Price</label>
                                        <input type="number" name="offer_price" class="form-control"
                                            placeholder="Ex: 850" value="{{ old('offer_price') }}" required>
                                        @error('offer_price')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <div class="form-group">
                                            <label>Discount</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="discount_text" readonly>
                                                <span class="input-group-text">%</span>
                                            </div>

                                            <!-- Hidden field for DB -->
                                            <input type="hidden" name="discount">
                                        </div>
                                    </div>

                                    {{-- <div class="mb-3 col-md-3">
                                        <label class="form-label">GST (%)</label>
                                        <input type="text" name="gst" class="form-control" value="{{ old('gst') }}">
                                        @error('gst')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div> --}}
                                </div>

                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">
                                            Thumbnail Image (size: 1600 × 1600 px, Max 1 MB)
                                        </label>
                                        <input type="file" name="file1" class="form-control image_input"
                                            id="file1" required>
                                        <div id="imagePreview"></div>
                                        @error('file1')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">
                                            Gallery Images (size: 1600 × 1600 px, Max 1 MB each)
                                        </label>                                        <input type="file" name="file2[]" class="form-control image_input"
                                            id="file2" multiple />
                                        <div id="imagePreviews" class="d-flex flex-wrap gap-2 mt-2"></div>

                                        @error('file2')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                        @error('file2.*')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="mb-3 col-md-12">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" id="description" required>{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col-md-12">
                                        <label class="form-label">Key Words</label>
                                        <textarea name="keywords" class="form-control" id="key-words" required>{{ old('keywords') }}</textarea>
                                        @error('keywords')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row align-items-center">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label">Status</label><br>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="active"
                                                value="1" {{ old('status') == 1 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="active">Active</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="inactive"
                                                value="0" {{ old('status') == 0 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="inactive">Inactive</label>
                                        </div>
                                        @error('status')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <br>
                                <input type="checkbox" id="best_sellers" name="best_sellers" value="1"
                                    {{ old('best_sellers') ? 'checked' : '' }}>
                                <label for="best_sellers"> Best Sellers</label><br>
                                <input type="checkbox" id="new_arrival" name="new_arrival" value="1"
                                    {{ old('new_arrival') ? 'checked' : '' }}>
                                <label for="new_arrival"> New Arrival</label><br>
                                <input type="checkbox" id="on_sale" name="on_sale" value="1"
                                    {{ old('on_sale') ? 'checked' : '' }}>
                                <label for="on_sale"> On Sale</label><br>
                                <input type="checkbox" id="featured" name="featured" value="1"
                                    {{ old('featured') ? 'checked' : '' }}>
                                <label for="featured"> Featured</label><br><br>

                                <button class="btn btn-primary" type="submit">Add Product</button>
                            </form>

                        </div>
                    </div>
                </div> <br><br><br>
            </div>
        </div>

    </div>
@endsection

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>

<script>
    $(document).ready(function() {
        CKEDITOR.replace('description');
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        var selectedCategory = "{{ old('category', $selectedCategory ?? '') }}";
        var selectedSubcategory = "{{ old('subcategory', $selectedSubcategory ?? '') }}";

        function loadSubcategories(category_id, selectedSubcategory = null) {
            if (category_id) {
                $.ajax({
                    type: "GET",
                    url: "{{ route('get-subcategories', ['id' => ':category_id']) }}".replace(
                        ':category_id', category_id),
                    success: function(data) {
                        var $subcategory = $('select[name="subcategory"]');
                        $subcategory.empty().append('<option value="">Please select</option>');

                        $.each(data, function(key, value) {
                            var isSelected = (key == selectedSubcategory) ? 'selected' : '';
                            $subcategory.append('<option value="' + key + '" ' +
                                isSelected + '>' + value + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", status, error);
                    }
                });
            } else {
                $('select[name="subcategory"]').empty().append('<option value="">Please select</option>');
            }
        }

        function loadSubmenus(subcategory_id) {
            if (subcategory_id) {
                $.ajax({
                    type: "GET",
                    url: "{{ route('get-submenu', ['id' => ':category_id']) }}".replace(':category_id',
                        subcategory_id),
                    success: function(data) {
                        var $subcategory = $('select[name="submenu"]');
                        $subcategory.empty().append('<option value="">Please select</option>');

                        $.each(data, function(key, value) {
                            var isSelected = (key == selectedSubcategory) ? 'selected' : '';
                            $subcategory.append('<option value="' + key + '" ' +
                                isSelected + '>' + value + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", status, error);
                    }
                });
            } else {
                $('select[name="subcategory"]').empty().append('<option value="">Please select</option>');
            }
        }

        $('#category').change(function() {
            var category_id = $(this).val();
            loadSubcategories(category_id);
        });
        $('#subcategory').change(function() {
            var subcategory_id = $(this).val();
            loadSubmenus(subcategory_id);
        });

        if (selectedCategory) {
            $('#category').val(selectedCategory);
            loadSubcategories(selectedCategory, selectedSubcategory);
        }
    });
</script>
@if ($errors->any())
    <script>
        $(document).ready(function() {
            @foreach ($errors->keys() as $field)
                var field = $('[name="{{ $field }}"]');
                field.addClass('is-invalid');
            @endforeach

            var firstInvalid = $('.is-invalid:first');
            if (firstInvalid.length) {
                firstInvalid.focus();
            }

            showValidationError();
            loadSubcategories(selectedCategory, selectedSubcategory);
        });

        function showValidationError() {
            var alertBox = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>⚠ Please correct the highlighted fields.</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
            $('form').before(alertBox);
        }
    </script>
@endif

<script>
    $(document).ready(function() {
        $("#file1").on("change", function() {
            const input = this;
            const preview = $("#imagePreview");
            preview.empty();

            if (input.files && input.files[0]) {
                const file = input.files[0];
                const fileSizeKB = file.size / 1024;

                if (fileSizeKB > 2048) {
                    alert("File size must be less than 2 MB.");
                    input.value = "";
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgHtml = `
                    <img src="${e.target.result}" class="img-thumbnail" width="120" height="120" alt="Thumbnail Preview" />
                `;
                    preview.html(imgHtml);
                };
                reader.readAsDataURL(file);
            }
        });

        // ---------- MULTIPLE IMAGES PREVIEW (file2) ----------
        let selectedFiles = [];

        $(".image_input").on("change", function() {
            const input = this;
            const previewContainer = $("#imagePreviews");

            if (input.id === "file2") {
                selectedFiles = Array.from(input.files);
                previewContainer.empty();

                if (selectedFiles.length > 4) {
                    alert("You can only select up to 4 images.");
                    input.value = "";
                    selectedFiles = [];
                    return;
                }

                // filter out files > 2MB
                selectedFiles = selectedFiles.filter(file => {
                    const fileSizeKB = file.size / 1024;
                    if (fileSizeKB > 2048) {
                        alert(`"${file.name}" exceeds 2 MB and will not be added.`);
                        return false;
                    }
                    return true;
                });

                selectedFiles.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imgHtml = `
                        <div class="position-relative d-inline-block me-2 mb-2" data-index="${index}">
                            <img src="${e.target.result}" class="img-thumbnail" width="80" height="80" />
                            <button type="button" class="btn btn-sm btn-danger remove-image position-absolute top-0 end-0 p-0 px-1" title="Remove">&times;</button>
                        </div>
                    `;
                        previewContainer.append(imgHtml);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });

        // ---------- REMOVE IMAGE ----------
        $(document).on("click", ".remove-image", function() {
            const $div = $(this).closest("div");
            const index = $div.data("index");

            selectedFiles.splice(index, 1);

            const dataTransfer = new DataTransfer();
            selectedFiles.forEach((file) => dataTransfer.items.add(file));
            document.querySelector("#file2").files = dataTransfer.files;

            $div.remove();

            $("#imagePreviews .position-relative").each(function(i) {
                $(this).attr("data-index", i);
            });

            if (selectedFiles.length === 0) {
                $("#file2").val("");
            }
        });
    });
</script>

<script>
    $(document).ready(function() {
        function calculateOfferPercentage() {
            let originalPrice = parseFloat($("[name='actual_price']").val()) || 0;
            let offerPrice    = parseFloat($("[name='offer_price']").val()) || 0;

            if (originalPrice > 0 && offerPrice > 0 && offerPrice < originalPrice) {
                let discountPercent = ((originalPrice - offerPrice) / originalPrice * 100).toFixed(0);

                $('#discount_text').val(discountPercent);
                $("[name='discount']").val(discountPercent); // numeric value
            } else {
                $('#discount_text').val('');
                $("[name='discount']").val('');
            }
        }

        $("[name='offer_price'], [name='actual_price']").on("input", calculateOfferPercentage);


        $('#product_type').change(function() {
            var selectedType = $(this).val();
            if (selectedType === 'book') {
                $('#pages_row').removeClass('d-none');
            } else {
                $('#pages_row').addClass('d-none');
            }
        });
    });
</script>
