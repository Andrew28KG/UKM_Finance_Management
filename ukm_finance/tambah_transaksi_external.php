<?php
session_start();
header("Access-Control-Allow-Origin: *");
include('inc/header.php');
include('inc/auth.php');

// Check if user is authenticated
requireAuth();

// DEFAULT SETTINGS
$data_type = isset($_GET['type']) ? $_GET['type'] : 'xml'; // Default to XML
$api_source = isset($_GET['source']) ? $_GET['source'] : 'file'; // Default to file
$current_api = isset($_GET['api']) ? $_GET['api'] : 'transactions.xml'; // Default API endpoint
$external_url = isset($_GET['external_url']) ? $_GET['external_url'] : ''; // For external APIs

// Define available API sources
$available_apis = [
    'xml' => [
        ['name' => 'transactions.xml', 'title' => 'Transactions XML', 'source' => 'file'],
        ['name' => 'transactions.xml', 'title' => 'Transactions XML API', 'source' => 'api'],
        ['name' => 'https://ukmfinancepraditas.infinityfreeapp.com/api/exchangeapi.php', 'title' => 'External Exchange API', 'source' => 'external']
    ]
];

// Allow custom API URL input
if ($api_source === 'external' && !empty($external_url)) {
    $current_api = $external_url;
}

// Check if form is submitted and URL is provided
$xmlData = null;
$error = null;
if (isset($_POST['api_url']) || $api_source !== 'file') {
    $apiUrl = $api_source === 'external' ? $current_api : (isset($_POST['api_url']) ? trim($_POST['api_url']) : '');
    
    if (filter_var($apiUrl, FILTER_VALIDATE_URL)) {
        // Try to fetch the XML
        $xmlContent = @file_get_contents($apiUrl);
        if ($xmlContent !== false) {
            // Try to parse XML
            $xmlData = @simplexml_load_string($xmlContent);
            if ($xmlData === false) {
                $error = 'Failed to parse XML.';
            }
        } else {
            $error = 'Failed to fetch XML from the provided URL.';
        }
    } else if ($api_source === 'file') {
        // Try to read from file
        $xmlContent = @file_get_contents($current_api);
        if ($xmlContent !== false) {
            $xmlData = @simplexml_load_string($xmlContent);
            if ($xmlData === false) {
                $error = 'Failed to parse XML file.';
            }
        } else {
            $error = 'Failed to read XML file.';
        }
    } else {
        $error = 'Invalid URL.';
    }
}
?>

