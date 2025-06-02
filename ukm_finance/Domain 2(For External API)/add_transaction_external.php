<?php
// Add this at the very top of the file, before any HTML output
if (isset($_POST['api_url'])) {
    header('Content-Type: application/json');
    
    $apiUrl = trim($_POST['api_url']);
    if (filter_var($apiUrl, FILTER_VALIDATE_URL)) {
        // Try to fetch the XML
        $xmlContent = @file_get_contents($apiUrl);
        if ($xmlContent !== false) {
            // Try to parse XML
            $xmlData = @simplexml_load_string($xmlContent);
            if ($xmlData === false) {
                echo json_encode([
                    'status' => 0,
                    'message' => 'Failed to parse XML.'
                ]);
                exit();
            }
            
            // Convert XML to array
            $transactions = [];
            foreach ($xmlData->transaksi as $transaksi) {
                $transactions[] = [
                    'tanggal' => (string)$transaksi->tanggal,
                    'nama_ukm' => (string)$transaksi->nama_ukm,
                    'jenis' => (string)$transaksi->jenis,
                    'kategori' => (string)$transaksi->kategori,
                    'jumlah' => (string)$transaksi->jumlah,
                    'keterangan' => (string)$transaksi->keterangan
                ];
            }
            
            echo json_encode([
                'status' => 1,
                'message' => 'Success',
                'data' => $transactions
            ]);
            exit();
        } else {
            echo json_encode([
                'status' => 0,
                'message' => 'Failed to fetch XML from the provided URL.'
            ]);
            exit();
        }
    } else {
        echo json_encode([
            'status' => 0,
            'message' => 'Invalid URL.'
        ]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Transaction</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2D664A;
            --secondary-color: #EEAD55;
            --danger-color: #FF5252;
            --background-color: #F8F9FC;
            --text-color: #333333;
            --card-bg: #FFFFFF;
            --border-color: #E0E0E0;
            --box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
        }

        .container {
            max-width: 960px;
            margin: 20px auto;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .page-header h1 {
            font-size: 2rem;
            color: var(--text-color);
            margin: 0;
            font-weight: 600;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
            font-size: 0.9rem;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            color: var(--text-color);
            background-color: var(--card-bg);
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(221, 221, 221, 0.1);
        }

        .form-group input[type="text"]::placeholder,
        .form-group textarea::placeholder {
            color: #666;
            opacity: 0.7;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: var(--card-bg);
            color: var(--text-color);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(221, 221, 221, 0.1);
            font-weight: 500;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #204d37;
        }

        .btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            color: var(--secondary-color);
            border: 1px solid rgba(76, 175, 80, 0.2);
        }

        .alert-danger {
            background: rgba(255, 82, 82, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(255, 82, 82, 0.2);
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Tab bar styling */
        .tab-bar {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .tab-button {
            padding: 10px 20px;
            cursor: pointer;
            border: none;
            background-color: transparent;
            font-size: 1rem;
            color: #666;
            transition: all 0.3s ease;
            border-bottom: 2px solid transparent;
            font-weight: 500;
        }

        .tab-button.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            font-weight: 600;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Transaction table styling */
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: var(--card-bg);
            box-shadow: var(--box-shadow);
            border-radius: 12px;
            overflow: hidden;
        }

        .transaction-table th,
        .transaction-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .transaction-table th {
            background: rgba(45, 102, 74, 0.05);
            font-weight: 600;
            color: var(--primary-color);
        }

        .transaction-table tr:hover {
            background-color: rgba(45, 102, 74, 0.02);
        }

        .transaction-table .pemasukan {
            color: var(--secondary-color);
        }

        .transaction-table .pengeluaran {
            color: var(--danger-color);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 10px;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .form-row .form-group {
                flex: none;
                width: 100%;
            }

            .tab-bar {
                flex-direction: column;
            }

            .tab-button {
                border-bottom: none;
                border-left: 2px solid transparent;
                text-align: left;
            }

            .tab-button.active {
                border-bottom-color: transparent;
                border-left-color: var(--primary-color);
            }

            .transaction-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
             <h1>Tambah Transaksi Baru</h1>
        </div>

        <div class="form-container">
            <div id="alert-container"></div>

            <!-- Tab Bar -->
            <div class="tab-bar">
                <button class="tab-button active" data-tab="select-ukm">Pilih UKM yang Ada</button>
                <button class="tab-button" data-tab="create-ukm">Buat UKM Baru</button>
                <button class="tab-button" data-tab="read-xml">Baca XML</button>
            </div>

            <!-- Tab Content -->
            <div id="select-ukm" class="tab-content active">
                 <form id="transaction-form">
                     <div class="form-group">
                         <label for="ukm_id">Pilih UKM *</label>
                         <select id="ukm_id" name="ukm_id" required>
                             <option value="">Memuat UKM...</option>
                         </select>
                     </div>

                     <div class="form-row">
                         <div class="form-group">
                             <label for="jenis">Jenis Transaksi *</label>
                             <select id="jenis" name="jenis" required>
                                 <option value="">Pilih Jenis</option>
                                 <option value="pemasukan">Pemasukan</option>
                                 <option value="pengeluaran">Pengeluaran</option>
                             </select>
                         </div>
                         <div class="form-group">
                             <label for="tanggal">Tanggal *</label>
                             <input type="date" id="tanggal" name="tanggal" required>
                         </div>
                     </div>

                     <div class="form-group">
                         <label for="kategori">Kategori *</label>
                         <input type="text" id="kategori" name="kategori" required placeholder="Contoh: Iuran, Konsumsi, Alat Tulis">
                     </div>

                     <div class="form-group">
                         <label for="jumlah">Jumlah (Rp) *</label>
                         <input type="number" id="jumlah" name="jumlah" required min="0" step="0.01">
                     </div>

                     <div class="form-group">
                         <label for="keterangan">Keterangan</label>
                         <textarea id="keterangan" name="keterangan" rows="3" placeholder="Opsional: Deskripsi singkat transaksi"></textarea>
                     </div>

                     <button type="submit" class="btn btn-primary" id="submit-btn-transaction">
                         Simpan Transaksi
                     </button>
                 </form>
            </div>

            <div id="create-ukm" class="tab-content">
                <form id="create-ukm-form">
                    <div class="form-group">
                        <label for="new_ukm_name">Nama UKM Baru *</label>
                        <input type="text" id="new_ukm_name" name="new_ukm_name" required placeholder="Masukkan nama UKM baru">
                    </div>
                    <button type="submit" class="btn btn-primary" id="submit-btn-create-ukm">
                        Buat UKM
                    </button>
                </form>
            </div>

            <div id="read-xml" class="tab-content">
                <form id="xml-form" class="xml-form">
                    <div class="form-group">
                        <label for="api_url">URL Exchange API</label>
                        <input type="text" id="api_url" name="api_url" placeholder="Masukkan URL Exchange API" value="https://ourgi.infinityfreeapp.com/Project_UAS/class/api.php">
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-primary" onclick="handleXmlFetch()">
                            <i class="fas fa-sync-alt"></i> Fetch & Display
                        </button>
                    </div>
                </form>
                <div id="xml-result" class="mt-4">
                    <pre id="xml-content" style="background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;"></pre>
                </div>
            </div>
        </div>
    </div>

    <script>
        // !! IMPORTANT: Replace with the actual URL of your internal UKM Finance API !!
        const INTERNAL_API_BASE = 'https://sistemkaskecil.infinityfreeapp.com'; 
        
        // Default user information
        const DEFAULT_USER = {
            id: 7,
            name: 'Arnold',
            email: 'arnold@example.com',
            role: 'bendahara'
        };
        
        // Add this function before the DOMContentLoaded event listener
        async function fetchExchangeData() {
            console.log('Fetch function called'); // Debug log
            const submitBtn = document.querySelector('#read-xml .btn-primary');
            const originalText = submitBtn.innerHTML;
            const tbody = document.getElementById('xml-transaction-list');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="spinner"></div> Mengambil data...';
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">Memuat data...</td></tr>';

            try {
                const apiUrl = document.getElementById('api_url').value;
                console.log('Fetching from URL:', apiUrl); // Debug log

                const response = await fetch(`${INTERNAL_API_BASE}/proxy.php?endpoint=external_transaksi.php`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-User-ID': DEFAULT_USER.id,
                        'X-User-Email': DEFAULT_USER.email
                    },
                    body: JSON.stringify({
                        api_url: apiUrl,
                        user_id: DEFAULT_USER.id,
                        user_email: DEFAULT_USER.email
                    })
                });

                console.log('Response status:', response.status); // Debug log

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Get the raw response text first
                const responseText = await response.text();
                console.log('Raw response:', responseText);

                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('Failed to parse JSON response:', e);
                    throw new Error('Invalid JSON response from server');
                }

                console.log('Parsed response:', result);

                if (!result || typeof result !== 'object') {
                    throw new Error('Invalid response format from server');
                }

                if (result.status !== 1) {
                    throw new Error(result.message || 'Failed to load exchange data');
                }

                // Clear existing rows
                tbody.innerHTML = '';

                const transactions = result.data;
                
                if (!transactions || !Array.isArray(transactions)) {
                    throw new Error('Invalid data format received from API');
                }

                if (transactions.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data transaksi</td></tr>';
                    return;
                }

                // Sort by date (newest first)
                transactions.sort((a, b) => new Date(b.tanggal) - new Date(a.tanggal));

                // Populate table
                transactions.forEach(transaksi => {
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                        <td>${formatDate(transaksi.tanggal)}</td>
                        <td>${transaksi.nama_ukm || '-'}</td>
                        <td class="${transaksi.jenis}">${transaksi.jenis === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran'}</td>
                        <td>${transaksi.kategori || '-'}</td>
                        <td class="${transaksi.jenis}">Rp ${formatNumber(transaksi.jumlah)}</td>
                        <td>${transaksi.keterangan || '-'}</td>
                    `;

                    tbody.appendChild(row);
                });

                showAlert('Data Exchange berhasil diambil!', 'success');

            } catch (error) {
                console.error('Error fetching exchange data:', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            Gagal mengambil data Exchange: ${error.message}
                            <br>
                            <small>Silakan periksa URL dan coba lagi.</small>
                        </td>
                    </tr>
                `;
                showAlert(`Gagal mengambil data Exchange: ${error.message}`, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadUkms();
            
            // Set default date to today
            document.getElementById('tanggal').valueAsDate = new Date();

            // Tab switching logic
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const targetTab = button.getAttribute('data-tab');

                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');

                    tabContents.forEach(content => {
                        if (content.id === targetTab) {
                            content.classList.add('active');
                            // Load transactions when the view-transactions tab is clicked
                            if (targetTab === 'view-transactions') {
                                loadTransactions();
                            }
                        } else {
                            content.classList.remove('active');
                        }
                    });
                });
            });

            // Form submission handlers
            document.getElementById('transaction-form').addEventListener('submit', handleSubmitTransaction);
            document.getElementById('create-ukm-form').addEventListener('submit', handleSubmitCreateUkm);

            // Add event listener for refresh button
            document.getElementById('refresh-transactions').addEventListener('click', loadTransactions);
        });

        async function loadUkms() {
            const select = document.getElementById('ukm_id');
            select.innerHTML = '<option value="">Memuat UKM...</option>'; // Reset and show loading
            
            try {
                console.log('Fetching UKMs from:', `${INTERNAL_API_BASE}/proxy.php?endpoint=ukm.php`);
                const response = await fetch(`${INTERNAL_API_BASE}/proxy.php?endpoint=ukm.php`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-User-ID': DEFAULT_USER.id,
                        'X-User-Email': DEFAULT_USER.email
                    }
                });
                
                const responseText = await response.text();
                console.log('Raw response:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('Failed to parse JSON response:', e);
                    throw new Error('Invalid JSON response from server');
                }
                
                if (!response.ok) {
                    console.error('Server response error (loadUkms):', {
                        status: response.status,
                        statusText: response.statusText,
                        headers: Object.fromEntries(response.headers.entries()),
                        body: data
                    });
                    throw new Error(data.message || `HTTP error! status: ${response.status}`);
                }

                console.log('Received UKM data:', data);

                select.innerHTML = '<option value="">Pilih UKM</option>'; // Clear loading and add default option
                
                if (data && data.status === 1 && Array.isArray(data.data)) {
                    data.data.forEach(ukm => {
                        const option = document.createElement('option');
                        option.value = ukm.id;
                        option.textContent = ukm.nama_ukm;
                        select.appendChild(option);
                    });
                } else {
                    console.error('Invalid data format:', data);
                    select.innerHTML = '<option value="">Gagal memuat UKM</option>';
                    throw new Error(data.message || 'Invalid data format received from UKM API');
                }

            } catch (error) {
                console.error('Error loading UKMs:', error);
                select.innerHTML = '<option value="">Gagal memuat UKM</option>';
                showAlert(error.message || 'Failed to load UKM list. Please check the console for details.', 'error');
            }
        }

        async function handleSubmitCreateUkm(e) {
            e.preventDefault();
            
            const form = e.target;
            const submitBtn = document.getElementById('submit-btn-create-ukm');
            const originalText = submitBtn.textContent;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="spinner"></div> Membuat...';
            
            const formData = new FormData(form);
            const jsonData = {
                ...Object.fromEntries(formData),
                user_id: DEFAULT_USER.id,
                user_email: DEFAULT_USER.email
            };

            const ukmName = jsonData.new_ukm_name;

            if (!ukmName) {
                showAlert('Nama UKM tidak boleh kosong.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                return;
            }

            try {
                const response = await fetch(`${INTERNAL_API_BASE}/proxy.php?endpoint=ukm_create.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-User-ID': DEFAULT_USER.id,
                        'X-User-Email': DEFAULT_USER.email
                    },
                    body: JSON.stringify({ nama_ukm: ukmName })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server response error (handleSubmitCreateUkm):', errorText);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                if (result.status === 1) {
                    showAlert(result.message || 'UKM berhasil dibuat!', 'success');
                    form.reset();
                    loadUkms();
                    document.querySelector('.tab-button[data-tab="select-ukm"]').click();
                } else {
                    showAlert(result.message || 'Gagal membuat UKM.', 'error');
                }

            } catch (error) {
                console.error('Error creating UKM:', error);
                showAlert(`Failed to create UKM: ${error.message}`, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }

        async function handleSubmitTransaction(e) {
            e.preventDefault();
            
            const form = e.target;
            const submitBtn = document.getElementById('submit-btn-transaction');
            const originalText = submitBtn.textContent;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="spinner"></div> Menyimpan...';
            
            const formData = new FormData(form);
            const jsonData = {
                ...Object.fromEntries(formData),
                user_id: DEFAULT_USER.id,
                user_email: DEFAULT_USER.email
            };

            if (!jsonData.ukm_id || jsonData.ukm_id === "") {
                showAlert('Pilih UKM terlebih dahulu.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                return;
            }

            try {
                const response = await fetch(`${INTERNAL_API_BASE}/proxy.php?endpoint=external_transaksi.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-User-ID': DEFAULT_USER.id,
                        'X-User-Email': DEFAULT_USER.email
                    },
                    body: JSON.stringify(jsonData)
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server response error (handleSubmitTransaction):', errorText);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.status === 1) {
                    showAlert(result.message || 'Transaksi berhasil ditambahkan!', 'success');
                    form.reset();
                    document.getElementById('tanggal').valueAsDate = new Date();
                } else {
                    showAlert(result.message || 'Gagal menambahkan transaksi.', 'error');
                }
            } catch (error) {
                console.error('Error submitting transaction:', error);
                showAlert(`Failed to submit transaction: ${error.message}`, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }

        async function loadTransactions() {
            const tbody = document.getElementById('transaction-list');
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">Memuat data...</td></tr>';

            try {
                const url = `${INTERNAL_API_BASE}/proxy.php?endpoint=transaksi.php`;
                console.log('Fetching transactions from:', url);
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-User-ID': DEFAULT_USER.id,
                        'X-User-Email': DEFAULT_USER.email
                    }
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', Object.fromEntries(response.headers.entries()));

                const responseText = await response.text();
                console.log('Raw response:', responseText);

                if (!response.ok) {
                    throw new Error(`Server returned ${response.status}: ${responseText}`);
                }

                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('Failed to parse JSON response:', e);
                    throw new Error(`Invalid JSON response: ${responseText.substring(0, 200)}...`);
                }

                if (!result || typeof result !== 'object') {
                    throw new Error('Invalid response format from server');
                }

                if (result.status !== 1) {
                    throw new Error(result.message || 'Failed to load transactions');
                }

                // Clear existing rows
                tbody.innerHTML = '';

                const transactions = result.data;
                
                if (!transactions || !Array.isArray(transactions) || transactions.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data transaksi</td></tr>';
                    return;
                }

                // Sort by date (newest first)
                transactions.sort((a, b) => new Date(b.tanggal) - new Date(a.tanggal));

                // Populate table
                transactions.forEach(transaksi => {
                    const row = document.createElement('tr');
                    
                    row.innerHTML = `
                        <td>${formatDate(transaksi.tanggal)}</td>
                        <td>${transaksi.nama_ukm || '-'}</td>
                        <td class="${transaksi.jenis}">${transaksi.jenis === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran'}</td>
                        <td>${transaksi.kategori || '-'}</td>
                        <td class="${transaksi.jenis}">Rp ${formatNumber(transaksi.jumlah)}</td>
                        <td>${transaksi.keterangan || '-'}</td>
                    `;

                    tbody.appendChild(row);
                });

            } catch (error) {
                console.error('Error loading transactions:', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            Gagal memuat data transaksi: ${error.message}
                            <br>
                            <small>Silakan coba refresh halaman atau hubungi administrator.</small>
                        </td>
                    </tr>
                `;
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }

        function formatNumber(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        function showAlert(message, type) {
            const container = document.getElementById('alert-container');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            
            container.innerHTML = ''; // Clear previous alerts
            container.appendChild(alert);
            
            // Auto-hide after 7 seconds
            setTimeout(() => {
                alert.remove();
            }, 7000);
        }

        function displayXmlTransactions(transactions) {
            const tbody = document.getElementById('xml-transaction-list');
            tbody.innerHTML = '';

            if (!transactions || transactions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada data transaksi</td></tr>';
                return;
            }

            // Sort by date (newest first)
            transactions.sort((a, b) => new Date(b.tanggal) - new Date(a.tanggal));

            // Populate table
            transactions.forEach(transaksi => {
                const row = document.createElement('tr');
                
                row.innerHTML = `
                    <td>${formatDate(transaksi.tanggal)}</td>
                    <td>${transaksi.nama_ukm || '-'}</td>
                    <td class="${transaksi.jenis}">${transaksi.jenis === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran'}</td>
                    <td>${transaksi.kategori || '-'}</td>
                    <td class="${transaksi.jenis}">Rp ${formatNumber(transaksi.jumlah)}</td>
                    <td>${transaksi.keterangan || '-'}</td>
                `;

                tbody.appendChild(row);
            });
        }

        async function handleXmlFetch() {
            const submitBtn = document.querySelector('#xml-form .btn-primary');
            const originalText = submitBtn.innerHTML;
            const xmlContent = document.getElementById('xml-content');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="spinner"></div> Mengambil data...';
            xmlContent.textContent = 'Memuat data...';

            try {
                const apiUrl = document.getElementById('api_url').value;
                console.log('Fetching from URL:', apiUrl);

                const response = await fetch('read_xml.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `api_url=${encodeURIComponent(apiUrl)}`
                });

                const responseText = await response.text();
                console.log('Raw response:', responseText);

                // Display the raw XML content
                xmlContent.textContent = responseText;

            } catch (error) {
                console.error('Error fetching XML data:', error);
                xmlContent.textContent = `Error: ${error.message}`;
                showAlert(`Gagal mengambil data Exchange: ${error.message}`, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    </script>
</body>
</html>