@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('unit::units.units'))

    <li class="active">{{ trans('unit::units.units') }}</li>
@endcomponent

@section('content')
    <div class="row">
        <div class="btn-group pull-right">
            <a href="{{ route('admin.units.create') }}" class="btn btn-primary btn-actions btn-create">
                {{ trans('admin::resource.create', ['resource' => trans('unit::units.unit')]) }}
            </a>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-body">
            <div id="units-custom-table">
                <div class="table-controls" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <label style="margin-right: 10px;">
                            Göster:
                            <select id="perPage" style="padding: 5px 10px; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="10">10</option>
                                <option value="20" selected>20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </label>
                    </div>
                    <div>
                        <input type="text" id="searchBox" placeholder="Ara..." style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; width: 250px;">
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table class="units-table-custom" style="width: 100%; border-collapse: collapse; background: white;">
                        <thead>
                            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                                <th style="padding: 16px 12px; text-align: center; width: 50px; font-weight: 600; color: #495057;">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th style="padding: 16px 12px; text-align: left; width: 20%; font-weight: 600; color: #495057;">Ad</th>
                                <th style="padding: 16px 12px; text-align: center; width: 12%; font-weight: 600; color: #495057;">Kısaltma</th>
                                <th style="padding: 16px 12px; text-align: center; width: 12%; font-weight: 600; color: #495057;">Min</th>
                                <th style="padding: 16px 12px; text-align: center; width: 12%; font-weight: 600; color: #495057;">Adım</th>
                                <th style="padding: 16px 12px; text-align: center; width: 14%; font-weight: 600; color: #495057;">Varsayılan Miktar</th>
                                <th style="padding: 16px 12px; text-align: center; width: 12%; font-weight: 600; color: #495057;">Ürün Sayısı</th>
                                <th style="padding: 16px 12px; text-align: left; width: 18%; font-weight: 600; color: #495057;">Tarih</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <div id="pagination" style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div id="showingInfo" style="color: #6c757d;"></div>
                    <div id="paginationButtons"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module">
        let allUnits = [];
        let filteredUnits = [];
        let currentPage = 1;
        let perPage = 20;

        // Fetch units data
        async function fetchUnits() {
            try {
                const response = await axios.get('{{ route("admin.units.table") }}');
                allUnits = response.data.data;
                filteredUnits = [...allUnits];
                renderTable();
            } catch (error) {
                console.error('Error fetching units:', error);
            }
        }

        // Render table
        function renderTable() {
            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            const pageData = filteredUnits.slice(start, end);

            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = '';

            pageData.forEach(unit => {
                const row = document.createElement('tr');
                row.style.borderBottom = '1px solid #dee2e6';
                row.style.transition = 'background-color 0.2s';
                row.onmouseenter = () => row.style.backgroundColor = '#f8f9fa';
                row.onmouseleave = () => row.style.backgroundColor = 'white';

                row.innerHTML = `
                    <td style="padding: 14px 12px; text-align: center;">
                        <input type="checkbox" class="row-checkbox" value="${unit.id}">
                    </td>
                    <td style="padding: 14px 12px; text-align: left;">
                        <a href="${window.FleetCart.baseUrl}/admin/units/${unit.id}/edit" style="color: #007bff; text-decoration: none; font-weight: 500;">
                            ${unit.name}
                        </a>
                    </td>
                    <td style="padding: 14px 12px; text-align: center; color: #495057;">
                        ${unit.short_suffix || '-'}
                    </td>
                    <td style="padding: 14px 12px; text-align: center; color: #495057; font-family: monospace;">
                        ${parseFloat(unit.min).toFixed(2).replace('.', ',')}
                    </td>
                    <td style="padding: 14px 12px; text-align: center; color: #495057; font-family: monospace;">
                        ${parseFloat(unit.step).toFixed(2).replace('.', ',')}
                    </td>
                    <td style="padding: 14px 12px; text-align: center; color: #495057; font-family: monospace;">
                        ${parseFloat(unit.default_qty).toFixed(2).replace('.', ',')}
                    </td>
                    <td style="padding: 14px 12px; text-align: center;">
                        <span style="background: #17a2b8; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                            ${unit.products_count || 0}
                        </span>
                    </td>
                    <td style="padding: 14px 12px; text-align: left; color: #6c757d;">
                        ${formatDate(unit.created_at)}
                    </td>
                `;
                tbody.appendChild(row);
            });

            updatePagination();
        }

        // Format date to Turkish
        function formatDate(dateString) {
            const date = new Date(dateString);
            const months = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 
                          'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
            return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
        }

        // Update pagination
        function updatePagination() {
            const totalPages = Math.ceil(filteredUnits.length / perPage);
            const start = (currentPage - 1) * perPage + 1;
            const end = Math.min(currentPage * perPage, filteredUnits.length);

            document.getElementById('showingInfo').textContent = 
                `Toplam ${filteredUnits.length} kayıttan ${start}-${end} arası gösteriliyor`;

            const paginationButtons = document.getElementById('paginationButtons');
            paginationButtons.innerHTML = '';

            if (totalPages <= 1) return;

            const buttonStyle = 'padding: 6px 12px; margin: 0 2px; border: 1px solid #dee2e6; background: white; cursor: pointer; border-radius: 4px;';
            const activeStyle = 'padding: 6px 12px; margin: 0 2px; border: 1px solid #007bff; background: #007bff; color: white; cursor: pointer; border-radius: 4px;';

            // Previous button
            const prevBtn = document.createElement('button');
            prevBtn.textContent = '‹';
            prevBtn.style.cssText = buttonStyle;
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => { currentPage--; renderTable(); };
            paginationButtons.appendChild(prevBtn);

            // Page buttons
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    const btn = document.createElement('button');
                    btn.textContent = i;
                    btn.style.cssText = i === currentPage ? activeStyle : buttonStyle;
                    btn.onclick = () => { currentPage = i; renderTable(); };
                    paginationButtons.appendChild(btn);
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    const dots = document.createElement('span');
                    dots.textContent = '...';
                    dots.style.padding = '6px 8px';
                    paginationButtons.appendChild(dots);
                }
            }

            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.textContent = '›';
            nextBtn.style.cssText = buttonStyle;
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => { currentPage++; renderTable(); };
            paginationButtons.appendChild(nextBtn);
        }

        // Search functionality
        document.getElementById('searchBox').addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            filteredUnits = allUnits.filter(unit => 
                unit.name.toLowerCase().includes(searchTerm) ||
                (unit.short_suffix && unit.short_suffix.toLowerCase().includes(searchTerm))
            );
            currentPage = 1;
            renderTable();
        });

        // Per page change
        document.getElementById('perPage').addEventListener('change', (e) => {
            perPage = parseInt(e.target.value);
            currentPage = 1;
            renderTable();
        });

        // Select all checkbox
        document.getElementById('selectAll').addEventListener('change', (e) => {
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.checked = e.target.checked;
            });
        });

        // Initialize
        fetchUnits();
    </script>
@endpush