<div class="wrapper">
    <?php include('inc/sidebar.php'); ?>
    <div id="main-content">
        <section class="dashboard">
            <div class="welcome-section">
                <div class="welcome-message">
                    <h1>Tambah Transaksi External</h1>
                </div>
                <?php include('inc/profile_bar.php'); ?>
            </div>

            <div class="dashboard-column">
                <div class="finance-summary-container">
                    <div class="finance-summary-header">
                        <h1>XML API Reader</h1>
                    </div>
                    <div class="finance-summary-content">
                        <div class="card">
                            <div class="card-body">
                                <h3>Pilih Sumber Data</h3>
                                <p>Pilih jenis data dan sumber yang ingin ditampilkan.</p>
                                
                                <div class="api-selector">
                                    <?php foreach ($available_apis as $type => $apis): ?>
                                        <?php foreach ($apis as $api): ?>
                                            <div class="api-selector-item <?php echo ($data_type === $type && $current_api === $api['name'] && $api_source === $api['source']) ? 'active' : ''; ?>" 
                                                 onclick="location.href='?type=<?php echo $type; ?>&api=<?php echo $api['name']; ?>&source=<?php echo $api['source']; ?>'">
                                                <h4><?php echo htmlspecialchars($api['title']); ?> 
                                                    <span class="badge badge-<?php echo $api['source']; ?>">
                                                        <?php echo strtoupper($api['source']); ?>
                                                    </span>
                                                </h4>
                                                <p><?php echo ucfirst($type); ?> format (<?php echo substr($api['name'], 0, 50) . (strlen($api['name']) > 50 ? '...' : ''); ?>)</p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Form for custom external API URL -->
                                <div class="external-url-form">
                                    <label for="external_url">
                                        <i class="fas fa-link"></i> Masukkan URL API External:
                                    </label>
                                    <form action="" method="GET" class="input-group">
                                        <input type="hidden" name="source" value="external">
                                        <input type="hidden" name="type" value="<?php echo $data_type; ?>">
                                        <input type="text" name="external_url" id="external_url" 
                                               placeholder="https://example.com/api/data.xml" 
                                               value="<?php echo ($api_source === 'external' && !empty($external_url)) ? htmlspecialchars($external_url) : ''; ?>" 
                                               class="form-control">
                                        <button type="submit" class="btn">
                                            <i class="fas fa-arrow-right"></i> Load API
                                        </button>
                                    </form>
                                </div>

                                <?php if ($error): ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?php echo $error; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($xmlData): ?>
                                    <div class="xml-data-container">
                                        <div class="xml-view-tabs">
                                            <button class="xml-tab-button active" data-view="table">Table View</button>
                                            <button class="xml-tab-button" data-view="raw">Raw XML</button>
                                        </div>
                                        
                                        <div id="xml-table-view" class="xml-view active">
                                            <h3>XML Data (Table View):</h3>
                                            <div class="table-responsive">
                                                <table class="table">
                                                    <?php
                                                    // Function to display XML as a table
                                                    function displayXmlTable($xml) {
                                                        if (count($xml->children()) == 0) {
                                                            echo '<tr><td>' . htmlspecialchars($xml->getName()) . '</td><td>' . htmlspecialchars((string)$xml) . '</td></tr>';
                                                        } else {
                                                            foreach ($xml->children() as $child) {
                                                                echo '<tr><td>' . htmlspecialchars($child->getName()) . '</td><td>';
                                                                if (count($child->children()) > 0) {
                                                                    echo '<table class="table">';
                                                                    displayXmlTable($child);
                                                                    echo '</table>';
                                                                } else {
                                                                    echo htmlspecialchars((string)$child);
                                                                }
                                                                echo '</td></tr>';
                                                            }
                                                        }
                                                    }
                                                    displayXmlTable($xmlData);
                                                    ?>
                                                </table>
                                            </div>
                                        </div>

                                        <div id="xml-raw-view" class="xml-view">
                                            <h3>XML Data (Raw View):</h3>
                                            <pre class="xml-raw-content"><?php echo htmlspecialchars($xmlContent); ?></pre>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<style>
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.xml-data-container {
    margin-top: 20px;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.table td {
    padding: 8px;
    border: 1px solid #ddd;
}

.table table {
    margin: 0;
}

.alert {
    padding: 15px;
    margin: 15px 0;
    border-radius: 4px;
}

.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert i {
    margin-right: 5px;
}

.xml-view-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}

.xml-tab-button {
    background: #f4f8fb;
    border: none;
    color: #138496;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    outline: none;
    transition: background 0.18s, color 0.18s;
}

.xml-tab-button.active {
    background: #17a2b8;
    color: #fff;
}

.xml-view {
    display: none;
}

.xml-view.active {
    display: block;
}

.xml-raw-content {
    background: #f8f9fa;
    padding: 16px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    overflow-x: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
    font-family: monospace;
    font-size: 14px;
    line-height: 1.5;
}

/* New styles for API selector */
.api-selector {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.api-selector-item {
    flex: 1;
    min-width: 200px;
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 2px solid #eaeaea;
    cursor: pointer;
    transition: all 0.3s ease;
}

.api-selector-item:hover {
    border-color: #ddd;
    transform: translateY(-2px);
}

.api-selector-item.active {
    border-color: #17a2b8;
    background-color: rgba(23, 162, 184, 0.05);
}

.api-selector-item h4 {
    margin-bottom: 5px;
    font-weight: 600;
    color: #212529;
}

.api-selector-item p {
    color: #6c757d;
    font-size: 0.9rem;
    margin: 0;
}

.badge {
    display: inline-block;
    padding: 3px 8px;
    font-size: 12px;
    border-radius: 12px;
    background-color: #e9ecef;
    margin-left: 8px;
}

.badge-api {
    background-color: #4a6fdc;
    color: white;
}

.badge-file {
    background-color: #6c757d;
    color: white;
}

.badge-external {
    background-color: #28a745;
    color: white;
}

.external-url-form {
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.external-url-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}

.input-group {
    display: flex;
    gap: 10px;
}

.input-group input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
}

.input-group .btn {
    white-space: nowrap;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // XML View Tabs
    const xmlTabButtons = document.querySelectorAll('.xml-tab-button');
    const xmlViews = document.querySelectorAll('.xml-view');

    xmlTabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const viewType = this.dataset.view;
            
            // Update active button
            xmlTabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Show selected view
            xmlViews.forEach(view => {
                view.classList.remove('active');
                if (view.id === `xml-${viewType}-view`) {
                    view.classList.add('active');
                }
            });
        });
    });
});
</script> 