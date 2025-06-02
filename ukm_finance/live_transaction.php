<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Petty Cash Real-time dengan Raw XML</title> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #17a2b8;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --background-color: #f4f8fb;
            --card-background: #fff;
            --text-color: #222;
            --shadow: 0 4px 24px rgba(23,162,184,0.08), 0 1.5px 6px rgba(0,0,0,0.03);
            --border-radius: 14px;
            --transition: 0.18s cubic-bezier(.4,0,.2,1);
        }
        body {
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
            background: linear-gradient(120deg, #e0f7fa 0%, #f4f8fb 100%);
            color: var(--text-color);
            margin: 0;
            padding: 32px 0;
        }
        .container {
            max-width: 980px;
            margin: 0 auto;
            background: var(--card-background);
            padding: 32px 28px 28px 28px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            border: 1px solid #e3eaf1;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1.5px solid #e3eaf1;
            padding-bottom: 16px;
            margin-bottom: 28px;
        }
        .page-header h1 {
            margin: 0;
            font-size: 2.1em;
            letter-spacing: 0.5px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .status-indicator {
            font-size: 1em;
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 500;
            box-shadow: 0 1px 4px rgba(23,162,184,0.07);
            transition: background var(--transition), color var(--transition);
        }
        .status-indicator.loading { background: #ffe082; color: #333; }
        .status-indicator.success { background: var(--success-color); color: #fff; }
        .status-indicator.error { background: var(--danger-color); color: #fff; }
        .btn {
            padding: 9px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            background: linear-gradient(90deg, var(--primary-color) 80%, #1cc7d0 100%);
            color: #fff;
            font-size: 1em;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(23,162,184,0.07);
            transition: background var(--transition), box-shadow var(--transition), transform var(--transition);
        }
        .btn .fas { margin-right: 7px; }
        .btn:disabled {
            background: #b2bec3;
            cursor: not-allowed;
            opacity: 0.7;
            box-shadow: none;
        }
        .btn:hover:not(:disabled) {
            background: linear-gradient(90deg, #138496 80%, #1cc7d0 100%);
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 4px 16px rgba(23,162,184,0.13);
        }
        .input-url-section {
            background: #fafdff;
            padding: 18px 18px 10px 18px;
            margin-bottom: 26px;
            border: 1.5px solid #e3eaf1;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(23,162,184,0.04);
        }
        .form-group { margin-bottom: 1.1rem; }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 1em;
            color: #138496;
            letter-spacing: 0.2px;
        }
        .form-control {
            display: block;
            width: 100%;
            padding: 0.5rem 1rem;
            font-size: 1.05em;
            font-weight: 400;
            line-height: 1.5;
            color: var(--text-color);
            background: #f7fafc;
            border: 1.5px solid #b2bec3;
            border-radius: 0.4rem;
            transition: border-color var(--transition), box-shadow var(--transition);
            box-sizing: border-box;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            outline: 0;
            box-shadow: 0 0 0 0.18rem rgba(23, 162, 184, 0.13);
            background: #fff;
        }
        .input-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .input-group .form-control { flex-grow: 1; }
        .input-group .btn { flex-shrink: 0; padding: 0.5rem 1.1rem; }
        .table-responsive { overflow-x: auto; }
        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 18px;
            font-size: 1em;
            background: #fafdff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(23,162,184,0.04);
        }
        .data-table th, .data-table td {
            padding: 13px 12px;
            text-align: left;
            border-bottom: 1.5px solid #e3eaf1;
        }
        .data-table th {
            background: linear-gradient(90deg, #e0f7fa 80%, #fafdff 100%);
            font-weight: 700;
            color: #138496;
            font-size: 1.05em;
            border-top: 1.5px solid #e3eaf1;
        }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover { background: #f1fafd; }
        .text-center { text-align: center !important; }
        .text-danger { color: var(--danger-color); }
        .alert {
            padding: 13px 18px;
            margin-top: 18px;
            border-radius: 7px;
            font-size: 1em;
            font-weight: 500;
            box-shadow: 0 1px 4px rgba(23,162,184,0.04);
        }
        .alert-danger {
            background: linear-gradient(90deg, #f8d7da 80%, #fff 100%);
            color: #721c24;
            border: 1.5px solid #f5c6cb;
        }
        .alert-info {
            background: linear-gradient(90deg, #d1ecf1 80%, #fff 100%);
            color: #0c5460;
            border: 1.5px solid #bee5eb;
        }
        #last-updated {
            font-size: 0.93em;
            color: #6c757d;
            text-align: right;
            margin-top: 7px;
            font-style: italic;
        }
        .spinner {
            display: inline-block;
            width: 1.2em;
            height: 1.2em;
            border: 0.18em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spin 0.75s linear infinite;
            margin-right: 0.5em;
            vertical-align: middle;
        }
        .table-placeholder .spinner.page-load {
            font-size: 2.2em;
            margin: 32px auto;
            display: block;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        /* Raw XML/JSON Section */
        .raw-data-section {
            margin-top: 38px;
            background: #fafdff;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(23,162,184,0.04);
            padding: 18px 18px 12px 18px;
            border: 1.5px solid #e3eaf1;
        }
        .raw-data-section h3 {
            font-size: 1.18em;
            margin-bottom: 12px;
            border-bottom: 1px solid #e3eaf1;
            padding-bottom: 7px;
            color: #138496;
            font-weight: 600;
            letter-spacing: 0.2px;
        }
        #raw-xml-output {
            background: #23272e;
            color: #bfc9d1;
            padding: 18px 14px;
            border-radius: 7px;
            font-family: 'Fira Mono', 'Consolas', 'Courier New', Courier, monospace;
            font-size: 0.97em;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 420px;
            overflow-y: auto;
            border: 1.5px solid #444;
            margin-bottom: 0;
            box-shadow: 0 1px 4px rgba(23,162,184,0.04);
        }
        #raw-xml-output:empty::before {
            content: "Data mentah akan ditampilkan di sini...";
            color: #666;
        }
        /* Responsive */
        @media (max-width: 700px) {
            .container { padding: 12px 2vw; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .data-table th, .data-table td { padding: 9px 6px; font-size: 0.97em; }
            .raw-data-section { padding: 10px 4vw 8px 4vw; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
             <h1><i class="fas fa-wallet" style="color: var(--primary-color);"></i> Data Petty Cash</h1>
             <div>
                <button id="manual-refresh-btn" class="btn btn-sm" title="Segarkan Data dari URL Terakhir">
                    <i class="fas fa-sync-alt"></i> Segarkan
                </button>
                <span id="status-indicator" class="status-indicator loading">Memuat...</span>
             </div>
        </div>

        <div class="input-url-section">
            <div class="form-group">
                <label for="xml-api-url-input">Masukkan URL API (XML/JSON):</label>
                <div class="input-group">
                    <input type="url" id="xml-api-url-input" class="form-control" placeholder="https://contoh.com/path/ke/api.php">
                    <button id="load-from-url-btn" class="btn" type="button">
                        <i class="fas fa-cloud-download-alt"></i> Muat dari URL
                    </button>
                </div>
            </div>
        </div>
        <div id="alert-container"></div>
        <div id="last-updated"></div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Tipe</th>
                        <th>Jumlah (Rp)</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody id="petty-cash-data-list">
                    <tr class="table-placeholder">
                        <td colspan="6" class="text-center"><div class="spinner page-load"></div>Mengambil data awal...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="raw-data-section">
            <h3><i class="fas fa-code"></i> Data Mentah dari API</h3>
            <pre id="raw-xml-output"></pre>
        </div>
    </div>

    <script>
        // --- KONFIGURASI WAJIB (Sesuaikan dengan setup Anda) ---
        // Ini adalah contoh, pastikan PROXY_SCRIPT_URL_BASE dan DEFAULT_API_PETTY_CASH_URL sudah benar
        const PROXY_SCRIPT_URL_BASE = 'https://pettycashkecil.infinityfreeapp.com'; // Ganti dengan URL Anda
        const DEFAULT_API_PETTY_CASH_URL = 'https://ukmfinancepraditas.infinityfreeapp.com/api/exchangeapi.php'; // Ganti dengan URL API default Anda
        const PROXY_FILENAME = 'proxy1.php';
        const UPDATE_INTERVAL_MS = 0;
        // --- AKHIR KONFIGURASI ---

        const dataListBody = document.getElementById('petty-cash-data-list');
        const statusIndicator = document.getElementById('status-indicator');
        const lastUpdatedElement = document.getElementById('last-updated');
        const alertContainer = document.getElementById('alert-container');
        const manualRefreshButton = document.getElementById('manual-refresh-btn');
        const loadFromUrlButton = document.getElementById('load-from-url-btn');
        const xmlApiUrlInput = document.getElementById('xml-api-url-input');

        // Elemen BARU untuk menampilkan raw XML
        const rawXmlOutputElement = document.getElementById('raw-xml-output');

        let currentActiveApiUrl = DEFAULT_API_PETTY_CASH_URL;
        let updateIntervalId = null;

        document.addEventListener('DOMContentLoaded', function() {
            if (loadFromUrlButton && xmlApiUrlInput) {
                loadFromUrlButton.addEventListener('click', function() {
                    const newApiUrl = xmlApiUrlInput.value.trim();
                    if (newApiUrl && isValidHttpUrl(newApiUrl)) {
                        currentActiveApiUrl = newApiUrl;
                        if (updateIntervalId) clearInterval(updateIntervalId);
                        loadPettyCashData();
                        if (UPDATE_INTERVAL_MS > 0) {
                            updateIntervalId = setInterval(loadPettyCashData, UPDATE_INTERVAL_MS);
                        }
                    } else {
                        showAlert('Silakan masukkan URL API yang valid.', 'danger');
                        rawXmlOutputElement.textContent = ''; // Kosongkan raw output jika URL tidak valid
                    }
                });
            }

            manualRefreshButton.addEventListener('click', () => {
                if (currentActiveApiUrl) {
                    loadPettyCashData();
                } else {
                     showAlert('Tidak ada URL API yang aktif untuk disegarkan.', 'info');
                     rawXmlOutputElement.textContent = ''; // Kosongkan raw output
                }
            });

            if (DEFAULT_API_PETTY_CASH_URL && typeof DEFAULT_API_PETTY_CASH_URL === 'string' && DEFAULT_API_PETTY_CASH_URL.trim() !== '') {
                xmlApiUrlInput.value = DEFAULT_API_PETTY_CASH_URL;
                currentActiveApiUrl = DEFAULT_API_PETTY_CASH_URL;
                loadPettyCashData(); // Muat data awal
                if (UPDATE_INTERVAL_MS > 0) {
                    updateIntervalId = setInterval(loadPettyCashData, UPDATE_INTERVAL_MS);
                }
            } else {
                const colspan = document.querySelector('.data-table thead tr').cells.length || 6;
                dataListBody.innerHTML = `<tr><td colspan="${colspan}" class="text-center">Masukkan URL API lalu klik "Muat dari URL", atau konfigurasikan URL default.</td></tr>`;
                statusIndicator.textContent = "Menunggu Input";
                statusIndicator.className = 'status-indicator';
                rawXmlOutputElement.textContent = ''; // Kosongkan raw output
            }
        });

        function isValidHttpUrl(string) {
            let url;
            try {
                url = new URL(string);
            } catch (_) {
                return false;  
            }
            return url.protocol === "http:" || url.protocol === "https:";
        }

        async function fetchDataViaProxy(targetApiUrl) {
            const headers = { 'Accept': 'application/json' }; // JavaScript tetap minta JSON dari proxy
            const proxyBase = PROXY_SCRIPT_URL_BASE.replace(/\/$/, '');
            const proxyFile = PROXY_FILENAME.replace(/^\//, '');
            const proxyRequestUrl = `${proxyBase}/${proxyFile}?target_url=${encodeURIComponent(targetApiUrl)}`;
            
            console.log(`Fetching via Proxy: ${proxyRequestUrl}`);
            showAlert('', true); 

            try {
                const response = await fetch(proxyRequestUrl, { headers, cache: 'no-store' });
                const responseText = await response.text(); // Dapatkan teks mentah dulu
                console.log(`Raw proxy response for ${targetApiUrl}: ${responseText.substring(0, 500)}...`);
                
                let data;
                try { 
                    data = JSON.parse(responseText); // Coba parse sebagai JSON
                } catch (e) { 
                    // Jika gagal parse sebagai JSON, mungkin proxy mengirim teks mentah (misal error HTML dari proxy)
                    // Tampilkan teks mentah ini di area raw output
                    if (rawXmlOutputElement) {
                        rawXmlOutputElement.textContent = responseText; // Tampilkan error mentah dari proxy
                    }
                    throw new Error(`Respons tidak valid dari proxy (bukan JSON). Isi (awal): ${responseText.substring(0,150)}... (JSON Parse Error: ${e.message})`); 
                }

                // Tampilkan raw_xml, raw_json, atau raw_content jika ada di respons proxy
                if (rawXmlOutputElement) {
                    if (data.raw_xml) {
                        rawXmlOutputElement.textContent = data.raw_xml;
                    } else if (data.raw_json) {
                         // Pretty print JSON jika memungkinkan
                        try {
                            const parsedJson = JSON.parse(data.raw_json);
                            rawXmlOutputElement.textContent = JSON.stringify(parsedJson, null, 2);
                        } catch {
                            rawXmlOutputElement.textContent = data.raw_json;
                        }
                    } else if (data.raw_content) {
                        rawXmlOutputElement.textContent = data.raw_content;
                    } else if (data.status !== 1 && data.response_preview) { // Jika error dan ada preview
                        rawXmlOutputElement.textContent = `Preview dari URL Target (Error ${data.http_code || response.status}):\n${data.response_preview}`;
                    } else if (data.status !== 1) { // Error umum dari proxy
                        rawXmlOutputElement.textContent = JSON.stringify(data, null, 2); // Tampilkan seluruh error JSON dari proxy
                    } else {
                        rawXmlOutputElement.textContent = 'Tidak ada data mentah spesifik (raw_xml/raw_json/raw_content) yang diterima dari proxy, tetapi status OK.';
                    }
                }

                if (!response.ok || !data || data.status !== 1) {
                    throw new Error(data.message || `HTTP error! Status: ${response.status}. Proxy harus mengembalikan JSON {status:1, data:[...]}.`);
                }
                return data; // data di sini adalah objek JSON yang sudah diparsing
            } catch (error) { 
                // Jika fetch gagal total (misal proxy tidak ditemukan), tampilkan error di raw output juga
                if (rawXmlOutputElement) {
                    rawXmlOutputElement.textContent = `Error saat fetch ke proxy: ${error.message}`;
                }
                throw error; 
            }
        }

        async function loadPettyCashData() {
            if (!currentActiveApiUrl) {
                showAlert('URL API belum ditentukan.', 'info');
                statusIndicator.textContent = "URL Tidak Ada";
                statusIndicator.className = 'status-indicator error';
                if(rawXmlOutputElement) rawXmlOutputElement.textContent = ''; // Kosongkan raw output
                return;
            }

            statusIndicator.textContent = "Memuat...";
            statusIndicator.className = 'status-indicator loading';
            manualRefreshButton.disabled = true;
            manualRefreshButton.innerHTML = '<span class="spinner"></span> Memuat...';
            loadFromUrlButton.disabled = true;
            if(rawXmlOutputElement) rawXmlOutputElement.textContent = 'Sedang mengambil data mentah...'; // Placeholder saat loading

            try {
                const result = await fetchDataViaProxy(currentActiveApiUrl); 
                dataListBody.innerHTML = ''; 

                if (result && result.status === 1 && Array.isArray(result.data)) {
                    if (result.data.length === 0) {
                        const colspan = document.querySelector('.data-table thead tr').cells.length || 6;
                        dataListBody.innerHTML = `<tr><td colspan="${colspan}" class="text-center">Tidak ada data petty cash dari URL: ${htmlspecialchars(currentActiveApiUrl)}</td></tr>`;
                        // Jika data kosong tapi sukses, raw_xml mungkin tetap ada
                        if (rawXmlOutputElement && result.raw_xml) {
                             rawXmlOutputElement.textContent = result.raw_xml;
                        } else if (rawXmlOutputElement && result.raw_json) {
                             rawXmlOutputElement.textContent = JSON.stringify(JSON.parse(result.raw_json), null, 2);
                        } else if (rawXmlOutputElement) {
                            rawXmlOutputElement.textContent = "Data tabel kosong. Data mentah (jika ada) telah ditampilkan di atas.";
                        }

                    } else {
                        // Menggunakan pemetaan kunci yang sudah Anda sesuaikan sebelumnya
                        result.data.forEach(record => {
                            const row = dataListBody.insertRow();
                            row.insertCell().textContent = htmlspecialchars(record.id || '-');
                            row.insertCell().textContent = formatDate(record.tanggal || '-'); // dari API: tanggal
                            const deskripsiGabungan = `${htmlspecialchars(record.nama_ukm || '')} (${htmlspecialchars(record.kategori || '')})`; // dari API: nama_ukm, kategori
                            row.insertCell().textContent = deskripsiGabungan.trim() !== '()' ? deskripsiGabungan : '-';
                            
                            const tipeCell = row.insertCell();
                            let tipeDisplay = htmlspecialchars(record.jenis || '-'); // dari API: jenis
                            if (String(record.jenis).toLowerCase() === 'pengeluaran') {
                                tipeDisplay = 'Pengeluaran';
                                tipeCell.style.color = 'var(--danger-color)';
                            } else if (String(record.jenis).toLowerCase() === 'pemasukan') {
                                tipeDisplay = 'Pemasukan';
                                tipeCell.style.color = 'var(--success-color)';
                            }
                            tipeCell.textContent = tipeDisplay;
                            tipeCell.style.fontWeight = 'bold';
                            
                            const jumlahCell = row.insertCell();
                            jumlahCell.textContent = formatCurrency(record.jumlah || 0); // dari API: jumlah
                            jumlahCell.style.textAlign = 'right';
                            if (String(record.jenis).toLowerCase() === 'pengeluaran') {
                                jumlahCell.style.color = 'var(--danger-color)';
                            } else if (String(record.jenis).toLowerCase() === 'pemasukan') {
                                jumlahCell.style.color = 'var(--success-color)';
                            }
                            row.insertCell().textContent = htmlspecialchars(record.keterangan || '-'); // dari API: keterangan
                        });
                    }
                    statusIndicator.textContent = "Terbaru";
                    statusIndicator.className = 'status-indicator success';
                    lastUpdatedElement.textContent = `Data dari ${htmlspecialchars(currentActiveApiUrl)}. Diperbarui: ${new Date().toLocaleTimeString('id-ID')}`;
                    showAlert('', true);
                } else { 
                    // Jika result.status bukan 1, error message sudah ada di result.message
                    // Dan raw data sudah dihandle di fetchDataViaProxy
                    throw new Error(result.message || 'Format data tidak valid dari proxy/API.'); 
                }
            } catch (error) {
                console.error('Gagal memuat data petty cash:', error);
                showError(`Gagal memuat data dari ${htmlspecialchars(currentActiveApiUrl)}: ${error.message}`);
                statusIndicator.textContent = "Error";
                statusIndicator.className = 'status-indicator error';
                // rawXmlOutputElement sudah diisi oleh error handling di fetchDataViaProxy jika ada error di sana
                // Jika error terjadi setelah fetchDataViaProxy, pastikan rawXmlOutputElement tetap menampilkan sesuatu yang relevan
                if (dataListBody.querySelectorAll('tr').length === 0 || dataListBody.querySelector('.table-placeholder')) {
                    const colspan = document.querySelector('.data-table thead tr').cells.length || 6;
                    dataListBody.innerHTML = `<tr><td colspan="${colspan}" class="text-center text-danger">Gagal memuat data. Periksa log konsol dan tampilan data mentah.</td></tr>`;
                }
            } finally {
                manualRefreshButton.disabled = false;
                manualRefreshButton.innerHTML = '<i class="fas fa-sync-alt"></i> Segarkan';
                loadFromUrlButton.disabled = false;
            }
        }
        
        // --- Fungsi Helper (htmlspecialchars, formatDate, formatCurrency, showAlert, showError) ---
        // SAMA PERSIS seperti sebelumnya, tidak perlu diubah.
        function htmlspecialchars(str) {
            if (typeof str !== 'string' && typeof str !== 'number') return str === null || typeof str === 'undefined' ? '' : String(str);
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(str).replace(/[&<>"']/g, m => map[m]);
        }

        function formatDate(dateString) {
            if (!dateString || dateString === '-') return '-';
            const date = new Date(dateString);
            if (isNaN(date.getTime())) {
                const parts = String(dateString).split(/[- :T]/);
                if (parts.length >= 3) {
                    const year = parseInt(parts[0], 10);
                    const monthIndex = parseInt(parts[1], 10) - 1;
                    const day = parseInt(parts[2], 10);
                    const tempDate = new Date(Date.UTC(year, monthIndex, day));
                    if (!isNaN(tempDate.getTime())) return tempDate.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                }
                return htmlspecialchars(dateString);
            }
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        function formatCurrency(number) {
            const num = parseFloat(String(number).replace(/[^0-9.-]+/g,""));
            if (isNaN(num)) return 'Rp 0';
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(num);
        }
        
        function showAlert(message, clearOnly = false) {
            alertContainer.innerHTML = ''; 
            if (clearOnly || !message) return;
            const type = (message.toLowerCase().includes('error') || message.toLowerCase().includes('gagal') || message.toLowerCase().includes('tidak valid')) ? 'danger' : 'info';
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.setAttribute('role', 'alert');
            alertDiv.textContent = message;
            alertContainer.appendChild(alertDiv);
        }
        function showError(message) { showAlert(message); }

    </script>
</body>
</html>