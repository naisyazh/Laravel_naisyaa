@section('style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .assignment-tabs {
            gap: 0.75rem;
        }

        .assignment-tabs .nav-link {
            border-radius: 999px;
            background: #f3f4f6;
            color: #4b5563;
            font-weight: 600;
        }

        .assignment-tabs .nav-link.active {
            background: linear-gradient(90deg, #4f46e5, #06b6d4);
            color: #fff;
        }

        .assignment-panel {
            border: 1px solid #edf2f7;
            border-radius: 1rem;
            padding: 1.5rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .assignment-select,
        .assignment-panel .form-control {
            min-height: 48px;
        }

        .summary-card,
        .checkout-card {
            background: #111827;
            color: #fff;
            border-radius: 1rem;
            padding: 1.25rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding-bottom: 0.85rem;
            margin-bottom: 0.85rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .summary-item span,
        .checkout-label {
            color: rgba(255, 255, 255, 0.72);
        }

        .summary-item strong {
            text-align: right;
        }

        .checkout-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .assignment-table tbody td {
            vertical-align: middle;
        }

        .assignment-empty {
            color: #6b7280;
            text-align: center;
            padding: 2rem 0;
        }

        .btn-loading .spinner-inline {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            margin-right: 0.5rem;
            border: 2px solid rgba(255, 255, 255, 0.55);
            border-top-color: #fff;
            border-radius: 50%;
            animation: assignment-spin 0.8s linear infinite;
            vertical-align: middle;
        }

        @keyframes assignment-spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 767.98px) {
            .assignment-panel {
                padding: 1rem;
            }

            .checkout-card {
                flex-direction: column;
                align-items: stretch;
            }

            .assignment-tabs {
                gap: 0.5rem;
            }
        }
    </style>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(function() {
            const apiRoutes = {
                provinces: @json(route('assignment.regions.provinces')),
                regencies: @json(route('assignment.regions.regencies')),
                districts: @json(route('assignment.regions.districts')),
                villages: @json(route('assignment.regions.villages')),
                lookupBarang: @json(route('assignment.barang.lookup')),
                checkout: @json(route('assignment.checkout')),
            };
            const csrfToken = @json(csrf_token());

            function formatRupiah(value) {
                return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
            }

            function setStatusText($element, text, tone) {
                $element.removeClass('text-muted text-success text-danger text-warning').addClass('text-' + tone).text(
                    text
                );
            }

            function setButtonLoading($button, isLoading, loadingText) {
                const defaultText = $button.data('default-text') || $button.text().trim();

                if (!$button.data('default-text')) {
                    $button.data('default-text', defaultText);
                }

                if (isLoading) {
                    $button.addClass('btn-loading').prop('disabled', true);
                    $button.html('<span class="spinner-inline" aria-hidden="true"></span>' + loadingText);
                    return;
                }

                $button.removeClass('btn-loading').html($button.data('default-text'));
            }

            function parseErrorMessage(error) {
                if (error.responseJSON && error.responseJSON.message) {
                    return error.responseJSON.message;
                }

                if (error.response && error.response.data) {
                    const responseData = error.response.data;

                    if (responseData.message) {
                        return responseData.message;
                    }

                    if (responseData.errors) {
                        const firstKey = Object.keys(responseData.errors)[0];
                        if (firstKey && responseData.errors[firstKey][0]) {
                            return responseData.errors[firstKey][0];
                        }
                    }
                }

                if (error.statusText) {
                    return error.statusText;
                }

                return 'Terjadi kesalahan saat memproses permintaan.';
            }

            function showToast(icon, title, text) {
                if (window.Swal) {
                    Swal.fire({
                        icon: icon,
                        title: title,
                        text: text,
                        confirmButtonText: 'Tutup',
                    });
                    return;
                }

                alert(title + '\n' + text);
            }

            function createAjaxTransport() {
                return {
                    get: function(url, params = {}) {
                        return new Promise(function(resolve, reject) {
                            $.ajax({
                                url: url,
                                method: 'GET',
                                data: params,
                                success: resolve,
                                error: reject,
                            });
                        });
                    },
                    post: function(url, payload = {}) {
                        return new Promise(function(resolve, reject) {
                            $.ajax({
                                url: url,
                                method: 'POST',
                                data: Object.assign({
                                    _token: csrfToken
                                }, payload),
                                success: resolve,
                                error: reject,
                            });
                        });
                    }
                };
            }

            function createAxiosTransport() {
                return {
                    get: function(url, params = {}) {
                        return axios.get(url, {
                            params: params,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        }).then(function(response) {
                            return response.data;
                        });
                    },
                    post: function(url, payload = {}) {
                        return axios.post(url, payload, {
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        }).then(function(response) {
                            return response.data;
                        });
                    }
                };
            }

            function resetSelect($select, placeholder) {
                $select.empty().append(new Option(placeholder, '')).prop('disabled', true);
            }

            function fillSelect($select, placeholder, items) {
                resetSelect($select, placeholder);

                items.forEach(function(item) {
                    $select.append(new Option(item.name, item.id));
                });

                $select.prop('disabled', items.length === 0);
            }

            function selectedText($select) {
                return $select.val() ? $select.find('option:selected').text() : '-';
            }

            function initRegionModule(mode, transport) {
                const prefix = mode + '-region';
                const $province = $('#' + prefix + '-province');
                const $regency = $('#' + prefix + '-regency');
                const $district = $('#' + prefix + '-district');
                const $village = $('#' + prefix + '-village');
                const $status = $('#' + prefix + '-status');

                const outputs = {
                    province: $('#' + prefix + '-province-output'),
                    regency: $('#' + prefix + '-regency-output'),
                    district: $('#' + prefix + '-district-output'),
                    village: $('#' + prefix + '-village-output'),
                };

                function updateSummary() {
                    outputs.province.text(selectedText($province));
                    outputs.regency.text(selectedText($regency));
                    outputs.district.text(selectedText($district));
                    outputs.village.text(selectedText($village));
                }

                async function loadOptions(url, params, $target, placeholder, successMessage, emptyMessage) {
                    setStatusText($status, 'Memuat data...', 'warning');

                    try {
                        const response = await transport.get(url, params);
                        const items = response.data || [];

                        fillSelect($target, placeholder, items);
                        setStatusText($status, items.length ? successMessage : emptyMessage, items.length ? 'success' :
                            'warning');
                    } catch (error) {
                        resetSelect($target, placeholder);
                        setStatusText($status, parseErrorMessage(error), 'danger');
                    }
                }

                $province.on('change', function() {
                    updateSummary();
                    resetSelect($regency, 'Pilih Kota / Kabupaten');
                    resetSelect($district, 'Pilih Kecamatan');
                    resetSelect($village, 'Pilih Kelurahan');
                    updateSummary();

                    if (!this.value) {
                        setStatusText($status, 'Pilih provinsi untuk memuat kota / kabupaten.', 'muted');
                        return;
                    }

                    loadOptions(
                        apiRoutes.regencies, {
                            province_id: this.value
                        },
                        $regency,
                        'Pilih Kota / Kabupaten',
                        'Kota / kabupaten berhasil dimuat.',
                        'Data kota / kabupaten tidak ditemukan.'
                    );
                });

                $regency.on('change', function() {
                    updateSummary();
                    resetSelect($district, 'Pilih Kecamatan');
                    resetSelect($village, 'Pilih Kelurahan');
                    updateSummary();

                    if (!this.value) {
                        setStatusText($status, 'Pilih kota / kabupaten untuk memuat kecamatan.', 'muted');
                        return;
                    }

                    loadOptions(
                        apiRoutes.districts, {
                            regency_id: this.value
                        },
                        $district,
                        'Pilih Kecamatan',
                        'Kecamatan berhasil dimuat.',
                        'Data kecamatan tidak ditemukan.'
                    );
                });

                $district.on('change', function() {
                    updateSummary();
                    resetSelect($village, 'Pilih Kelurahan');
                    updateSummary();

                    if (!this.value) {
                        setStatusText($status, 'Pilih kecamatan untuk memuat kelurahan.', 'muted');
                        return;
                    }

                    loadOptions(
                        apiRoutes.villages, {
                            district_id: this.value
                        },
                        $village,
                        'Pilih Kelurahan',
                        'Kelurahan berhasil dimuat.',
                        'Data kelurahan tidak ditemukan.'
                    );
                });

                $village.on('change', updateSummary);

                (async function bootstrapRegions() {
                    try {
                        setStatusText($status, 'Memuat daftar provinsi...', 'warning');
                        const response = await transport.get(apiRoutes.provinces);
                        fillSelect($province, 'Pilih Provinsi', response.data || []);
                        setStatusText($status, 'Provinsi siap dipilih.', 'success');
                    } catch (error) {
                        resetSelect($province, 'Pilih Provinsi');
                        setStatusText($status, parseErrorMessage(error), 'danger');
                    }

                    updateSummary();
                })();
            }

            function initPosModule(mode, transport) {
                const prefix = mode + '-pos';
                const $form = $('#' + prefix + '-form');
                const $kode = $('#' + prefix + '-kode');
                const $nama = $('#' + prefix + '-nama');
                const $harga = $('#' + prefix + '-harga');
                const $jumlah = $('#' + prefix + '-jumlah');
                const $lookupStatus = $('#' + prefix + '-lookup-status');
                const $checkoutStatus = $('#' + prefix + '-checkout-status');
                const $addButton = $('#' + prefix + '-add');
                const $payButton = $('#' + prefix + '-pay');
                const $tbody = $('#' + prefix + '-tbody');
                const $total = $('#' + prefix + '-total');

                const state = {
                    selectedItem: null,
                    cart: [],
                };

                function clearSelectedItem() {
                    state.selectedItem = null;
                    $nama.val('');
                    $harga.val('');
                    $jumlah.val(1);
                    toggleAddButton();
                }

                function resetLookupForm() {
                    clearSelectedItem();
                    $kode.val('').focus();
                    setStatusText($lookupStatus, 'Ketik kode barang lalu tekan Enter untuk mencari data.', 'muted');
                }

                function toggleAddButton() {
                    const qty = parseInt($jumlah.val() || 0, 10);
                    $addButton.prop('disabled', !(state.selectedItem && qty > 0));
                }

                function totalValue() {
                    return state.cart.reduce(function(sum, item) {
                        return sum + item.subtotal;
                    }, 0);
                }

                function renderCart() {
                    $tbody.empty();

                    if (!state.cart.length) {
                        $tbody.html(
                            '<tr><td colspan="6" class="text-center text-muted py-4">Belum ada item. Gunakan kode barang untuk mulai transaksi.</td></tr>'
                        );
                    } else {
                        state.cart.forEach(function(item) {
                            const row = `
                                <tr>
                                    <td>${item.kode}</td>
                                    <td>${item.nama}</td>
                                    <td>${formatRupiah(item.harga)}</td>
                                    <td>
                                        <input type="number" min="1" class="form-control form-control-sm qty-input"
                                            data-kode="${item.kode}" value="${item.jumlah}">
                                    </td>
                                    <td>${formatRupiah(item.subtotal)}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-inverse-danger btn-remove"
                                            data-kode="${item.kode}">Hapus</button>
                                    </td>
                                </tr>
                            `;
                            $tbody.append(row);
                        });
                    }

                    $total.text(formatRupiah(totalValue()));
                    $payButton.prop('disabled', state.cart.length === 0);
                }

                function mergeItemToCart(item, quantity) {
                    const existing = state.cart.find(function(cartItem) {
                        return cartItem.kode === item.kode;
                    });

                    if (existing) {
                        existing.jumlah += quantity;
                        existing.subtotal = existing.jumlah * existing.harga;
                        return;
                    }

                    state.cart.push({
                        kode: item.kode,
                        nama: item.nama,
                        harga: Number(item.harga),
                        jumlah: quantity,
                        subtotal: Number(item.harga) * quantity,
                    });
                }

                async function lookupBarang() {
                    const kode = $kode.val().trim().toUpperCase();

                    if (!kode) {
                        clearSelectedItem();
                        setStatusText($lookupStatus, 'Kode barang wajib diisi.', 'danger');
                        return;
                    }

                    $kode.val(kode);
                    clearSelectedItem();
                    setStatusText($lookupStatus, 'Mencari barang...', 'warning');

                    try {
                        const response = await transport.get(apiRoutes.lookupBarang, {
                            kode: kode
                        });
                        const barang = response.data;

                        state.selectedItem = barang;
                        $nama.val(barang.nama);
                        $harga.val(formatRupiah(barang.harga));
                        $jumlah.val(1);
                        toggleAddButton();
                        setStatusText($lookupStatus,
                            'Barang ditemukan. Anda bisa mengubah jumlah lalu klik Tambahkan.',
                            'success');
                    } catch (error) {
                        clearSelectedItem();
                        setStatusText($lookupStatus, parseErrorMessage(error), 'danger');
                    }
                }

                $kode.on('keydown', function(event) {
                    if (event.key !== 'Enter') {
                        return;
                    }

                    event.preventDefault();
                    lookupBarang();
                });

                $jumlah.on('input', toggleAddButton);

                $form.on('submit', function(event) {
                    event.preventDefault();

                    const qty = parseInt($jumlah.val() || 0, 10);

                    if (!state.selectedItem || qty < 1) {
                        setStatusText($lookupStatus, 'Barang harus ditemukan dan jumlah minimal 1.', 'danger');
                        toggleAddButton();
                        return;
                    }

                    mergeItemToCart(state.selectedItem, qty);
                    renderCart();
                    setStatusText($checkoutStatus,
                        'Keranjang diperbarui. Anda masih bisa mengubah jumlah atau menghapus item.', 'success');
                    resetLookupForm();
                });

                $tbody.on('change', '.qty-input', function() {
                    const kode = $(this).data('kode');
                    const qty = Math.max(1, parseInt($(this).val() || 1, 10));
                    const item = state.cart.find(function(cartItem) {
                        return cartItem.kode === kode;
                    });

                    if (!item) {
                        return;
                    }

                    item.jumlah = qty;
                    item.subtotal = item.harga * qty;
                    renderCart();
                    setStatusText($checkoutStatus, 'Jumlah item berhasil diperbarui.', 'success');
                });

                $tbody.on('click', '.btn-remove', function() {
                    const kode = $(this).data('kode');
                    state.cart = state.cart.filter(function(item) {
                        return item.kode !== kode;
                    });
                    renderCart();
                    setStatusText($checkoutStatus,
                        state.cart.length ? 'Item dihapus dari keranjang.' :
                        'Keranjang kosong. Silakan cari barang kembali.', 'warning');
                });

                $payButton.on('click', async function() {
                    if (!state.cart.length) {
                        return;
                    }

                    setButtonLoading($payButton, true, 'Menyimpan...');

                    try {
                        const response = await transport.post(apiRoutes.checkout, {
                            items: state.cart.map(function(item) {
                                return {
                                    kode: item.kode,
                                    jumlah: item.jumlah,
                                };
                            }),
                        });

                        showToast('success', 'Pembayaran Berhasil',
                            response.message + ' Nomor transaksi: ' + response.data.nomor_transaksi);

                        state.cart = [];
                        renderCart();
                        resetLookupForm();
                        setStatusText($checkoutStatus,
                            'Transaksi ' + response.data.nomor_transaksi + ' berhasil disimpan.', 'success');
                    } catch (error) {
                        const message = parseErrorMessage(error);
                        showToast('error', 'Pembayaran Gagal', message);
                        setStatusText($checkoutStatus, message, 'danger');
                    } finally {
                        setButtonLoading($payButton, false);
                        $payButton.prop('disabled', state.cart.length === 0);
                    }
                });

                renderCart();
                resetLookupForm();
            }

            initRegionModule('ajax', createAjaxTransport());
            initRegionModule('axios', createAxiosTransport());
            initPosModule('ajax', createAjaxTransport());
            initPosModule('axios', createAxiosTransport());
        });
    </script>
@endsection
